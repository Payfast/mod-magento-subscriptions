<?php
namespace Payfast\Payfast\Controller\Notify;

/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */


use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Payfast\Payfast\Controller\AbstractPayfast;
use Payfast\Payfast\Model\Config as PayFastConfig;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;
use Payfast\Payfast\Model\Info;
use Magento\Quote\Model\QuoteFactory;
use Payfast\Payfast\Model\PaymentTypeInterface;
use Payfast\Payfast\Model\States;

class Index extends AbstractPayfast implements CsrfAwareActionInterface, HttpPostActionInterface
{

    /** @var bool $isInitial when original order is pending and order total amount is not equal to paid order amount */
    protected bool $isInitial = false;

    /** @var bool $isRecurring when original order is not pending */
    protected bool $isRecurring = false;

    protected array $data;
    /**
     * indexAction
     *
     * Instantiate ITN model and pass ITN request to it
     */
    public function execute(): ResultInterface
    {
        $pre = __METHOD__ . " : ";
        $this->_logger->debug($pre . 'bof');

        // Variable Initialization
        $pfError = false;
        $pfErrMsg = '';
        $pfData = [];
        $serverMode = $this->getConfigData('server');
        $pfParamString = '';

        $pfHost = $this->paymentMethod->getPayfastHost($serverMode);

        pflog(' PayFast ITN call received');

        pflog('Server = ' . $pfHost);

        //// Notify PayFast that information has been received
        if (!$pfError) {
            header('HTTP/1.0 200 OK');
            flush();
        }

        //// Get data sent by PayFast
        if (!$pfError) {
            // Posted variables from ITN
            $pfData = pfGetData();

            if (empty($pfData)) {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }

        //// Verify security signature
        if (!$pfError) {
            pflog('Verify security signature');

            // If signature different, log for debugging
            if (!pfValidSignature(
                $pfData,
                $pfParamString,
                $this->getConfigData('passphrase'),
                $this->getConfigData('server')
            )) {
                $pfError = true;
                $pfErrMsg = PF_ERR_INVALID_SIGNATURE;
            }
        }

        //// Verify source IP (If not in debug mode)
        if (!$pfError && !defined('PF_DEBUG')) {
            pflog('Verify source IP');

            if (!pfValidIP($_SERVER['REMOTE_ADDR'], $serverMode)) {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_SOURCE_IP;
            }
        }

        //// Verify data received
        if (!$pfError) {
            pflog('Verify data received');

            if (!pfValidData($pfHost, $pfParamString)) {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }

        //// Get internal order and verify it hasn't already been processed
        if (!$pfError) {
            $this->data = $pfData;
            pflog("Check order hasn't been processed");

            // Load order
            $orderId = $pfData[Info::M_PAYMENT_ID];

            $this->_order = $this->orderFactory->create()->loadByIncrementId($orderId);

            pflog('order status is : ' . $this->_order->getStatus());

            // Check order is in "pending payment" state
            $this->isRecurring = ($this->_order->getState() !== Order::STATE_PENDING_PAYMENT && !empty($pfData['token']));
                // handle recurring subsequent charge
            $this->isInitial = ($this->_order->getState() === Order::STATE_PENDING_PAYMENT && !empty($pfData['token']));

            if ($this->_order->getState() !== Order::STATE_PENDING_PAYMENT && !$this->isRecurring) {
                $pfError = true;
                $pfErrMsg = PF_ERR_ORDER_PROCESSED;
            }
        }

        //// Check status and update order
        if (!$pfError) {
            pflog('Check status and update order');

            // Successful
            $isCompleted = ($pfData[Info::PAYMENT_STATUS] == "COMPLETE");

            if ($isCompleted && !($this->isInitial || $this->isRecurring)) {

                $this->setPaymentAdditionalInformation($pfData);
                // Save invoice
                $this->saveInvoice();
            } elseif ($isCompleted && ($this->isInitial || $this->isRecurring )) {
                if (false === $this->processRecurringPayment()) {
                    $pfError = true;
                    $pfErrMsg = 'Failed to create subscription subsequent payment order ';
                }
            } else {
                $pfErrMsg = 'Unknown payment type and needs investigation';
                $pfError = true;
            }
        }

        // If an error occurred
        if ($pfError) {
            pflog('Error occurred: ' . $pfErrMsg);
            $this->_logger->critical($pre . "Error occured : " . $pfErrMsg);
            return $this->rawResult
                ->setHttpResponseCode(400)
                ->setHeader('Content-Type', 'text/html')
                ->setContents($pfErrMsg);
        }

        return $this->rawResult
            ->setHttpResponseCode(200)
            ->setHeader('Content-Type', 'text/html')
            ->setContents('HTTP/1.0 200');
    }

    /**
     * processRecurringPayment
     *
     * deals with subsequent subscription charge.
     *
     */
    private function processRecurringPayment()
    {
        $pre = __METHOD__ . ' : ';
        pflog($pre . 'bof');
        $respose = true;
//        return $respose;
        try {
            /** @var \Payfast\Payfast\Model\Payment $paymentFactory */
            $paymentFactory = $this->paymentFactory->create();
            /** @var \Payfast\Payfast\Model\Payment $recurringPayment */
            $recurringPayment = $paymentFactory->loadByInternalReferenceId($this->data['custom_str1']);
            if (!$recurringPayment->getId()) {
                $this->_logger->error($pre . 'Failed to get recurring payment for token ' . $this->data['token']);
                throw new \Exception($pre . 'Failed to get recurring payment for token ' . $this->data['token']);
            }

            // create Product info Item needed for creating order
            $productItemInfo = new \Magento\Framework\DataObject;
            $productItemInfo->setShippingAmount(0);
            $productItemInfo->setPrice($this->data['amount_gross']);

            pflog(
                __(
                    '%1Setting Payment type to : %2, of %3',
                    $pre,
                    PaymentTypeInterface::RECURRING,
                    SubscriptionType::RECURRING_LABEL[$recurringPayment->getSubscriptionType()]
                )
            );

            $productItemInfo->setPaymentType(PaymentTypeInterface::RECURRING);

            $firstCharge = (null === $recurringPayment->getReferenceId());

            if ($firstCharge) {
                $recurringPayment->setReferenceId($this->data['token']);
            }

            if (null === $recurringPayment->getRecurringPaymentStartDate() && !empty($this->data['billing_date'])) {
                $recurringPayment->setRecurringPaymentStartDate($this->data['billing_date']);
            }
            if ($recurringPayment->getState() !== States::ACTIVE) {
                $recurringPayment->activate();
            } else {
                $recurringPayment->save();
            }

            if ($this->isInitial && (int)$recurringPayment->getSubscriptionType() === SubscriptionType::RECURRING_SUBSCRIPTION) {

                $this->setPaymentAdditionalInformation($this->data);
                $this->saveInvoice();
                $orderId = $this->_order->getId();

            } elseif ($firstCharge && (int)$recurringPayment->getSubscriptionType() === SubscriptionType::RECURRING_ADHOC) {
                $this->setPaymentAdditionalInformation($this->data);
                $this->saveInvoice();
                $orderId = $this->_order->getId();

            } else {

                $order = $recurringPayment->createOrder($productItemInfo);

                $payment = $order->getPayment()
                    ->setTransactionId($this->data['pf_payment_id'])
                    ->setCurrencyCode($recurringPayment->getCurrencyCode())
                    ->setPrepareMessage(__('ITN Recurring payment %1 ', $this->data['payment_status']))
                    ->setIsTransactionClosed(0);

                $payment->setAdditionalInformation(Info::PAYMENT_STATUS, $this->data[Info::PAYMENT_STATUS]);
                $payment->setAdditionalInformation(Info::M_PAYMENT_ID, $this->data[Info::M_PAYMENT_ID]);
                $payment->setAdditionalInformation(Info::PF_PAYMENT_ID, $this->data[Info::PF_PAYMENT_ID]);
                $payment->setAdditionalInformation(Info::EMAIL_ADDRESS, $this->data[Info::EMAIL_ADDRESS]);
                $payment->setAdditionalInformation("amount_fee", $this->data['amount_fee']);
                $payment->registerCaptureNotification($this->data['amount_gross']);

                $invoice = $order->prepareInvoice();
                $order = $invoice->getOrder();

                $order->setIsInProcess(true);

                $transaction = $this->transactionFactory->create();
                $transaction->addObject($order)->save();

                $this->orderResourceModel->save($order);

                $invoice = $payment->getCreatedInvoice();

                $orderId = $order->getId();

                if ($this->_config->getValue(PayFastConfig::KEY_SEND_CONFIRMATION_EMAIL)) {
                    pflog(
                        'before sending order email, canSendNewEmailFlag is ' . boolval(
                            $this->_order->getCanSendNewEmailFlag()
                        )
                    );
                    $this->orderSender->send($this->_order);

                    pflog('after sending order email');
                }

                if ($this->_config->getValue(PayFastConfig::KEY_SEND_INVOICE_EMAIL)) {
                    pflog('before sending invoice email is ' . boolval($this->_order->getCanSendNewEmailFlag()));
                    foreach ($this->_order->getInvoiceCollection() as $invoice) {
                        pflog('sending invoice #' . $invoice->getId());
                        if ($invoice->getId()) {
                            $this->invoiceSender->send($invoice);
                        }
                    }

                    pflog('after sending ' . boolval($invoice->getIncrementId()));
                }
            }

            $recurringPayment->addOrderRelation($orderId);
//            $pro
        } catch (LocalizedException $exception) {
            $respose = false;
            $this->_logger->error($pre . 'Error detected : '. $exception->getMessage(). PHP_EOL . $exception->getTraceAsString());
        }

        return $respose;
    }

    /**
     * getItemValue
     *
     * @param array $items
     * @param string $fieldType
     * @param string $targetField
     * @param  $default
     */
    private function getItemValue(array $items, string $fieldType, string $targetField, $default = 0)
    {
        $typeValue = null;
        foreach ($items as $item) {
            if (!empty($item['type']) && $item['type'] === $fieldType) {
                $typeValue += $item[$targetField];
            }
        }

        return $typeValue ?? $default;
    }
    /**
     * saveInvoice
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveInvoice()
    {
        pflog(__METHOD__ . ' : bof');

        try {

            $invoice = $this->_order->prepareInvoice();

            $order = $invoice->getOrder();
            $order->setTotalPaid($this->data['amount_gross']);
            $order->setBaseTotalPaid($this->data['amount_gross']);

            $this->_order->setIsInProcess(true);
            $transaction = $this->transactionFactory->create();
            $transaction->addObject($order)->save();

            $this->orderResourceModel->save($this->_order);

            if ($this->_config->getValue(PayFastConfig::KEY_SEND_CONFIRMATION_EMAIL)) {
                pflog(
                    'before sending order email, canSendNewEmailFlag is ' . boolval(
                        $this->_order->getCanSendNewEmailFlag()
                    )
                );
                $this->orderSender->send($this->_order);

                pflog('after sending order email');
            }

            if ($this->_config->getValue(PayFastConfig::KEY_SEND_INVOICE_EMAIL)) {
                pflog('before sending invoice email is ' . boolval($this->_order->getCanSendNewEmailFlag()));
                foreach ($this->_order->getInvoiceCollection() as $invoice) {
                    pflog('sending invoice #' . $invoice->getId());
                    if ($invoice->getId()) {
                        $this->invoiceSender->send($invoice);
                    }
                }

                pflog('after sending ' . boolval($invoice->getIncrementId()));
            }
        } catch (LocalizedException $e) {
            pflog(__METHOD__ . ' localizedException caught and will be re thrown. ');
            pflog(__METHOD__ . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            pflog(__METHOD__ . 'Exception caught and will be re thrown.');
            pflog(__METHOD__ . $e->getMessage());
            throw $e;
        }

        pflog(__METHOD__ . ' : eof');
    }

    /**
     * @param  $pfData
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function setPaymentAdditionalInformation($pfData)
    {
        pflog(__METHOD__ . ' : bof');
        pflog('Order complete');

        try {
            // Update order additional payment information
            /**
             * @var Payment $payment
             */
            $payment = $this->_order->getPayment();
            $payment->setAdditionalInformation(Info::PAYMENT_STATUS, $pfData[Info::PAYMENT_STATUS]);
            $payment->setAdditionalInformation(Info::M_PAYMENT_ID, $pfData[Info::M_PAYMENT_ID]);
            $payment->setAdditionalInformation(Info::PF_PAYMENT_ID, $pfData[Info::PF_PAYMENT_ID]);
            $payment->setAdditionalInformation(Info::EMAIL_ADDRESS, $pfData[Info::EMAIL_ADDRESS]);
            $payment->setAdditionalInformation("amount_fee", $pfData['amount_fee']);
            $payment->registerCaptureNotification($pfData['amount_gross'], true);

            $this->_order->setPayment($payment);
        } catch (LocalizedException $e) {
            pflog(__METHOD__ . ' localizedException caught and will be re thrown. ');
            pflog(__METHOD__ . $e->getMessage());
            throw $e;
        }

        pflog(__METHOD__ . ' : eof');
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}

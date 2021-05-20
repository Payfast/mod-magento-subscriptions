<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Model;

use Magento\Framework\Pricing\Helper\Data as AmountRenderer;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use Payfast\Payfast\Model\Config\Source\Frequency;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;

/**
 * Class Payment
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Payment extends PayfastRecurringPayment
{
    /**
     * Workflow variable
     *
     * @var Workflow
     */
    protected $_workflow = null;

    /**
     * OrderFactory variable
     *
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * AddressFactory variable
     *
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * PaymentFactory variable
     *
     * @var PaymentFactory
     */
    protected $_paymentFactory;

    /**
     * OrderItemFactory variable
     *
     * @var OrderItemFactory
     */
    protected $_orderItemFactory;

    /**
     * MathRandom variable
     *
     * @var MathRandom
     */
    protected $mathRandom;

    /**
     * States variable
     *
     * @var States
     */
    protected $states;

    /**
     * MessageManager variable
     *
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * Serializer
     *
     * @var SerializerInterface
     */
    private $serializer;

    /** @var AmountRenderer $amountRenderer */
    protected $amountRenderer;

    /**
     * Payment constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param Config\Source\BillingPeriodUnitsOptions $billingPeriodUnitsOptions
     * @param \Payfast\Payfast\Block\Fields $fields
     * @param ManagerInterfaceFactory $managerFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\Order\AddressFactory $addressFactory
     * @param \Magento\Sales\Model\Order\PaymentFactory $paymentFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param States $states
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param SerializerInterface $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param AmountRenderer $amountRenderer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentData,
        \Payfast\Payfast\Model\Config\Source\BillingPeriodUnitsOptions $billingPeriodUnitsOptions,
        \Payfast\Payfast\Block\Fields $fields,
        ManagerInterfaceFactory $managerFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        \Magento\Sales\Model\Order\PaymentFactory $paymentFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Math\Random $mathRandom,
        States $states,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        SerializerInterface $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Frequency $frequency,
        AmountRenderer $amountRenderer,
        array $data = []
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_addressFactory = $addressFactory;
        $this->_paymentFactory = $paymentFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->mathRandom = $mathRandom;
        $this->states = $states;
        $this->messageManager = $messageManager;
        $this->serializer = $serializer;
        $this->amountRenderer = $amountRenderer;
        parent::__construct(
            $context,
            $registry,
            $paymentData,
            $billingPeriodUnitsOptions,
            $fields,
            $managerFactory,
            $localeDate,
            $localeResolver,
            $dateTime,
            $serializer,
            $resource,
            $resourceCollection,
            $frequency,
            $amountRenderer,
            $data
        );
    }

    /**
     * LoadByInternalReferenceId function
     *
     * @param string $internalReferenceId internalReferenceId
     *
     * @return void
     */
    public function loadByInternalReferenceId($internalReferenceId)
    {
        return $this->load($internalReferenceId, 'internal_reference_id');
    }

    /**
     * @param string $referenceId Payfast token of subscription.
     *
     */
    public function loadByReferenceId($referenceId)
    {
        return $this->load($referenceId, 'reference_id');
    }

    /**
     * Submit function
     *
     * @return void
     */
    public function submit()
    {
        $pre = __METHOD__ . ' : ';
        $this->_getResource()->beginTransaction();
        try {
            $this->setInternalReferenceId($this->mathRandom->getUniqueHash($this->getId() . '-'));
            $this->setProfileId($this->mathRandom->getUniqueHash($this->getId() . '-'));
            $this->setOrderId($this->getQuote()->getReservedOrderId());

            $this->setSubscriptionType($this->getData('subscription_type'));
            $this->setReferenceId($this->getData('reference_id'));
            $this->_logger->debug($pre . 'subscription type is '. SubscriptionType::RECURRING_LABEL[$this->getSubscriptionType()]);

            if ((int)$this->getSubscriptionType() === SubscriptionType::RECURRING_SUBSCRIPTION) {
                $this->_logger->debug($pre. 'preparing Setting subscription data for db');
                $this->setRecurringPaymentStartDate($this->getData('recurring_payment_start_date'));
                $this->setBillingPeriodMaxCycles($this->getData('pf_billing_period_max_cycles'));
                $this->setBillingPeriodFrequency($this->getData('pf_billing_period_frequency'));
                $this->setInitialAmount($this->getData('pf_initial_amount'));
            }

            $this->getManager()->submit($this, $this->getQuote()->getPayment());
            $this->save();
            $this->_getResource()->commit();

//            $this->activate();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_getResource()->rollBack();
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
    }

    /**
     * Activate function
     *
     * @return void
     */
    public function activate()
    {
        $this->_checkWorkflow(States::ACTIVE, false);
        $this->setNewState(States::ACTIVE);
        $this->getManager()->updateStatus($this);
        $this->setState(States::ACTIVE)->save();
    }

    /**
     * addToken
     * will update model record with subscription token
     * @param string $token
     *
     */
    public function addToken(string $token)
    {
        if (null === $this->getReferenceId()) {
            $this->setReferenceId($token)->save();
        }
    }

    /**
     * CanActivate function
     *
     * @return boolean
     */
    public function canActivate()
    {
        return $this->_checkWorkflow(States::ACTIVE);
    }

    /**
     * Suspend function
     *
     * @return void
     */
    public function suspend()
    {
        $this->_checkWorkflow(States::SUSPENDED, false);
        $this->setNewState(States::SUSPENDED);
        $this->getManager()->updateStatus($this);
        $this->setState(States::SUSPENDED)->save();
    }

    /**
     * CanSuspend function
     *
     * @return boolean
     */
    public function canSuspend()
    {
        return $this->_checkWorkflow(States::SUSPENDED);
    }

    /**
     * Cancel function
     *
     * @return boolean
     */
    public function cancel()
    {
        $this->_checkWorkflow(States::CANCELED, false);
        $this->setNewState(States::CANCELED);
        $this->getManager()->updateStatus($this);
        $this->setState(States::CANCELED)->save();
    }

    /**
     * CanCancel function
     *
     * @return boolean
     */
    public function canCancel()
    {
        return $this->_checkWorkflow(States::CANCELED);
    }

    /**
     * FetchUpdate function
     *
     * @return void
     */
    public function fetchUpdate()
    {
        $result = new \Magento\Framework\DataObject();
        $this->getManager()->getDetails($this->getReferenceId(), $result);

        if ($result->getIsPaymentActive() || $result->getIsProfileActive()) {
            $this->setState(States::ACTIVE);
        } elseif ($result->getIsPaymentPending() || $result->getIsProfilePending()) {
            $this->setState(States::PENDING);
        } elseif ($result->getIsPaymentCanceled() || $result->getIsProfileCanceled()) {
            $this->setState(States::CANCELED);
        } elseif ($result->getIsPaymentSuspended() || $result->getIsProfileSuspended()) {
            $this->setState(States::SUSPENDED);
        } elseif ($result->getIsPaymentExpired() || $result->getIsProfileExpired()) {
            $this->setState(States::EXPIRED);
        }
    }

    /**
     * CanFetchUpdate function
     *
     * @return boolean
     */
    public function canFetchUpdate()
    {
        return $this->getManager()->canGetDetails();
    }

    /**
     * CreateOrder function
     *
     * @return Order
     */
    public function createOrder()
    {
        $items = [];
        $itemInfoObjects = func_get_args();

        $billingAmount = 0;
        $shippingAmount = 0;
        $taxAmount = 0;
        $isVirtual = 1;
        $weight = 0;
        foreach ($itemInfoObjects as $itemInfo) {
            $item = $this->_getItem($itemInfo);
            $billingAmount += $item->getPrice();
            $shippingAmount += $item->getShippingAmount();
            $taxAmount += $item->getTaxAmount();
            $weight += $item->getWeight();
            if (!$item->getIsVirtual()) {
                $isVirtual = 0;
            }
            $items[] = $item;
        }
        $grandTotal = $billingAmount + $shippingAmount + $taxAmount;

        $order = $this->_orderFactory->create();

        $billingAddress = $this->_addressFactory->create()
            ->setData($this->getBillingAddressInfo())
            ->setId(null);

        $shippingInfo = $this->getShippingAddressInfo();
        $shippingAddress = $this->_addressFactory->create()
            ->setData($shippingInfo)
            ->setId(null);

        $payment = $this->_paymentFactory->create()
            ->setMethod($this->getMethodCode());

        $transferDataKeys = [
            'store_id',             'store_name',           'customer_id',          'customer_email',
            'customer_firstname',   'customer_lastname',    'customer_middlename',  'customer_prefix',
            'customer_suffix',      'customer_taxvat',      'customer_gender',      'customer_is_guest',
            'customer_note_notify', 'customer_group_id',    'customer_note',        'shipping_method',
            'shipping_description', 'base_currency_code',   'global_currency_code', 'order_currency_code',
            'store_currency_code',  'base_to_global_rate',  'base_to_order_rate',   'store_to_base_rate',
            'store_to_order_rate'
        ];

        $orderInfo = $this->getOrderInfo();
        foreach ($transferDataKeys as $key) {
            if (isset($orderInfo[$key])) {
                $order->setData($key, $orderInfo[$key]);
            } elseif (isset($shippingInfo[$key])) {
                $order->setData($key, $shippingInfo[$key]);
            }
        }

        $order->setStoreId($this->getStoreId())
            //->setState(\Magento\Sales\Model\Order::STATE_NEW)
            ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->setBaseToOrderRate($this->getInfoValue('order_info', 'base_to_quote_rate'))
            ->setStoreToOrderRate($this->getInfoValue('order_info', 'store_to_quote_rate'))
            ->setOrderCurrencyCode($this->getInfoValue('order_info', 'quote_currency_code'))
            ->setBaseSubtotal($billingAmount)
            ->setSubtotal($billingAmount)
            ->setBaseShippingAmount($shippingAmount)
            ->setShippingAmount($shippingAmount)
            ->setBaseTaxAmount($taxAmount)
            ->setTaxAmount($taxAmount)
            ->setBaseGrandTotal($grandTotal)
            ->setGrandTotal($grandTotal)
            ->setIsVirtual($isVirtual)
            ->setWeight($weight)
            ->setTotalQtyOrdered($this->getInfoValue('order_info', 'items_qty'))
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setPayment($payment);

        foreach ($items as $item) {
            $order->addItem($item);
        }

        return $order;
    }

    /**
     * IsValid function
     *
     * @return boolean
     */
    public function isValid()
    {
        $pre = __METHOD__ . ' : ';
        $this->_logger->debug($pre . 'bof');

        parent::isValid();

        // state
        if (!array_key_exists($this->getState(), $this->states->toOptionArray())) {
            $this->_errors['state'][] = __('Wrong state: "%1"', $this->getState());
        }

        return empty($this->_errors);
    }

    /**
     * ImportQuote function
     *
     * @param string \Magento\Quote\Model\Quote $quote quote
     *
     * @return void
     */
    public function importQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->setQuote($quote);

        if ($quote->getPayment() && $quote->getPayment()->getMethod()) {
            $this->setManager(
                $this->_managerFactory->create(
                    ['paymentMethod' => $quote->getPayment()->getMethodInstance()]
                )
            );
        }

        $orderInfo = $quote->getData();
        $this->_cleanupArray($orderInfo);
        $this->setOrderInfo($orderInfo);

        $addressInfo = $quote->getBillingAddress()->getData();
        $this->_cleanupArray($addressInfo);
        $this->setBillingAddressInfo($addressInfo);
        if (!$quote->isVirtual()) {
            $addressInfo = $quote->getShippingAddress()->getData();
            $this->_cleanupArray($addressInfo);
            $this->setShippingAddressInfo($addressInfo);
        }

        $this->setCurrencyCode($quote->getBaseCurrencyCode());
        $this->setCustomerId($quote->getCustomerId());
        $this->setStoreId($quote->getStoreId());

        return $this;
    }

    /**
     * ImportQuoteItem function
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item item
     *
     * @return void
     */
    public function importQuoteItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $this->setQuoteItemInfo($item);

        $this->setBillingAmount($item->getBaseRowTotal())
            ->setTaxAmount($item->getBaseTaxAmount())
            ->setShippingAmount($item->getBaseShippingAmount());
        if (!$this->getScheduleDescription()) {
            $this->setScheduleDescription(trim($item->getName()));
        }

        $orderItemInfo = $item->getData();
        $this->_cleanupArray($orderItemInfo);

        $customOptions = $item->getOptionsByCode();
        if ($customOptions['info_buyRequest']) {
            $orderItemInfo['info_buyRequest'] = $customOptions['info_buyRequest']->getValue();
        }

        $this->setOrderItemInfo($orderItemInfo);

        return $this->_filterValues();
    }

    /**
     * RenderData function
     *
     * @param string $key key
     *
     * @return void
     */
    public function renderData($key)
    {
        $value = $this->_getData($key);
        switch ($key) {
            case 'state':
                $states = $this->states->toOptionArray();
                return $states[$value];
        }
        return parent::renderData($key);
    }

    /**
     * GetInfoValue function
     *
     * @param string $infoKey      infoKey
     * @param string $infoValueKey infoValueKey
     *
     * @return void
     */
    public function getInfoValue($infoKey, $infoValueKey)
    {
        $info = $this->getData($infoKey);
        if (!$info) {
            return;
        }
        if (!is_object($info)) {
            if (is_array($info) && isset($info[$infoValueKey])) {
                return $info[$infoValueKey];
            }
        } else {
            if ($info instanceof \Magento\Framework\DataObject) {
                return $info->getDataUsingMethod($infoValueKey);
            } elseif (isset($info->$infoValueKey)) {
                return $info->$infoValueKey;
            }
        }
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Payfast\Payfast\Model\ResourceModel\Payment');
    }

    /**
     * FilterValues function
     *
     * @return void
     */
    protected function _filterValues()
    {
        $result = parent::_filterValues();

        if (!$this->getState()) {
            $this->setState(States::UNKNOWN);
        }

        return $result;
    }

    /**
     * InitWorkflow function
     *
     * @return void
     */
    protected function _initWorkflow()
    {
        if (null === $this->_workflow) {
            $this->_workflow = [
                'unknown' => ['pending', 'active', 'suspended', 'canceled'],
                'pending' => ['active', 'canceled'],
                'active' => ['suspended', 'canceled'],
                'suspended' => ['active', 'canceled'],
                'canceled' => [],
                'expired' => [],
            ];
        }
    }

    /**
     * CheckWorkflow function
     *
     * @param string  $againstState againstState
     * @param boolean $soft         soft
     *
     * @return void
     */
    protected function _checkWorkflow($againstState, $soft = true)
    {
        $this->_initWorkflow();
        $state = $this->getState();
        $result = (!empty($this->_workflow[$state])) && in_array($againstState, $this->_workflow[$state]);

        if (!$soft && !$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This payment state cannot be changed to "%1".', $againstState)
            );
        }
        return $result;
    }

    /**
     * GetChildOrderIds function
     *
     * @return void
     */
    public function getChildOrderIds()
    {
        $ids = $this->_getResource()->getChildOrderIds($this);
        if (empty($ids)) {
            $ids[] = '-1';
        }
        return $ids;
    }

    /**
     * AddOrderRelation function
     *
     * @param int $orderId orderId
     *
     * @return void
     */
    public function addOrderRelation($orderId)
    {
        $this->getResource()->addOrderRelation($this->getId(), $orderId);
        return $this;
    }

    /**
     * GetItem function
     *
     * @param string $itemInfo itemInfo
     *
     * @return void
     */
    protected function _getItem($itemInfo)
    {
        $paymentType = $itemInfo->getPaymentType();
        if (!$paymentType) {
            throw new \Exception("PayFast Recurring payment type is not specified.");
        }

        switch ($paymentType) {
            case PaymentTypeInterface::RECURRING:
                return $this->_getRegularItem($itemInfo);
            case PaymentTypeInterface::TRIAL:
                return $this->_getTrialItem($itemInfo);
            case PaymentTypeInterface::INITIAL:
                return $this->_getInitialItem($itemInfo);
            default:
                new \Exception("Invalid PayFast recurring payment type '{$paymentType}'.");
        }
    }

    /**
     * GetRegularItem function
     *
     * @param string $itemInfo itemInfo
     *
     * @return void
     */
    protected function _getRegularItem($itemInfo)
    {
        $price = $itemInfo->getPrice() ? $itemInfo->getPrice() : $this->getBillingAmount();
        $shippingAmount = $itemInfo->getShippingAmount() ? $itemInfo->getShippingAmount() : $this->getShippingAmount();
        $taxAmount = $itemInfo->getTaxAmount() ? $itemInfo->getTaxAmount() : $this->getTaxAmount();

        $item = $this->_orderItemFactory->create()
            ->setData($this->getOrderItemInfo())
            ->setQtyOrdered($this->getInfoValue('order_item_info', 'qty'))
            ->setBaseOriginalPrice($this->getInfoValue('order_item_info', 'price'))
            ->setPrice($price)
            ->setBasePrice($price)
            ->setRowTotal($price)
            ->setBaseRowTotal($price)
            ->setTaxAmount($taxAmount)
            ->setShippingAmount($shippingAmount)
            ->setId(null);
        return $item;
    }

    /**
     * GetTrialItem function
     *
     * @param string $itemInfo itemInfo
     *
     * @return void
     */
    protected function _getTrialItem($itemInfo)
    {
        $item = $this->_getRegularItem($itemInfo);

        $item->setName(
            __('Trial ') . $item->getName()
        );

        $option = [
            'label' => __('Payment type'),
            'value' => __('Trial period payment')
        ];

        $this->_addAdditionalOptionToItem($item, $option);

        return $item;
    }

    /**
     * GetInitialItem function
     *
     * @param string $itemInfo itemInfo
     *
     * @return void
     */
    protected function _getInitialItem($itemInfo)
    {
        $price = $itemInfo->getPrice() ? $itemInfo->getPrice() : $this->getInitAmount();
        $shippingAmount = $itemInfo->getShippingAmount() ? $itemInfo->getShippingAmount() : 0;
        $taxAmount = $itemInfo->getTaxAmount() ? $itemInfo->getTaxAmount() : 0;
        $item = $this->_orderItemFactory->create()
            ->setStoreId($this->getStoreId())
            ->setProductType(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)
            ->setIsVirtual(1)
            ->setSku('initial_fee')
            ->setName(__('PayFast Recurring Payment Initial Fee'))
            ->setDescription('')
            ->setWeight(0)
            ->setQtyOrdered(1)
            ->setPrice($price)
            ->setOriginalPrice($price)
            ->setBasePrice($price)
            ->setBaseOriginalPrice($price)
            ->setRowTotal($price)
            ->setBaseRowTotal($price)
            ->setTaxAmount($taxAmount)
            ->setShippingAmount($shippingAmount);

        $option = [
            'label' => __('Payment type'),
            'value' => __('Initial period payment')
        ];

        $this->_addAdditionalOptionToItem($item, $option);
        return $item;
    }

    /**
     * AddAdditionalOptionToItem function
     *
     * @param sting $item   item
     * @param sting $option option
     *
     * @return void
     */
    protected function _addAdditionalOptionToItem($item, $option)
    {
        $options = $item->getProductOptions();
        $additionalOptions = $item->getProductOptionByCode('additional_options');
        if (is_array($additionalOptions)) {
            $additionalOptions[] = $option;
        } else {
            $additionalOptions = [$option];
        }
        $options['additional_options'] = $additionalOptions;
        $item->setProductOptions($options);
    }

    /**
     * CleanupArray function
     *
     * @param array $array array
     *
     * @return void
     */
    private function _cleanupArray(&$array)
    {
        if (!$array) {
            return;
        }
        foreach ($array as $key => $value) {
            if (is_object($value)) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                $this->_cleanupArray($array[$key]);
            }
        }
    }
}

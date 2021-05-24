<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Payfast\Payfast\Model\PayfastRecurringPayment;

/**
 * Class PrepareProductRecurringPaymentOptions
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za
 * @link     https://www.payfast.co.za
 */
class PrepareProductRecurringPaymentOptions implements ObserverInterface
{
    /**
     * StoreManagerInterface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * PaypalRecurringPaymentFactory
     *
     * @var \Payfast\Payfast\Model\Payfast
     */
    protected $payfastRecurringPayment;

    /**
     * Fields
     *
     * @var \Sparsh\PaypalRecurringPayment\Block\Fields
     */
    protected $fields;

    /**
     * Config
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentconfig;

    /**
     * SerializerInterface
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * PrepareProductRecurringPaymentOptions constructor.
     *
     * @param \Magento\Framework\Locale\ResolverInterface                            $locale                 locale
     * @param \Magento\Store\Model\StoreManagerInterface                             $storeManager           storeManager
     * @param \Payfast\Payfast\Model\PayfastRecurringPaymentFactory $payfastRecurringPaymentFactory paypalRecurringPayment
     * @param \Sparsh\PaypalRecurringPayment\Block\Fields                        $fields                 fields
     * @param \Magento\Payment\Model\Config                                          $paymentconfig          paymentconfig
     * @param \Magento\Framework\Serialize\SerializerInterface                       $serializer             serializer
     */
    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Payfast\Payfast\Model\PayfastRecurringPaymentFactory $payfastRecurringPaymentFactory,
        \Payfast\Payfast\Block\Fields $fields,
        \Magento\Payment\Model\Config $paymentconfig,
        SerializerInterface $serializer
    ) {
        $this->storeManager = $storeManager;
        $this->payfastRecurringPayment = $payfastRecurringPaymentFactory;
        $this->fields = $fields;
        $this->locale = $locale;
        $this->paymentconfig = $paymentconfig;
        $this->serializer = $serializer;
    }

    /**
     * Execute
     *
     * @param Observer $observer observer
     *
     * @return null
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $buyRequest = $observer->getEvent()->getBuyRequest();

        $activePaymentMethods = $this->paymentconfig->getActiveMethods();

        if (!$product->getIsPaypalRecurring() || !isset($activePaymentMethods['payfast'])) {
            return;
        }

        $payment = $this->payfastRecurringPayment->create(['locale' => $this->locale]);
        $payment->setStore($this->storeManager->getStore())
            ->importBuyRequest($buyRequest)
            ->importProduct($product);

        if (!$payment) {
            return;
        }

        // add the start datetime as product custom option
        $product->addCustomOption(
            PayfastRecurringPayment::PRODUCT_OPTIONS_KEY,
            $this->serializer
                ->serialize(['paypal_recurring_payment_start_date' => $payment->getPaypalRecurringPaymentStartDate()])
        );

        $infoOptions = [[
            'label' => $this->fields->getFieldLabel('paypal_recurring_payment_start_date'),
            'value' => $payment->exportPayfastRecurringPaymentStartDate(),
        ]];

        foreach ($payment->exportPaypalRecurringPaymentScheduleInfo() as $info) {
            $infoOptions[] = [
                'label' => $info->getTitle(),
                'value' => $info->getSchedule(),
            ];
        }

        $product->addCustomOption('additional_options', $this->serializer->serialize($infoOptions));
    }
}

<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Payfast\Payfast\Block\Fields;
use Payfast\Payfast\Model\PayfastRecurringPayment;
use Payfast\Payfast\Model\PayfastRecurringPaymentFactory;
use Psr\Log\LoggerInterface as LoggerInterfaceAlias;

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
     * @var StoreManagerInterface
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
     * @var Config
     */
    protected $paymentconfig;

    /**
     * SerializerInterface
     *
     * @var SerializerInterface
     */
    private $serializer;

    private ResolverInterface $locale;

    private $_logger;

    /**
     * PrepareProductRecurringPaymentOptions constructor.
     *
     * @param ResolverInterface                            $locale                 locale
     * @param StoreManagerInterface                             $storeManager           storeManager
     * @param PayfastRecurringPaymentFactory $payfastRecurringPaymentFactory paypalRecurringPayment
     * @param \Sparsh\PaypalRecurringPayment\Block\Fields                        $fields                 fields
     * @param Config                                          $paymentConfig          paymentconfig
     * @param \Magento\Framework\Serialize\SerializerInterface                       $serializer             serializer
     */
    public function __construct(
        ResolverInterface $locale,
        StoreManagerInterface $storeManager,
        PayfastRecurringPaymentFactory $payfastRecurringPaymentFactory,
        Fields $fields,
        Config $paymentConfig,
        SerializerInterface $serializer,
        LoggerInterfaceAlias $logger
    ) {
        $this->storeManager = $storeManager;
        $this->payfastRecurringPayment = $payfastRecurringPaymentFactory;
        $this->fields = $fields;
        $this->locale = $locale;
        $this->paymentconfig = $paymentConfig;
        $this->serializer = $serializer;
        $this->_logger = $logger;
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
        $pre = __METHOD__ . ' : ';
        $this->_logger->debug($pre . 'bof');
        $product = $observer->getEvent()->getProduct();
        $buyRequest = $observer->getEvent()->getBuyRequest();

        $activePaymentMethods = $this->paymentconfig->getActiveMethods();

        if (!$product->getIsPaypalRecurring() || !isset($activePaymentMethods[\Payfast\Payfast\Model\Config::METHOD_CODE])) {
            return;
        }
        /** @var PayfastRecurringPayment $payment */
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
                ->serialize(['payfast_recurring_payment_start_date' => $payment->getPayfastRecurringPaymentStartDate()])
        );

        $infoOptions = [[
            'label' => $this->fields->getFieldLabel(PayfastRecurringPayment::RECURRING_PAYMENT_START_DATE),
            'value' => $payment->exportPayfastRecurringPaymentStartDate(),
        ]];

        foreach ($payment->exportPaypalRecurringPaymentScheduleInfo() as $info) {
            $infoOptions[] = [
                'label' => $info->getTitle(),
                'value' => $info->getSchedule(),
            ];
        }

        $product->addCustomOption('additional_options', $this->serializer->serialize($infoOptions));

        $this->_logger->debug($pre . 'bof');
    }
}

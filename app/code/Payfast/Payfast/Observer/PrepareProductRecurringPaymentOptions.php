<?php
/**
 * Class PrepareProductRecurringPaymentOptions
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class PrepareProductRecurringPaymentOptions
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
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

        if (!$product->getIsPaypalRecurring() || !isset($activePaymentMethods['paypal_express'])) {
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
            \Sparsh\PaypalRecurringPayment\Model\PayfastRecurringPayment::PRODUCT_OPTIONS_KEY,
            $this->serializer
                ->serialize(['paypal_recurring_payment_start_date' => $payment->getPaypalRecurringPaymentStartDate()])
        );

        $infoOptions = [[
            'label' => $this->fields->getFieldLabel('paypal_recurring_payment_start_date'),
            'value' => $payment->exportPaypalRecurringPaymentStartDate(),
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

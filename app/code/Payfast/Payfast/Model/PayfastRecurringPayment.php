<?php
/**
 * Class PaypalRecurringPayment
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Helper\Data as AmountRenderer;
use Payfast\Payfast\Model\Config\Source\BillingPeriodUnitsOptions;
use Magento\Framework\Serialize\SerializerInterface;
use Payfast\Payfast\Model\Config\Source\Frequency;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;

/**
 * Class PaypalRecurringPayment
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class PayfastRecurringPayment extends \Magento\Framework\Model\AbstractModel
{
    /**
     * PAYPAL_RECURRING_PAYMENT_START_DATE
     */
    const RECURRING_PAYMENT_START_DATE = 'recurring_payment_start_date';
    /**
     * PRODUCT_OPTIONS_KEY
     */
    const PRODUCT_OPTIONS_KEY = 'payfast_recurring_payment_options';

    /**
     * Errors
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Manager
     *
     * @var manager
     */
    protected $_manager = null;



    /**
     * Store
     *
     * @var $_store
     */
    protected $_store = null;

    /**
     * PaymentMethods
     *
     * @var array
     */
    protected $_paymentMethods = [];

    /**
     * Data
     *
     * @var \Magento\Payment\Helper\Data|null
     */
    protected $_paymentData = null;

    /**
     * BillingPeriodUnitsOptions
     *
     * @var BillingPeriodUnitsOptions
     */
    protected $_billingPeriodUnitsOptions;

    /** @var Frequency $_frequency */
    protected $_frequency;

    /**
     * Fields
     *
     * @var \Payfast\Payfast\Block\Fields $_fields
     */
    protected $_fields;

    /**
     * TimezoneInterface
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * ResolverInterface
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * ManagerInterfaceFactory
     *
     * @var ManagerInterfaceFactory
     */
    protected $_managerFactory;

    /**
     * DateTime
     *
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * Serializer
     *
     * @var SerializerInterface
     */
    private $serializer;

    /** @var AmountRenderer $amountRenderer */
    protected $amountRenderer;

    /**
     * PaypalRecurringPayment constructor.
     *
     * @param \Magento\Framework\Model\Context                             $context                   context
     * @param \Magento\Framework\Registry                                  $registry                  registry
     * @param \Magento\Payment\Helper\Data                                 $paymentData               paymentData
     * @param BillingPeriodUnitsOptions                                    $billingPeriodUnitsOptions billingPeriodUnitsOptions
     * @param \Payfast\Payfast\Block\Fields              $fields                    fields
     * @param ManagerInterfaceFactory                                      $managerFactory            managerFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $localeDate                localeDate
     * @param \Magento\Framework\Locale\ResolverInterface                  $localeResolver            localeResolver
     * @param \Magento\Framework\Stdlib\DateTime                           $dateTime                  dateTime
     * @param Magento\Framework\Serialize\SerializerInterface              $serializer                serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource                  resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection        resourceCollection
     * @param AmountRenderer $amountRender
     * @param array                                                        $data                      data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentData,
        BillingPeriodUnitsOptions $billingPeriodUnitsOptions,
        \Payfast\Payfast\Block\Fields $fields,
        \Payfast\Payfast\Model\ManagerInterfaceFactory $managerFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        SerializerInterface $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        Frequency $frequency,
        AmountRenderer $amountRender,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        $this->_billingPeriodUnitsOptions = $billingPeriodUnitsOptions;
        $this->_fields = $fields;
        $this->_managerFactory = $managerFactory;
        $this->_localeDate = $localeDate;
        $this->_localeResolver = $localeResolver;
        $this->_dateTime = $dateTime;
        $this->serializer = $serializer;
        $this->_frequency = $frequency;
        $this->amountRenderer = $amountRender;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * IsValid
     *
     * @return bool
     */
    public function isValid()
    {

        $this->_filterValues();
        $this->_errors = [];

        if (!$this->getRecurringPaymentStartDate()) {
            $this->_errors['payfast_recurring_payment_start_date'][] = __('PayFast Recurring Payment Start date is undefined.');
        } elseif (!\Zend_Date::isDate($this->getPaypalRecurringPaymentStartDate(), \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT)) {
            $this->_errors['payfast_recurring_payment_start_date'][] = __('PayFast Recurring Payment Start date has an invalid format.');
        }

        if (!$this->getScheduleDescription()) {
            $this->_errors['schedule_description'][] = __('Schedule description must be provided.');
        }

        if (!$this->getBillingPeriodUnit() || !$this->_billingPeriodUnitsOptions->getOptionText($this->getBillingPeriodUnit())
        ) {
            $this->_errors['billing_period_unit'][] = __('Billing period unit is not defined or wrong.');
        }

        if ($this->getBillingPeriodFrequency() && !$this->_validatePeriodFrequency('billing_period_unit', 'billing_period_frequency')) {
            $this->_errors['billing_period_frequency'][] = __('Billing period frequency is wrong.');
        }

        if ($this->getIsTrialAvailable()) {
            if ($this->getTrialPeriodUnit()) {
                if (!$this->_billingPeriodUnitsOptions->getOptionText($this->getTrialPeriodUnit())) {
                    $this->_errors['trial_period_unit'][] = __('Trial period unit is wrong.');
                }
                if (!$this->getTrialPeriodFrequency() || !$this->_validatePeriodFrequency('trial_period_unit', 'trial_period_frequency')
                ) {
                    $this->_errors['trial_period_frequency'][] = __('Trial period frequency is wrong.');
                }
                if (!$this->getTrialPeriodMaxCycles()) {
                    $this->_errors['trial_period_max_cycles'][] = __('Trial period max cycles is wrong.');
                }
                if (!$this->getTrialPeriodAmount()) {
                    $this->_errors['trial_period_amount'][] = __('Trial period amount is wrong.');
                }
            }
        }

        if (!$this->getBillingAmount() || 0 >= $this->getBillingAmount()) {
            $this->_errors['billing_amount'][] = __('We found a wrong or empty billing amount specified.');
        }


        if (!$this->getCurrencyCode()) {
            $this->_errors['currency_code'][] = __('The currency code is undefined.');
        }

        if (!$this->_manager || !$this->getMethodCode()) {
            $this->_errors['method_code'][] = __('The payment method code is undefined.');
        }

        if ($this->_manager) {
            try {
                $this->_manager->validate($this);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_errors['payment_method'][] = $e->getMessage();
            }
        }

        $this->_logger->debug(__METHOD__ . ' : errors', $this->_errors);
        return empty($this->_errors);
    }

    /**
     * GetValidationErrors
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return array
     */
    public function getValidationErrors()
    {
        if ($this->_errors) {
            $result = [];
            foreach ($this->_errors as $row) {
                $result[] = implode(' ', $row);
            }
            throw new \Magento\Framework\Exception\LocalizedException(
                __("The payment is invalid:\n%1.", implode("\n", $result))
            );
        }
        return $this->_errors;
    }

    /**
     * SetManager
     *
     * @param ManagerInterface $object object
     *
     * @return $this
     */
    public function setManager(ManagerInterface $object)
    {
        $this->_manager = $object;
        return $this;
    }

    /**
     * ImportBuyRequest
     *
     * @param \Magento\Framework\DataObject $buyRequest buyRequest
     *
     * @return PayfastRecurringPayment
     *@throws \Magento\Framework\Exception\LocalizedException
     *
     */
    public function importBuyRequest(\Magento\Framework\DataObject $buyRequest)
    {
        $recurringPaymentStartDate = $buyRequest->getData(self::RECURRING_PAYMENT_START_DATE);
        if ($recurringPaymentStartDate) {
            if (!$this->_localeDate || !$this->_store) {
                throw new \Exception('Locale and store instances must be set for this operation.');
            }
            $dateFormat = $this->_localeDate->getDateFormat(
                \IntlDateFormatter::SHORT
            );
            $localeCode = $this->_localeResolver->getLocale();
            if (!\Zend_Date::isDate($recurringPaymentStartDate, $dateFormat, $localeCode)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The PayFast recurring payment start date has invalid format.'));
            }

            if (strtotime($recurringPaymentStartDate) < strtotime(date("d-m-Y", strtotime($this->_localeDate->formatDate())))) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The PayFast recurring payment start date must be future date.'));
            }

            $utcDate =  $this->_localeDate->date($recurringPaymentStartDate)->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $this->setPaypalRecurringPaymentStartDate($utcDate)->setImportedPaypalRecurringPaymentStartDate($recurringPaymentStartDate);
        }
        return $this->_filterValues();
    }

    /**
     * ImportProduct
     *
     * @param Product $product product
     *
     * @return bool|PayfastRecurringPayment
     */
    public function importProduct(Product $product)
    {
        $pre = __METHOD__ . ' : ';
        $this->_logger->debug($pre . 'bof');
//        billing_period_frequency
        if ($product->getIsPayfastRecurring()) {
            $this->setScheduleDescription(trim($product->getPfScheduleDescription()));
            $this->setIsStartDateEditable($product->getPfIsStartDateEditable());
            $this->setBillingPeriodFrequency($product->getPfBillingPeriodFrequency());
            $this->setBillingPeriodMaxCycles($product->getPfBillingPeriodMaxCycles());
            $this->setPfInitialAmount($product->getPfInitialAmount());
            $this->setSubscriptionType($product->getSubscriptionType());

            // automatically set product name if there is no schedule description
            if (!$this->hasScheduleDescription()) {
                $this->setScheduleDescription(trim($product->getName()));
            }

            // collect start datetime from the product options
            $options = $product->getCustomOption(self::PRODUCT_OPTIONS_KEY);
            if ($options) {
                $options = $this->serializer->unserialize($options->getValue());
                if (is_array($options)) {
                    if (isset($options['recurring_payment_start_date'])) {
                        $paypalRecurringPaymentStartDate = $this->_dateTime->formatDate($options['recurring_payment_start_date']);
                        $this->setNearestPayfastRecurringPaymentStartDate($this->_dateTime, $paypalRecurringPaymentStartDate);
                    }
                }
            }

            return $this->_filterValues();
        }
        return false;
    }


    /**
     * ExportPaypalRecurringPaymentScheduleInfo
     *
     * @return array
     */
    public function exportPayfastRecurringPaymentScheduleInfo()
    {
        return [
            new \Magento\Framework\DataObject(
                [
                'title' => __('Subscription Details'),
                'schedule' => $this->_renderSchedule('billing_period_unit', 'billing_period_frequency', 'billing_period_max_cycles'),
                ]
            )
        ];
    }

    /**
     * SetNearestPaypalRecurringPaymentStartDate
     *
     * @param null $date        date
     * @param null $date_string date_string
     *
     * @return $this
     */
    protected function setNearestPayfastRecurringPaymentStartDate($date = null, $date_string = null)
    {
        if (!$date || $date->strToTime($date_string) < time()) {
            $date = $this->_localeDate->date()->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        } else {
            $date =  $this->_localeDate->date($date_string)->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        }

        $this->setPayfastRecurringPaymentStartDate($date);

        return $this;
    }

    /**
     * exportPayfasRecurringPaymentStartDate
     *
     * @return string
     */
    public function exportPayfasRecurringPaymentStartDate()
    {
        $datetime = $this->getPayfastRecurringPaymentStartDate();
        if (!$datetime || !$this->_localeDate || !$this->_store) {
            return '';
        }
        $date = $this->_localeDate->scopeDate($this->_store, strtotime($datetime), true);
        return $date->format("l, jS \of F Y");
    }

    /**
     * SetStore
     *
     * @param \Magento\Store\Model\Store $store store
     *
     * @return $this
     */
    public function setStore(\Magento\Store\Model\Store $store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * RenderData
     *
     * @param string $key key
     *
     * @return mixed|string
     */
    public function renderData($key)
    {
        $value = $this->_getData($key);
        switch ($key) {
            case 'period_unit':
                return $this->_billingPeriodUnitsOptions->toOptionArray()[$value];
            case 'method_code':
                if (!$this->_paymentMethods) {
                    $this->_paymentMethods = $this->_paymentData->getPaymentMethodList(false);
                }
                if (isset($this->_paymentMethods[$value])) {
                    return $this->_paymentMethods[$value];
                }
                break;
            case 'paypal_recurring_payment_start_date':
                return $this->exportPayfastRecurringPaymentStartDate();
        }
        return $value;
    }

    /**
     * FilterValues
     *
     * @return $this
     */
    protected function _filterValues()
    {
        // determine payment method/code
        if ($this->_manager) {
            $this->setMethodCode($this->_manager->getPaymentMethodCode());
        } elseif ($this->getMethodCode()) {
            $this->getManager();
        }

        return $this;
    }

    /**
     * GetManager
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return ManagerInterface|null
     */
    protected function getManager()
    {
        if (!$this->_manager) {
            $this->_manager = $this->_managerFactory->create(
                ['paymentMethod' => $this->_paymentData->getMethodInstance($this->getMethodCode())]
            );
        }
        return $this->_manager;
    }
    /**
     * IsActiveMethod
     *
     * @return bool
     */
    public function isActiveMethod()
    {
        $activePaymentMethods = $this->payment_config->getActiveMethods();
        return isset($activePaymentMethods['payfast_payfast']) ? true : false;
    }
    /**
     * ValidatePeriodFrequency
     *
     * @param string $unitKey      unitKey
     * @param string $frequencyKey frequencyKey
     *
     * @return bool
     */
    protected function _validatePeriodFrequency($unitKey, $frequencyKey)
    {
        return !($this->getData($unitKey) == BillingPeriodUnitsOptions::SEMI_MONTH && $this->getData($frequencyKey) != 1);
    }

    /**
     * ValidateBeforeSave
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return mixed
     */
    protected function _validateBeforeSave()
    {
        if (!$this->isValid()) {
            throw new \Magento\Framework\Exception\LocalizedException($this->getValidationErrors());
        }
        if (!$this->getInternalReferenceId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('An internal reference ID is required to save the payment.'));
        }
    }

    /**
     * RenderSchedule
     *
     * @param string $periodKey    periodKey
     * @param string $frequencyKey frequencyKey
     * @param string $cyclesKey    cyclesKey
     *
     * @return array
     */
    protected function _renderSchedule($periodKey, $frequencyKey, $cyclesKey)
    {
        $result = [];

        $period = $this->_getData($periodKey);
        $frequency = (int)$this->_getData($frequencyKey);


        $result[] = __('%1', $this->_getData('schedule_description'));

        if ((int)$this->_getData('subscription_type') === SubscriptionType::RECURRING_SUBSCRIPTION) {
            $result[] = __('%1 cycle.', $this->_frequency->getOptionText($frequency));
            $cycles = (int)$this->_getData($cyclesKey);
            if ($cycles) {
                $result[] = __('Repeats %1 time(s)', $cycles);
            } else {
                $result[] = __('Repeats until Paused or canceled.');
            }
            if (empty($this->_getData('pf_initial_amount')) && !empty($this->storedData['initial_amount'])) {
                $this->setPfInitialAmount($this->storedData['initial_amount']);
            }
            $result[] = __('Initial Amount : %1', $this->amountRenderer->currency($this->_getData('pf_initial_amount'),true, false));
        }

        $this->_logger->debug(__METHOD__ . ' : results is ', $result);
        return $result;
    }
}

<?php

/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Model;

require_once dirname(__FILE__) . '/../Model/payfast_common.inc';


use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedExceptionFactory;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;


 /**
  * PayFast Module.
  *
  * @method                                         \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
  * @SuppressWarnings(PHPMD.TooManyFields)
  * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
  */

class Payfast
{
    /**
     * @var string
     */
    protected $_code = Config::METHOD_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = 'Payfast\Payfast\Block\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Payfast\Payfast\Block\Payment\Info';

    /**
     * @var string
     */
    protected $_configType = 'Payfast\Payfast\Model\Config';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * Website Payments Pro instance
     *
     * @var Config $config
     */
    protected $_config;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var LocalizedExceptionFactory
     */
    protected $_exception;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ConfigFactory $configFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param Session $checkoutSession
     * @param LocalizedExceptionFactory $exception
     * @param TransactionRepositoryInterface $transactionRepository
     * @param BuilderInterface $transactionBuilder
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ConfigFactory $configFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        Session $checkoutSession,
        LocalizedExceptionFactory $exception,
        TransactionRepositoryInterface $transactionRepository,
        BuilderInterface $transactionBuilder,
        ProductRepository $productRepository
    ) {
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_checkoutSession = $checkoutSession;
        $this->_exception = $exception;
        $this->transactionRepository = $transactionRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->productRepository = $productRepository;
        $parameters = [ 'params' => [ $this->_code ] ];

        $this->_config = $configFactory->create($parameters);

        if (! defined('PF_DEBUG')) {
            define('PF_DEBUG', $this->_config->getValue('debug'));
        }
    }

    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param Store|int $store
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStore($store)
    {
        $this->setData('store', $store);

        if (null === $store) {
            $store = $this->_storeManager->getStore()->getId();
        }
        $this->_config->setStoreId(is_object($store) ? $store->getId() : $store);

        return $this;
    }

    /**
     * Whether method is available for specified currency
     *
     * @param string $currencyCode
     *
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->_config->isCurrencyCodeSupported($currencyCode);
    }

    /**
     * Payment action getter compatible with payment model
     *
     * @see    \Magento\Sales\Model\Payment::place()
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->_config->getPaymentAction();
    }

    /**
     * Check whether payment method can be used
     *
     * @param CartInterface|Quote|null $quote
     *
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null)
    {
        return $this->_config->isMethodAvailable();
    }

    /**
     * @return mixed
     */
    protected function getStoreName()
    {
        $pre = __METHOD__ . " : ";
        pflog($pre . 'bof');

        $storeName = $this->_config->getValue(
            'general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        pflog($pre . 'store name is ' . $storeName);

        return $storeName;
    }

    /**
     * this where we compile data posted by the form to payfast
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStandardCheckoutFormFields()
    {
        $pre = __METHOD__ . ' : ';
        // Variable initialization

        $order = $this->_checkoutSession->getLastRealOrder();

        $description = '';

        pflog($pre . 'serverMode : ' . $this->_config->getValue('server'));

        // If NOT test mode, use normal credentials
        if ($this->_config->getValue('server') == 'live') {
            $merchantId = $this->_config->getValue('merchant_id');
            $merchantKey = $this->_config->getValue('merchant_key');
        }
        // If test mode, use generic sandbox credentials
        else {
            $merchantId = '10000100';
            $merchantKey = '46f0cd694581a';
        }

        // Create description
        foreach ($order->getAllItems() as $items) {
            $description .= $this->getNumberFormat($items->getQtyOrdered()) . ' x ' . $items->getName() . ';';
        }

        $pfDescription = trim(substr($description, 0, 254));

        // Construct data for the form
        $data = [
            // Merchant details
            'merchant_id' => $merchantId,
            'merchant_key' => $merchantKey,
            'return_url' => $this->getPaidSuccessUrl(),
            'cancel_url' => $this->getPaidCancelUrl(),
            'notify_url' => $this->getPaidNotifyUrl(),

            // Buyer details
            'name_first' => $order->getData('customer_firstname'),
            'name_last' => $order->getData('customer_lastname'),
            'email_address' => $order->getData('customer_email'),

            // Item details
            'm_payment_id' => $order->getRealOrderId(),
            'amount' => $this->getTotalAmount($order),
            'item_name' => $this->_storeManager->getStore()->getName() . ', Order #' . $order->getRealOrderId(),
             //this html special characters breaks signature.
            //'item_description' => $pfDescription,
        ];
        $data = array_merge($data, $this->subscriptionData());
        $passPhrase = trim($this->_config->getValue('passphrase'));
        if (!empty($passPhrase) && $this->_config->getValue('server') !== 'test') {
            $data["passphrase"] = $passPhrase;
        }

        ksort($data);
        $pfOutput = '';
        // Create output string
        foreach ($data as $key => $val) {
            if (!empty($val)) {
                $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }

        $pfOutput = substr($pfOutput, 0, -1);

        pflog($pre . 'pfOutput for signature is : ' . $pfOutput);

        $pfSignature = md5($pfOutput);

        $data['signature'] = $pfSignature;
        $data['user_agent'] = 'Magento ' . $this->getAppVersion();
        pflog($pre . 'data is :' . print_r($data, true));

        return($data);
    }

    private function subscriptionData()
    {
        $pre = __METHOD__ . ' : ';
        pflog($pre. 'load recurring data if product is of type recurring');

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_checkoutSession->getLastRealOrder();
        $data = [];

        foreach ($order->getAllItems() as $item) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($item->getProductId(), false, $this->_storeManager->getStore()->getId());
            if ($product->getIsPayfastRecurring()) {
                $data['subscription_type'] = (int) $product->getSubscriptionType();
                $data['item_name'] = trim(substr($product->getName(), 0,254));
                $data['item_description'] = trim(substr($product->getPfScheduleDescription(), 0, 256));

                if ($data['subscription_type'] === SubscriptionType::RECURRING_SUBSCRIPTION) {
                    $data['frequency'] = $product->getPfBillingPeriodFrequency();
                    $data['cycles'] = $product->getPfBillingPeriodMaxCycles();
                    $data['amount'] =  $this->getTotalAmount($order);
                    $data['recurring_amount'] = $this->getNumberFormat($product->getPfRecurringAmount());
//                    $data['billing_date'] = $product->getPfBillingDate();
                }
            }
        }

        pflog($pre . 'subscription data is '. print_r($data, true));

        return $data;
    }
    /**
     * getAppVersion
     *
     * @return string
     */
    private function getAppVersion(): string
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();

        return  (preg_match('([0-9])', $version)) ? $version : '2.0.0';
    }
    /**
     * getTotalAmount
     */
    public function getTotalAmount($order): string
    {
        if ($this->_config->getValue('use_store_currency')) {
            $price = $this->getNumberFormat($order->getGrandTotal());
        } else {
            $price = $this->getNumberFormat($order->getBaseGrandTotal());
        }

        return $price;
    }

    /**
     * getNumberFormat
     */
    public function getNumberFormat($number)
    {
        return number_format($number, 2, '.', '');
    }

    /**
     * getPaidSuccessUrl
     */
    public function getPaidSuccessUrl()
    {
        return $this->_urlBuilder->getUrl('payfast/redirect/success', [ '_secure' => true ]);
    }

    /**
     * Get transaction with type order
     *
     * @param OrderPaymentInterface $payment
     *
     * @return false|TransactionInterface
     */
    protected function getOrderTransaction($payment)
    {
        return $this->transactionRepository->getByTransactionType(Transaction::TYPE_ORDER, $payment->getId(), $payment->getOrder()->getId());
    }

    /*
     * called dynamically by checkout's framework.
     */
    public function getOrderPlaceRedirectUrl()
    {
        $pre = __METHOD__ . " : ";
        pflog($pre . 'bof');

        return $this->_urlBuilder->getUrl('payfast/redirect');
    }
    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see    \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see    Quote\Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        $pre = __METHOD__ . " : ";
        pflog($pre . 'bof');

        return $this->_urlBuilder->getUrl('payfast/redirect');
    }


    /**
     * getPaidCancelUrl
     */
    public function getPaidCancelUrl()
    {
        return $this->_urlBuilder->getUrl('payfast/redirect/cancel', [ '_secure' => true ]);
    }
    /**
     * getPaidNotifyUrl
     */
    public function getPaidNotifyUrl()
    {
        return $this->_urlBuilder->getUrl('payfast/notify', [ '_secure' => true ]);
    }

    /**
     * getPayFastUrl
     *
     * Get URL for form submission to PayFast.
     */
    public function getPayFastUrl()
    {
        return('https://' . $this->getPayfastHost($this->_config->getValue('server')) . '/eng/process');
    }

    /**
     * @param $serverMode
     *
     * @return string
     */
    public function getPayfastHost($serverMode)
    {
        if (!in_array($serverMode, [ 'live', 'test' ])) {
            $pfHost = "payfast.{$serverMode}";
        } else {
            $pfHost = (($serverMode == 'live') ? 'www' : 'sandbox') . '.payfast.co.za';
        }

        return $pfHost;
    }
}

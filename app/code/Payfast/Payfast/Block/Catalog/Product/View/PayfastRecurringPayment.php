<?php
/**
 * Class PayfastRecurringPayment
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Payfast_Payfast
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Block\Catalog\Product\View;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;
use Psr\Log\LoggerInterface;

/**
 * Class PayfastRecurringPayment
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class PayfastRecurringPayment extends \Magento\Framework\View\Element\Template
{
    /**
     * Payfast_recurring_payment
     *
     * @var \Payfast\Payfast\Model\PayfastRecurringPayment $payfast_recurring_payment
     */
    protected $payfast_recurring_payment = false;

    /**
     * PayfastRecurringPaymentFactory
     *
     * @var \Payfast\Payfast\Model\PayfastRecurringPayment $PayfastRecurringPaymentFactory
     */
    protected $payfastRecurringPayment;

    /**
     * Config
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $payment_config;

    protected $logger;
    private ProductRepository $productRepository;
    private StoreManagerInterface $storeManager;

    /**
     * PayfastRecurringPayment constructor.
     *
     * @param Context $context context
     * @param \Payfast\Payfast\Model\PayfastRecurringPaymentFactory $payfastRecurringPayment payfastRecurringPayment
     * @param \Magento\Payment\Model\Config $payment_config payment_config
     * @param LoggerInterface $logger
     * @param array $data data
     */
    public function __construct(
        Context $context,
        \Payfast\Payfast\Model\PayfastRecurringPaymentFactory $payfastRecurringPayment,
        \Magento\Payment\Model\Config $payment_config,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->payfastRecurringPayment = $payfastRecurringPayment;
        $this->payment_config = $payment_config;
        $this->logger = $logger;
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * GetPayfastRecurringPaymentScheduleInfo
     *
     * @return array
     */
    public function getPayfastRecurringPaymentScheduleInfo()
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');

        $scheduleInfo = [];
        foreach ($this->payfast_recurring_payment->exportPayfastRecurringPaymentScheduleInfo() as $info) {
            $scheduleInfoTitle = (string)$info->getTitle();
            if (!empty($scheduleInfoTitle)) {
                $scheduleInfo[$scheduleInfoTitle] = $info->getSchedule();
            }
        }

        return $scheduleInfo;
    }


    /**
     * IsActiveMethod
     *
     * @return bool
     */
    public function isActiveMethod()
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');

        $activePaymentMethods = $this->payment_config->getActiveMethods();
        return isset($activePaymentMethods['payfast']);
    }

    /**
     * IsStartDateEditable
     *
     * @return mixed
     */
    public function isStartDateEditable()
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');
        return $this->payfast_recurring_payment->getIsStartDateEditable();
    }

    /**
     * PrepareLayout
     *
     * @return \Magento\Framework\View\Element\Template
     */
    protected function _prepareLayout()
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');

        $product = $this->getProduct();
        if ($product) {
            $this->payfast_recurring_payment = $this->payfastRecurringPayment->create()->importProduct($product);
        }
        return parent::_prepareLayout();
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isScheduledProduct()
    {
        $product = $this->getProduct();
        return ($product->getIsPayfastRecurring() && (int) $product->getSubscriptionType() === SubscriptionType::RECURRING_SUBSCRIPTION );
    }

     /**
     * ToHtml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');
        if (!$this->payfast_recurring_payment) {
            $this->_template = null;
        }

        return parent::_toHtml();
    }

    /**
     * @return Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProduct(): Product
    {
        $productId = $this->getRequest()->getParam('id');
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
        return $product;
    }
}

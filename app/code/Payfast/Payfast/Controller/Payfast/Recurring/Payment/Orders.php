<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Controller\Payfast\Recurring\Payment;

use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Exception\LocalizedException as LocalizedException;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;

/**
 * Class Orders
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za
 * @link     https://www.payfast.co.za
 */
class Orders extends Grid
{
    /**
     * CustomerSession
     *
     * @var $customerSession
     */
    protected $customerSession = null;

    /**
     * CoreRegistry
     *
     * @var $coreRegistry
     */
    protected $coreRegistry = null;

    /**
     * PageFactory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * ResultFactory
     *
     * @var Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * Payment
     *
     * @var \Payfast\Payfast\Model\Payment
     */
    protected $paymentModel;

    /**
     * Logger
     *
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * Orders constructor.
     *
     * @param \Magento\Framework\App\Action\Context            $context           context
     * @param \Magento\Framework\Registry                      $coreRegistry      coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory resultPageFactory
     * @param ResultFactory                                    $resultFactory     resultFactory
     * @param \Payfast\Payfast\Model\Payment $paymentModel      paymentModel
     * @param \Magento\Customer\Model\Session                  $customerSession   customerSession
     * @param \Zend\Log\Logger                                 $logger            logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ResultFactory $resultFactory,
        \Payfast\Payfast\Model\Payment $paymentModel,
        \Magento\Customer\Model\Session $customerSession,
        \Zend\Log\Logger $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        parent::__construct($context, $coreRegistry, $resultPageFactory, $resultFactory, $paymentModel, $customerSession);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $payment = $this->_initPayment();
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->prepend(__('%1 Reference # %2', SubscriptionType::RECURRING_LABEL[$payment->getSubscriptionType()],$payment->getReferenceId()));
                return $resultPage;
            } else {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/grid');
    }
}

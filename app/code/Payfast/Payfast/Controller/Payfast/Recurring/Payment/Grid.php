<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Controller\Payfast\Recurring\Payment;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Payfast\Payfast\Model\Payment;

/**
 * Class Grid
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Grid extends \Magento\Framework\App\Action\Action
{
    /**
     * Session
     *
     * @var Session|null
     */
    protected $customerSession = null;

    /**
     * Registry
     *
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * Title
     *
     * @var $title
     */
    protected $title = null;

    /**
     * PageFactory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * ResultFactory
     *
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Payment
     *
     * @var Payment
     */
    protected $paymentModel;

    /**
     * Grid constructor.
     *
     * @param Context            $context           context
     * @param Registry                      $coreRegistry      coreRegistry
     * @param PageFactory       $resultPageFactory resultPageFactory
     * @param ResultFactory                                    $resultFactory     resultFactory
     * @param Payment $paymentModel      paymentModel
     * @param Session                  $customerSession   customerSession
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ResultFactory $resultFactory,
        Payment $paymentModel,
        Session $customerSession
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        $this->paymentModel = $paymentModel;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('PayFast Recurring Payments'));
            return $resultPage;
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
    }

    /**
     * ViewAction
     *
     * @return mix
     */
    public function viewAction()
    {
        $this->_viewAction();
    }

    /**
     * OrdersAction
     *
     * @return mix
     */
    public function ordersAction()
    {
        $this->_viewAction();
    }

    /**
     * UpdateStateAction
     *
     * @return mix
     */
    public function updateStateAction()
    {
        $payment = null;
        try {
            $payment = $this->_initPayment();
            switch ($this->getRequest()->getParam('action')) {
                case 'cancel':
                    $payment->cancel();
                    break;
                case 'suspend':
                    $payment->suspend();
                    break;
                case 'activate':
                    $payment->activate();
                    break;
                default:
                    break;
            }
            $this->messageManager->addSuccess(__('The payment state has been updated.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the payment.'));
        }
        if ($payment) {
            $this->_redirect('*/*/view', ['payment' => $payment->getId()]);
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * UpdatePaymentAction
     *
     * @return mix
     */
    public function updatePaymentAction()
    {
        $payment = null;
        try {
            $payment = $this->_initPayment();
            $payment->fetchUpdate();
            if ($payment->hasDataChanges()) {
                $payment->save();
                $this->messageManager->addSuccess(__('The payment has been updated.'));
            } else {
                $this->messageManager->addNotice(__('The payment has no changes.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the payment.'));
        }
        if ($payment) {
            $this->_redirect('*/*/view', ['payment' => $payment->getId()]);
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * ViewAction
     *
     * @return mix
     */
    protected function _viewAction()
    {
        try {
            $payment = $this->_initPayment();
            $this->title->add(__('PayFast Recurring Billing Payments'));
            $this->title->add(__('Payment #%1', $payment->getReferenceId()));
            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * InitPayment
     *
     * @return Payment
     * @throws LocalizedException
     */
    protected function _initPayment()
    {
        $payment = $this->paymentModel->load($this->getRequest()->getParam('payment'));
        if (!$payment->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Something is wrong. Please try again'));
        }
        $this->coreRegistry->register('current_payfast_recurring_payment', $payment);
        return $payment;
    }
}

<?php
/**
 * Class Index
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Controller\Adminhtml\Payfast\Recurring\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Payfast\Payfast\Model\Payment;
use Psr\Log\LoggerInterface;

/**
 * Class Index
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Index extends Action
{
    /**
     * PARAM_CUSTOMER_ID
     */
    const PARAM_CUSTOMER_ID = 'id';
    /**
     * PARAM_PAYMENT
     */
    const PARAM_PAYMENT = 'payment';
    /**
     * PARAM_ACTION
     */
    const PARAM_ACTION = 'action';
    /**
     * ACTION_CANCEL
     */
    const ACTION_CANCEL = 'cancel';
    /**
     * ACTION_SUSPEND
     */
    const ACTION_SUSPEND = 'suspend';
    /**
     * ACTION_ACTIVATE
     */
    const ACTION_ACTIVATE = 'activate';

    /**
     * Registry
     *
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * LoggerInterface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * PageFactory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Payment
     *
     * @var \Sparsh\PaypalRecurringPayment\Model\Payment
     */
    protected $paymentModel;

    /**
     * Index constructor.
     *
     * @param Context              $context           context
     * @param Registry                      $coreRegistry      coreRegistry
     * @param LoggerInterface                         $logger            logger
     * @param PageFactory       $resultPageFactory resultPageFactory
     * @param Payment $paymentModel      paymentModel
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        LoggerInterface $logger,
        PageFactory $resultPageFactory,
        Payment $paymentModel
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
        $this->paymentModel = $paymentModel;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Payfast_Payfast::payfast_recurring_payment');
        $resultPage->getConfig()->getTitle()->prepend(__('Payfast Recurring Payments'));
        return $resultPage;
    }

    /**
     * ViewAction
     *
     * @return mix
     */
    public function viewAction()
    {
        try {
            $this->_title->prepend(__('PayFast Recurring Billing Payments'));
            $payment = $this->_initPayment();
            $this->_view->loadLayout();
            $this->_setActiveMenu('Payfast_Payfast::recurring_payment');
            $this->_title->prepend(__('Payment #%1', $payment->getReferenceId()));
            $this->_view->renderLayout();
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->err($e);
        }
        $this->_redirect('sales/*/');
    }

    /**
     * GridAction
     *
     * @return mix
     */
    public function gridAction()
    {
        try {
            $this->_view->loadLayout()->renderLayout();
            return;
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->err($e);
        }
        $this->_redirect('sales/*/');
    }

    /**
     * OrdersAction
     *
     * @throws NotFoundException
     * @return mix
     */
    public function ordersAction()
    {
        try {
            $this->_initPayment();
            $this->_view->loadLayout()->renderLayout();
        } catch (\Exception $e) {
            $this->logger->err($e);
            throw new NotFoundException();
        }
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
            $action = $this->getRequest()->getParam(self::PARAM_ACTION);
            switch ($action) {
                case self::ACTION_CANCEL:
                    $payment->cancel();
                    break;
                case self::ACTION_SUSPEND:
                    $payment->suspend();
                    break;
                case self::ACTION_ACTIVATE:
                    $payment->activate();
                    break;
                default:
                    throw new \Exception(sprintf('Wrong action parameter: %s', $action));
            }
            $this->messageManager->addSuccess(__('The payment state has been updated.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We could not update the payment.'));
            $this->logger->err($e);
        }
        if ($payment) {
            $this->_redirect('sales/*/view', [self::PARAM_PAYMENT => $payment->getId()]);
        } else {
            $this->_redirect('sales/*/');
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
                $this->messageManager->addSuccess(__('You updated the payment.'));
            } else {
                $this->messageManager->addNotice(__('The payment has no changes.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We could not update the payment.'));
            $this->logger->err($e);
        }
        if ($payment) {
            $this->_redirect('sales/*/view', [self::PARAM_PAYMENT => $payment->getId()]);
        } else {
            $this->_redirect('sales/*/');
        }
    }

    /**
     * CustomerGridAction
     *
     * @return Layout
     */
    public function customerGridAction()
    {
        $customerId = (int)$this->getRequest()->getParam(self::PARAM_CUSTOMER_ID);
        if ($customerId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
        }
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * InitPayment
     *
     * @return \PayFast\Payfast\Model\Payment
     * @throws LocalizedException
     */
    protected function _initPayment()
    {
        $payment = $this->paymentModel->load($this->getRequest()->getParam(self::PARAM_PAYMENT));

        if (!$payment->getId()) {
            throw new LocalizedException(__('The payment you specified does not exist.'));
        }
        $this->coreRegistry->register('current_payfast_recurring_payment', $payment);
        return $payment;
    }

    /**
     * IsAllowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Payfast_Payfast::payfast_recurring_payment');
    }
}

<?php
/**
 * Class UpdateState
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PayfastRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Controller\Payfast\Recurring\Payment;

use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Exception\LocalizedException as LocalizedException;

/**
 * Class UpdateState
 *
 * @category Sparsh
 * @package  Sparsh_PayfastRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class UpdateState extends Grid
{
    /**
     * Session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession = null;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
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
     * UpdateState constructor.
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
        $payment = null;
        try {
            if ($this->customerSession->isLoggedIn()) {
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
                $this->messageManager->addSuccess(__('Payment status has been updated.'));
            } else {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We couldn\'t update the payment.'));
        }
        if ($payment) {
            $this->_redirect('*/*/view', ['payment' => $payment->getId()]);
        } else {
            $this->_redirect('*/*/grid');
        }
    }
}

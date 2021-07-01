<?php
/**
 * Class View
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

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;
use Payfast\Payfast\Model\Payment;

/**
 * Class View
 *
 * @category Sparsh
 * @package  Sparsh_PayfastRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class View extends Grid
{
    /**
     * Session
     *
     * @var Session
     */
    protected $customerSession = null;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * PageFactory
     *
     * @var PageFactory
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
     * @var Payment
     */
    protected $paymentModel;

    /**
     * Logger
     *
     * @var \Zend\Log\Logger
     */
    protected $logger;

    /**
     * View constructor.
     *
     * @param Context            $context           context
     * @param Registry                      $coreRegistry      coreRegistry
     * @param PageFactory       $resultPageFactory resultPageFactory
     * @param ResultFactory                                    $resultFactory     resultFactory
     * @param Payment $paymentModel      paymentModel
     * @param Session                  $customerSession   customerSession
     * @param \Zend\Log\Logger                                 $logger            logger
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ResultFactory $resultFactory,
        Payment $paymentModel,
        Session $customerSession,
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
                $resultPage->getConfig()->getTitle()->prepend(__('PayFast Recurring Payments'));
                $resultPage->getConfig()->getTitle()->prepend(__('%1 Reference # %2', SubscriptionType::RECURRING_LABEL[$payment->getSubscriptionType()],$payment->getReferenceId()));
                return $resultPage;
            } else {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->err($e);
        }
        $this->_redirect('*/*/grid');
    }
}

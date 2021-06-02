<?php
/**
 * Class View
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Controller\Adminhtml\Payfast\Recurring\Payment;

use \Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class View
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class View extends Index
{
    /**
     * LoggerInterface
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * PageFactory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Payment
     *
     * @var \Payfast\Payfast\Model\Payfast
     */
    protected $paymentModel;

    /**
     * View constructor.
     *
     * @param \Magento\Backend\App\Action\Context              $context           context
     * @param \Magento\Framework\Registry                      $coreRegistry      coreRegistry
     * @param \Psr\Log\LoggerInterface                         $logger            logger
     * @param \Magento\Framework\View\Result\PageFactory       $resultPageFactory resultPageFactory
     * @param \Payfast\Payfast\Model\Payment $paymentModel      paymentModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Payfast\Payfast\Model\Payment $paymentModel
    ) {
        $this->logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
        $this->paymentModel = $paymentModel;
        parent::__construct($context, $coreRegistry, $logger, $resultPageFactory, $paymentModel);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');
        try {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Payfast_Payfast::payfast_recurring_payment');
            $resultPage->getConfig()->getTitle()->prepend(__('PayFast Referrence # %1', $this->paymentModel->getReferenceId()));
            $payment = $this->_initPayment();
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->err($e);
        }
        $this->_redirect('sales/*');
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

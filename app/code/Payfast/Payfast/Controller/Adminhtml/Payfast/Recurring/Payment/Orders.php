<?php
/**
 * Class Orders
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Controller\Adminhtml\Payfast\Recurring\Payment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Payfast\Payfast\Model\Payment;
use Psr\Log\LoggerInterface;

/**
 * Class Orders
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Orders extends Index
{
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
     * Orders constructor.
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
        $this->logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry, $logger, $resultPageFactory, $paymentModel);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->_initPayment();
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        } catch (LocalizedException $e) {
            $this->logger->err($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->err($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
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

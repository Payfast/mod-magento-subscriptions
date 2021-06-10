<?php
/**
 * Class UpdateState
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
use \Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Payfast\Payfast\Model\Payment;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateState
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class UpdateState extends Index
{
    /**
     * LoggerInterface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * UpdateState constructor.
     *
     * @param Context              $context           context
     * @param Registry                      $coreRegistry      coreRegistry
     * @param LoggerInterface                         $logger            logger
     * @param PageFactory       $resultPageFactory resultPageFactory
     * @param Payfast $paymentModel      paymentModel
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        LoggerInterface $logger,
        PageFactory $resultPageFactory,
        Payment $paymentModel
    ) {
        $this->logger = $logger;
        parent::__construct($context, $coreRegistry, $logger, $resultPageFactory, $paymentModel);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
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
                case self::ACTION_UNPAUSE:
                    $payment->unpause();
                    break;
                default:
                    throw new \Exception(sprintf('Wrong action parameter: %s', $action));
            }
            $this->messageManager->addSuccessMessage(__('Payment status has been updated.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We could not update the payment.'));
            $this->logger->err($e);
        }
        if ($payment) {
            $this->_redirect('sales/*/view', [self::PARAM_PAYMENT => $payment->getId()]);
        } else {
            $this->_redirect('sales/*/');
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

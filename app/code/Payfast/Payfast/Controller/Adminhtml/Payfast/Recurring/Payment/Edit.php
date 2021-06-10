<?php

/**
 * Class UpdatePayment
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
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException as LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Result\PageFactory;
use Payfast\Payfast\Model\Payment;
use Psr\Log\LoggerInterface;

class Edit extends Index implements ActionInterface, HttpPostActionInterface
{

    protected ArrayManager $arrayManger;

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
        Payment $paymentModel,
        ArrayManager $arrayManager
    ) {
        $this->arrayManger = $arrayManager;
        parent::__construct($context, $coreRegistry,  $logger, $resultPageFactory, $paymentModel);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');
        $responseContent = [
            'success' => false,
            'message' => __('Failed to Update Subscription.'),
            'code' => 400,
        ];

        try {
            $payment = $this->_initPayment();

            $data = $this->_request->getParams();

            $data['guid'] = $payment->getReferenceId();

            $result = $payment->update($data);

//            {"code":"400","status":"error","data":{"result":"Date cannot be today's date or in the past"}}

            if ((int)$this->arrayManger->get('code', $result, 200) === 200 ) {
                $responseContent['message']= __('Successfully updated %1', $this->paymentModel->getScheduleDescription());
            } elseif ((int) $this->arrayManger->get('code',$result, 400) === 400 ) {
                $responseContent['message'] = __($this->arrayManger->get('data/result', $result));
            }

        } catch (LocalizedException $e) {
            $this->logger->err($e);
            $responseContent['message'] = __($e->getMessage());

        } catch (\Exception $e) {
            $this->logger->err($e);
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $resultJson->setData($responseContent);

        return  $resultJson;
    }
}

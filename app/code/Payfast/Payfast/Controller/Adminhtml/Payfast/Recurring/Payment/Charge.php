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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Result\PageFactory;
use Payfast\Payfast\Model\Payment;
use Psr\Log\LoggerInterface;


class Charge extends Index implements ActionInterface, HttpPostActionInterface
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
        $this->logger->debug($pre . 'bof', $this->_request->getParams());
        $error = null;
        $responseContent = [
            'success' => false,
            'message' => __('Failed to charge.'),
            'code' => 400,
        ];

        try {
            $payment = $this->_initPayment();

            if (empty((float)$this->_request->getParam('amount'))) {
                $error[] = PHP_EOL .__('Invalid amount provided');
            }

            if (empty($this->_request->getParam('description'))) {
                $error[] = PHP_EOL.__('Empty description Not allowed');
            }

            if (!empty($error)) {
                throw new LocalizedException(__('%1', implode(', AND ',$error )));
            }

            $data = $this->_request->getParams();
            $data['guid'] = $payment->getReferenceId();
            $result = $payment->charge($data);

            if (!empty($result['code']) && $result['code'] === 200 && $this->arrayManger->get('data/message', $result, 'Success') !== 'Failure') {
                $responseContent['message']= __($result['data']['message']);
            } elseif ($this->arrayManger->exists('data/response/reason', $result)) {
                $responseContent['message'] = __($this->arrayManger->get('data/response/reason',$result));
            }

        } catch (LocalizedException $e) {
            $responseContent['message'] = __('Error: %1', $e->getMessage());
            $this->logger->err($e->getMessage());
        } catch (\Exception $e) {
            $responseContent['message'] = __('Error: %1', $e->getMessage());
            $this->logger->err($e->getMessage());
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $resultJson->setData($responseContent);

        return  $resultJson;

    }
}

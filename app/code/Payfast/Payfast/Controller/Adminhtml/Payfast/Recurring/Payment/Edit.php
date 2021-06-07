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

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Exception\LocalizedException as LocalizedException;

class Edit extends Index implements ActionInterface, HttpPostActionInterface
{


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $pre = __METHOD__ . ' : ';
        $this->logger->debug($pre . 'bof');

        try {
            $payment = $this->_initPayment();

            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Payfast_Payfast::payfast_recurring_payment');

            $resultPage->getConfig()->getTitle()->prepend(__('PayFast Referrence # %1', $payment->getReferenceId()));


            if (empty((float)$this->_request->getParam('amount'))) {
                $this->messageManager->addWarningMessage('Invalid amount provided');
                return $resultPage;
            }

            if (empty($this->_request->getParam('description'))) {
                $this->messageManager->addWarningMessage('Empty description Not allowed');
                return $resultPage;
            }

            $data = $this->_request->getParams();
            $data['guid'] = $payment->getReferenceId();
            $payment->charge($data);


            $this->logger->debug($pre . 'params', $this->_request->getParams());

            $this->messageManager->addSuccessMessage('Successfully charged token');

            $this->_redirect('sales/*/view', [self::PARAM_PAYMENT => $payment->getId()]);

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('sales/*/view', [self::PARAM_PAYMENT => $payment->getId(), ]);
        } catch (\Exception $e) {
            $this->logger->err($e);
        }
        $this->_redirect('sales/*');
    }
}

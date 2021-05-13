<?php
/**
 * Class Data
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Payment\View;

/**
 * Class Data
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Data extends \Payfast\Payfast\Block\Payment\View
{
    /**
     * PrepareLayout
     *
     * @return \Payfast\Payfast\Block\Payment\View|void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->addData(
            [
            'reference_id' => $this->_payfastRecurringPayment->getReferenceId(),
            'can_cancel'   => $this->_payfastRecurringPayment->canCancel(),
            'cancel_url'   => $this->getUrl(
                '*/*/updatestate',
                [
                    'payment' => $this->_payfastRecurringPayment->getId(),
                    'action' => 'cancel'
                ]
            ),
            'can_suspend'  => $this->_payfastRecurringPayment->canSuspend(),
            'suspend_url'  => $this->getUrl(
                '*/*/updatestate',
                [
                    'payment' => $this->_payfastRecurringPayment->getId(),
                    'action' => 'suspend'
                ]
            ),
            'can_activate' => $this->_payfastRecurringPayment->canActivate(),
            'activate_url' => $this->getUrl(
                '*/*/updatestate',
                [
                    'payment' => $this->_payfastRecurringPayment->getId(),
                    'action' => 'activate'
                ]
            ),
            'can_update'   => $this->_payfastRecurringPayment->canFetchUpdate(),
            'update_url'   => $this->getUrl(
                '*/*/updatepayment',
                [
                    'payment' => $this->_payfastRecurringPayment->getId()
                ]
            ),
            'back_url'     => $this->getUrl('*/*/grid'),
            'confirmation_message' => __('Are you sure?'),
            ]
        );
    }
}

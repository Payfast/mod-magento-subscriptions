<?php
/**
 * Class View
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Block\Adminhtml\Payfast\Recurring\Payment;

/**
 * Class View
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class View extends \Magento\Backend\Block\Widget\Container
{
    /**
     * Registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $coreRegistry = null;

    /**
     * Payment
     *
     * @var \Payfast\Payfast\Model\Payfast
     */
    protected $recurringPaymentModel;

    /**
     * View constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context            $context               context
     * @param \Magento\Framework\Registry                      $registry              registry
     * @param \Payfast\Payfast\Model\Payment $recurringPaymentModel recurringPaymentModel
     * @param array                                            $data                  data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Payfast\Payfast\Model\Payment $recurringPaymentModel,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->recurringPaymentModel = $recurringPaymentModel;
        parent::__construct($context, $data);
    }

    /**
     * PrepareLayout
     *
     * @return \Magento\Backend\Block\Widget\Container
     */
    protected function _prepareLayout()
    {
        $this->addButton(
            'back',
            [
            'label'     => __('Back'),
            'onclick'   => "setLocation('{$this->getUrl('sales/payfast_recurring_payment/index')}')",
            'class'     => 'back',
            ]
        );

        $payment = $this->recurringPaymentModel->load($this->getRequest()->getParam('payment'));

        $confirmationMessage = __('Are you sure?');

        // cancel
        if ($payment->canCancel()) {
            $url = $this->getUrl('*/*/updatestate', ['payment' => $payment->getId(), 'action' => 'cancel']);
            $this->addButton(
                'cancel',
                [
                'label'     => __('Cancel'),
                'onclick'   => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                'class'     => 'delete',
                ]
            );
        }

        // suspend
        if ($payment->canSuspend()) {
            $url = $this->getUrl('*/*/updatestate', ['payment' => $payment->getId(), 'action' => 'suspend']);
            $this->addButton(
                'suspend',
                [
                'label'     => __('Pause'),
                'onclick'   => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                'class'     => 'delete',
                ]
            );
        }

        if ($payment->canUnpause()) {
            $url = $this->getUrl('*/*/updatestate', ['payment' => $payment->getId(), 'action' => 'unpause']);
            $this->addButton(
                'suspend',
                [
                    'label'     => __('Un Pause'),
                    'onclick'   => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                    'class'     => 'delete',
                ]
            );
        }
        // activate
        if ($payment->canActivate()) {
            $url = $this->getUrl('*/*/updatestate', ['payment' => $payment->getId(), 'action' => 'activate']);
            $this->addButton(
                'activate',
                [
                'label'     => __('Activate'),
                'onclick'   => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                'class'     => 'add',
                ]
            );
        }

        // get update
        if ($payment->canFetchUpdate()) {
            $url = $this->getUrl('*/*/updatepayment', ['payment' => $payment->getId(),]);
            $this->addButton(
                'update',
                [
                'label'     => __('Get Update'),
                'onclick'   => "confirmSetLocation('{$confirmationMessage}', '{$url}')",
                'class'     => 'add',
                ]
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * BeforeToHtml
     *
     * @return \Magento\Backend\Block\Widget\Container
     */
    protected function _beforeToHtml()
    {
        $payment = $this->coreRegistry->registry('current_payfast_recurring_payment');
        $this->_headerText = __('PayFast Recurring Payment # %1', $payment->getReferenceId());
        $this->setViewHtml('<div id="' . $this->getDestElementId() . '"></div>');
        return parent::_beforeToHtml();
    }
}

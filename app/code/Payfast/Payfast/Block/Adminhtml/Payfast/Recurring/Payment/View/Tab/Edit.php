<?php
/**
 * Class Info
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Adminhtml\Payfast\Recurring\Payment\View\Tab;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Payfast\Payfast\Model\Config\Source\Frequency;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;
use Payfast\Payfast\Model\Payment;
use Payfast\Payfast\Model\States;

/**
 * Class Charge
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Edit extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $payment;
    private Frequency $frequency;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Payment $payment,
        Frequency $frequency,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
        $this->payment = $payment;
        $this->frequency = $frequency;
    }

    public function _construct()
    {
        $this->_frontController = 'payfast_adminhtml_payfast_recurring_payment_edit';

        parent::_construct();
    }

    /**
     * GetTabLabel
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Edit');
    }

    public function getFrequecy()
    {
        $pre = __METHOD__ . ' : ';
        $this->_logger->debug(__($pre . 'frequency is ( %1 )', $this->payment->getData('billing_period_frequency')));
        return $this->payment->getData('billing_period_frequency');
    }

    public function getDescription()
    {
        return $this->payment->getData('schedule_description');
    }

    public function getNextRunDate()
    {

        return $this->getLayout()->createBlock('Magento\Framework\View\Element\Html\Date')
            ->setData([
                          'name' => 'next_run',
                          'id' => 'date',
                          'value' => date('Y-m-d', strtotime($this->payment->getData('recurring_payment_start_date'))),
//                          'date_format' => 'Y-m-d',
                          'date_format' => 'yyyy-mm-dd',
                          'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                          'years_range' => '-120y:c+nn',
                          'min_date' => '1d',
                          'max_date' => '12m',
                          'change_month' => 'true',
                          'change_year' => 'true',
                          'show_on' => 'both',
                          'first_day' => 1
                      ])
            ->toHtml();
    }

    public function getAmount()
    {
        return $this->payment->getAmountRender()->currency($this->payment->getBillingAmount(), false, false);
    }

    public function getCycles()
    {
        return $this->payment->getData('billing_period_max_cycles');
    }
    public function getMethodCode()
    {
        return 'payfast';
    }

    public function getChargeUrl()
    {
        return $this->getUrl('*/*/Edit', ['payment' => $this->payment->getId()]);
    }
    /**
     * GetTabTitle
     *
     * @return mixed
     */
    public function getTabTitle()
    {
        return $this->getLabel();
    }


    /**
     * CanShowTab
     *
     * @return bool
     */
    public function canShowTab()
    {
        $pre = __METHOD__ . ' : ';
        $this->_logger->debug($pre . 'bof');
        $this->_logger->debug($pre . ' looking at this id' .$this->payment->getId());
        return (int)$this->payment->getSubscriptionType() === SubscriptionType::RECURRING_SUBSCRIPTION
            && $this->payment->getState() === States::ACTIVE;
    }

    /**
     * IsHidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    public function getOptions()
    {
        return $this->frequency->getAllOptions();
    }

}

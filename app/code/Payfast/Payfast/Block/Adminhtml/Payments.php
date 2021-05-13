<?php
/**
 * Class Payments
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Adminhtml;

class Payments extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Payfast_Payfast
     *
     * @var string
     */
    protected $_blockGroup = 'Payfast_Payfast';

    /**
     * Adminhtml_payfast_recurring_payment
     *
     * @var string
     */
    protected $_controller = 'adminhtml_payfast_recurring_payment';

    /**
     * Set Grid setting
     *
     * @return Void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->header_text = __('PayFast Recurring Payments');
        $this->removeButton('add');
    }
}

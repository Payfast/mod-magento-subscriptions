<?php
/**
 * Class Info
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Adminhtml\Payfast\Recurring\Payment\View\Tab;
/**
 * Class Info
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Info extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * GetTabLabel
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Payment Information');
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
        return true;
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
}

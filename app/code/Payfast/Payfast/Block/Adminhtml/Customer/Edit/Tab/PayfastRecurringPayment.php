<?php
/**
 * Class PayfastRecurringPayment
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class PayfastRecurringPayment extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * CoreRegistry
     *
     * @var \Magento\Framework\Registry coreRegistry
     */
    protected $coreRegistry;

    /**
     * PayfastRecurringPayment constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context  context context
     * @param \Magento\Framework\Registry             $registry registry
     * @param array                                   $data     data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * GetCustomerId
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * GetTabLabel
     *
     * @return mixed
     */
    public function getTabLabel()
    {
        return __('PayFast Recurring Payments');
    }

    /**
     * GetTabTitle
     *
     * @return mixed
     */
    public function getTabTitle()
    {
        return __('PayFast Recurring Payments');
    }

    /**
     * CanShowTab
     *
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * IsHidden
     *
     * @return bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * GetTabClass
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * GetTabUrl
     *
     * @return mixed
     */
    public function getTabUrl()
    {
        return $this->getUrl('sales/payfast_recurring_payment/customerGrid', ['_current' => true]);
    }

    /**
     * IsAjaxLoaded
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }
}

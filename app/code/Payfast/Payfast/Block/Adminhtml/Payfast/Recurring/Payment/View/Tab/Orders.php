<?php
/**
 * Class Orders
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

use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Orders
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Orders extends \Magento\Backend\Block\Widget\Grid\Extended implements TabInterface
{
    /**
     * Registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $coreRegistry = null;

    /**
     * CollectionFactory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollection;

    /**
     * ConfigFactory
     *
     * @var \Magento\Sales\Model\Order\ConfigFactory
     */
    protected $orderConfig;

    /**
     * CollectionFilter
     *
     * @var \Payfast\Payfast\Model\ResourceModel\Order\CollectionFilter
     */
    protected $recurringCollectionFilter;

    /**
     * Orders constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                     $context                   context
     * @param \Magento\Backend\Helper\Data                                $backendHelper             backendHelper
     * @param \Magento\Framework\Registry                                 $coreRegistry              coreRegistry
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory  $orderCollection           orderCollection
     * @param \Magento\Sales\Model\Order\ConfigFactory                    $orderConfig               orderConfig
     * @param \Payfast\Payfast\Model\ResourceModel\Order\CollectionFilter $recurringCollectionFilter recurringCollectionFilter
     * @param array                                                       $data                      data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        \Payfast\Payfast\Model\ResourceModel\Order\CollectionFilter $recurringCollectionFilter,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->orderCollection = $orderCollection;
        $this->orderConfig = $orderConfig;
        $this->recurringCollectionFilter = $recurringCollectionFilter;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @return Void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('payfast_payfast_recurring_payment_orders')
            ->setUseAjax(true)
            ->setSkipGenerateContent(true);
    }

    /**
     * GetTabLabel
     *
     * @return mixed
     */
    public function getTabLabel()
    {
        return __('Related Orders');
    }

    /**
     * GetTabTitle
     *
     * @return mixed
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
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

    /**
     * PrepareCollection
     *
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $collection = $this->recurringCollectionFilter->byIds(
            $this->orderCollection->create(),
            $this->coreRegistry->registry('current_payfast_recurring_payment')->getId()
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * PrepareColumns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'real_order_id',
            [
            'header'=> __('Order'),
            'type'  => 'text',
            'index' => 'increment_id',
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                [
                'header'    => __('Purchase Point'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
                ]
            );
        }

        $this->addColumn(
            'created_at',
            [
            'header' => __('Purchased'),
            'index' => 'created_at',
            'type' => 'datetime'
            ]
        );

        $this->addColumn(
            'billing_name',
            [
            'header' => __('Bill-to Name'),
            'index' => 'billing_name',
            ]
        );

        $this->addColumn(
            'shipping_name',
            [
            'header' => __('Ship-To Name'),
            'index' => 'shipping_name',
            ]
        );

        $this->addColumn(
            'base_grand_total',
            [
            'header' => __('Grand Total (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
            ]
        );

        $this->addColumn(
            'grand_total',
            [
            'header' => __('Grand Total'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
            ]
        );

        $this->addColumn(
            'status',
            [
            'header' => __('Status'),
            'index' => 'status',
            'type'  => 'options',
            'options' => $this->orderConfig->create()->getStatuses(),
            ]
        );

        if ($this->_authorization->isAllowed('Magento_Sales::actions_view')) {
            $this->addColumn(
                'action',
                [
                    'header'    => __('Action'),
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => [
                        [
                            'caption' => __('View'),
                            'url'     => ['base'=>'sales/order/view'],
                            'field'   => 'order_id'
                        ]
                    ],
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
                ]
            );
        }

        return parent::_prepareColumns();
    }

    /**
     * GetRowUrl
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getId()]);
    }

    /**
     * GetGridUrl
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getTabUrl();
    }

    /**
     * GetTabUrl
     *
     * @return string
     */
    public function getTabUrl()
    {
        $recurringPayment = $this->coreRegistry->registry('current_payfast_recurring_payment');
        return $this->getUrl('*/*/orders', ['payment' => $recurringPayment->getId()]);
    }

    /**
     * GetTabClass
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }
}

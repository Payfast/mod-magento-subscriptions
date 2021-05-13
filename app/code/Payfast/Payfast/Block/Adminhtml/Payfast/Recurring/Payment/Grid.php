<?php
/**
 * Class Grid
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Adminhtml\Payfast\Recurring\Payment;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * CollectionFactory
     *
     * @var \Payfast\Payfast\Model\ResourceModel\Payment\CollectionFactory
     */
    protected $paymentCollection;

    /**
     * States
     *
     * @var \Payfast\Payfast\Model\States
     */
    protected $recurringStates;

    /**
     * Fields
     *
     * @var \Payfast\Payfast\Block\Fields
     */
    protected $fields;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                               $context           context
     * @param \Magento\Backend\Helper\Data                                          $backendHelper     backendHelper
     * @param \Payfast\Payfast\Model\ResourceModel\Payment\CollectionFactory $paymentCollection paymentCollection
     * @param \Payfast\Payfast\Model\States                           $recurringStates   recurringStates
     * @param \Payfast\Payfast\Block\Fields                           $fields            fields
     * @param array                                                                 $data              data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Payfast\Payfast\Model\ResourceModel\Payment\CollectionFactory $paymentCollection,
        \Payfast\Payfast\Model\States $recurringStates,
        \Payfast\Payfast\Block\Fields $fields,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->paymentCollection = $paymentCollection;
        $this->recurringStates = $recurringStates;
        $this->fields = $fields;
    }

    /**
     * Construct
     *
     * @return Void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('payfast_recurring_payment_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * PrepareCollection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->paymentCollection->create();
        $this->setCollection($collection);
        if (!$this->getParam($this->getVarNameSort())) {
            $collection->setOrder('payment_id', 'desc');
        }
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
            'reference_id',
            [
            'header' => $this->fields->getFieldLabel('reference_id'),
            'index' => 'reference_id',
            'html_decorators' => ['nobr']
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                [
                'header'     => __('Store'),
                'index'      => 'store_id',
                'type'       => 'store',
                'store_view' => true,
                'display_deleted' => true,
                ]
            );
        }

        $this->addColumn(
            'state',
            [
            'header' => $this->fields->getFieldLabel('state'),
            'index' => 'state',
            'type'  => 'options',
            'options' => $this->recurringStates->toOptionArray(),
            'html_decorators' => ['nobr']
            ]
        );

        $this->addColumn(
            'created_at',
            [
            'header' => $this->fields->getFieldLabel('created_at'),
            'index' => 'created_at',
            'type' => 'datetime',
            'html_decorators' => ['nobr']
            ]
        );

        $this->addColumn(
            'updated_at',
            [
            'header' => $this->fields->getFieldLabel('updated_at'),
            'index' => 'updated_at',
            'type' => 'datetime',
            'html_decorators' => ['nobr']
            ]
        );

        $this->addColumn(
            'schedule_description',
            [
            'header' => $this->fields->getFieldLabel('schedule_description'),
            'index' => 'schedule_description',
            ]
        );

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
        return $this->getUrl('sales/payfast_recurring_payment/view', ['payment' => $row->getId()]);
    }

    /**
     * GetGridUrl
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales/*/grid', ['_current'=>true]);
    }
}

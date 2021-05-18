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
namespace Payfast\Payfast\Block\Payment\Related\Orders;

use Payfast\Payfast\Block\Payment\GridView as View;

/**
 * Class Grid
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Grid extends View
{
    /**
     * Collection

     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orderCollection;

    /**
     * Config

     * @var \Magento\Sales\Model\Order\Config
     */
    protected $config;

    /**
     * Data

     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $amoutnRenderer;

    /**
     * CollectionFilter

     * @var \Payfast\Payfast\Model\ResourceModel\Order\CollectionFilter
     */
    protected $recurringCollectionFilter;

    /**
     * Payment
     *
     * @var \Payfast\Payfast\Model\Payfast
     */
    protected $paymentModel;

    /**
     * StoreManagerInterface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Session
     *
     * @var \Magento\Customer\Model\Session|null
     */
    protected $customerSession = null;

    /**
     * Grid constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context                 $context                   context
     * @param \Magento\Framework\Registry                                      $registry                  registry
     * @param \Payfast\Payfast\Model\Payment                     $paymentModel              paymentModel
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager              storeManager
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection              $collection                collection
     * @param \Magento\Sales\Model\Order\Config                                $config                    config
     * @param \Magento\Framework\Pricing\Helper\Data                           $amoutnRenderer            amoutnRenderer
     * @param \Payfast\Payfast\Model\ResourceModel\Order\CollectionFilter $recurringCollectionFilter recurringCollectionFilter
     * @param \Magento\Customer\Model\Session                                  $customerSession           customerSession
     * @param array                                                            $data                      data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Payfast\Payfast\Model\Payment $paymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\ResourceModel\Order\Collection $collection,
        \Magento\Sales\Model\Order\Config $config,
        \Magento\Framework\Pricing\Helper\Data $amoutnRenderer,
        \Payfast\Payfast\Model\ResourceModel\Order\CollectionFilter $recurringCollectionFilter,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentModel, $storeManager, $data);
        $this->amoutnRenderer = $amoutnRenderer;
        $this->orderCollection = $collection;
        $this->config = $config;
        $this->recurringCollectionFilter = $recurringCollectionFilter;
        $this->customerSession = $customerSession;
    }

    /**
     * PrepareRelatedOrders
     *
     * @param string $fieldsToSelect fieldsToSelect
     *
     * @return mixed
     */
    protected function _prepareRelatedOrders($fieldsToSelect = '*')
    {
        if (null === $this->_relatedOrders) {
            $this->orderCollection
                ->addFieldToSelect($fieldsToSelect)
                ->addFieldToFilter('customer_id', $this->customerSession->getCustomerId())
                ->setOrder('entity_id', 'desc');

            $this->_relatedOrders = $this->recurringCollectionFilter->byIds(
                $this->orderCollection,
                $this->_payfastRecurringPayment->getId()
            );
        }
    }

    /**
     * GetRecurringRelatedOrderByCustomer
     *
     * @return Void
     */
    public function getRecurringRelatedOrderByCustomer()
    {
        $this->_prepareRelatedOrders();
        return $this->_relatedOrders;
    }

    /**
     * PrepareLayout
     *
     * @return \Magento\Framework\View\Element\Template|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->_prepareRelatedOrders(
            [
            'increment_id', 'created_at', 'customer_firstname', 'customer_lastname', 'base_grand_total', 'status'
            ]
        );
        $this->_relatedOrders->addFieldToFilter(
            'state',
            [
            'in' => $this->config->getVisibleOnFrontStatuses()
            ]
        );

        $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class)
            ->setCollection($this->_relatedOrders)
            ->setIsOutputRequired(false);

        $this->setChild('pager', $pager);

        $this->setGridColumns(
            [
            new \Magento\Framework\DataObject(
                [
                'index' => 'increment_id',
                'title' => __('Order #'),
                'is_nobr' => true
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                'index' => 'created_at',
                'title' => __('Date'),
                'is_nobr' => true
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                'index' => 'customer_name',
                'title' => __('Customer Name'),
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                'index' => 'base_grand_total',
                'title' => __('Order Total'),
                'is_nobr' => true,
                'is_amount' => true,
                ]
            ),
            new \Magento\Framework\DataObject(
                [
                'index' => 'status',
                'title' => __('Order Status'),
                'is_nobr' => true
                ]
            ),
            ]
        );

        $orders = [];
        foreach ($this->_relatedOrders as $order) {
            $orders[] = new \Magento\Framework\DataObject(
                [
                'increment_id' => $order->getIncrementId(),
                'created_at' => $this->formatDate($order->getCreatedAt()),
                'customer_name' => $order->getCustomerName(),
                'base_grand_total' => $this->amoutnRenderer->currency(
                    $order->getBaseGrandTotal(),
                    false
                ),
                'status' => $order->getStatusLabel(),
                'increment_id_link_url' => $this->getUrl('sales/order/view/', ['order_id' => $order->getId()]),
                ]
            );
        }
        if ($orders) {
            $this->setGridElements($orders);
        }
    }
}

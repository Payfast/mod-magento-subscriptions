<?php
/**
 * Class CollectionFilter
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Model\ResourceModel\Order;

/**
 * Class CollectionFilter
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class CollectionFilter
{
    /**
     * Add filter by specified PayFast recurring payment id(s)
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection collection
     * @param array|int                                                               $ids        ids
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function byIds($collection, $ids)
    {
        $ids = (is_array($ids)) ? $ids : [$ids];
        $collection->getSelect()
            ->joinInner(
                ['rpo' => $collection->getTable('payfast_payfast_recurring_payment_order')],
                'main_table.entity_id = rpo.order_id',
                []
            )
            ->where('rpo.payment_id IN(?)', $ids);
        return $collection;
    }
}

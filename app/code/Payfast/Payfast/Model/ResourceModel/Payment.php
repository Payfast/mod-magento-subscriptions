<?php
/**
 * Class Payment
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
//use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
/**
 * Class Payment
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Payment extends AbstractDb
{
    /**
     * Connection
     *
     * @var
     */
    protected $connection;

    /**
     * Payment constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context                 context
     * @param Snapshot                                          $entitySnapshot          entitySnapshot
     * @param RelationComposite                                 $entityRelationComposite entityRelationComposite
     * @param null                                              $connectionName          connectionName
     */
//    public function __construct(
//        \Magento\Framework\Model\ResourceModel\Db\Context $context,
//        Snapshot $entitySnapshot,
//        RelationComposite $entityRelationComposite,
//        $connectionName = null
//    ) {
//        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
//    }

//    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context)
//    {
//        parent::__construct($context);
//    }

    /**
     * Get connection to perform core queries and etc
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false from parent
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $this->connection = parent::getConnection();
        }
        return $this->connection;
    }

    /**
     * Initialize main table and column
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('payfast_payfast_recurring_payment', 'payment_id');

        $this->_serializableFields = [
            'payment_vendor_info' => [null, []],
            'additional_info' => [null, []],
            'order_info' => [null, []],
            'order_item_info' => [null, []],
            'billing_address_info' => [null, []],
            'shipping_address_info' => [null, []]
        ];
    }


    /**
     * Return PayFast recurring payment child Orders Ids
     *
     * @param \Magento\Object $object object
     *
     * @return array
     */
    public function getChildOrderIds($object)
    {
        $adapter = $this->_getReadAdapter();
        $bind = [':payment_id' => $object->getId()];
        $select = $adapter->select()
            ->from(
                ['main_table' => $this->getTable('payfast_payfast_recurring_payment_order')],
                ['order_id']
            )
            ->where('payment_id=:payment_id');

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Add order relation to payfast_payfast_recurring_payment_order table
     *
     * @param int $recurringPaymentId recurringPaymentId
     * @param int $orderId            orderId
     *
     * @return $this
     */
    public function addOrderRelation($recurringPaymentId, $orderId)
    {
        $this->getConnection()->insert(
            $this->getTable('payfast_payfast_recurring_payment_order'),
            [
                'payment_id' => $recurringPaymentId,
                'order_id' => $orderId
            ]
        );
        return $this;
    }
}

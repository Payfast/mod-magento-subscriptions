<?php
/**
 * Class Collection
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Model\ResourceModel\Payment;

/**
 * Class Collection
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection

{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'payfast_recurring_payment_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'payfast_recurring_payment_collection';

    /**
     * StoreManagerInterface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;


    protected function _construct()
    {
        $this->_init(
        \Payfast\Payfast\Model\Payment::class,
        \Payfast\Payfast\Model\ResourceModel\Payment::class
        );
    }
}

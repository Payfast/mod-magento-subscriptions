<?php
/**
 * Class States
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Model;

/**
 * Class States
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class States implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * UNKNOWN
     *
     * @var string
     */
    const UNKNOWN = 'unknown';

    /**
     *PENDING
     */
    const PENDING = 'pending';

    /**
     * ACTIVE
     */
    const ACTIVE = 'active';

    /**
     * SUSPENDED
     */
    const SUSPENDED = 'suspended';

    const PAUSE = 'pause';
    const PAUSED = 'paused';
    /**
     * CANCELED
     */
    const CANCELED = 'cancel';
    const CANCELLED = 'cancelled';

    /**
     * EXPIRED
     */
    const EXPIRED = 'expired';
    const UNPAUSE = 'unpause';

    /**
     * ToOptionArray
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::UNKNOWN => __('Not Initialized'),
            self::PENDING => __('Pending'),
            self::ACTIVE => __('Active'),
            self::SUSPENDED => __('Suspended'),
            self::PAUSE => __('Pause'),
            self::PAUSED => __('Paused'),
            self::UNPAUSE => __('unpause'),
            self::CANCELED => __('Cancel'),
            self::CANCELLED => __('Cancelled'),
            self::EXPIRED => __('Expired'),
        ];
    }
}

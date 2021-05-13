<?php
/**
 * Class BillingPeriodUnitsOptions
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class BillingPeriodUnitsOptions
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class BillingPeriodUnitsOptions extends AbstractSource
{
    /**
     * DAY
     */
    const DAY = 'day';
    /**
     * WEEK
     */
    const WEEK = 'week';
    /**
     * SEMI_MONTH
     */
    const SEMI_MONTH = 'semi_month';
    /**
     * MONTH
     */
    const MONTH = 'month';
    /**
     * YEAR
     */
    const YEAR = 'year';

    /**
     * GetAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [
            ['value'=> self::DAY, 'label' => __('Day')],
            ['value'=> self::WEEK ,'label'=> __('Week')],
            ['value'=> self::SEMI_MONTH, 'label' => __('Two Weeks')],
            ['value'=> self::MONTH, 'label' => __('Month')],
            ['value'=> self::YEAR, 'label' => __('Year')]
        ];
        return $this->_options;
    }

    /**
     * GetOptionText
     *
     * @param int|string $value value
     *
     * @return bool|string
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }

    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $columns = [];
        $attributeCode = $this->getAttribute()->getAttributeCode();

        $type = \Magento\Framework\DB\Ddl\Table::TYPE_TEXT;
        $columns[$attributeCode] = [
            'type' => $type,
            'length' => 255,
            'unsigned' => false,
            'nullable' => true,
            'default' => null,
            'extra' => null,
            'comment' => $attributeCode . ' column',
        ];

        return $columns;
    }

    /**
     * Retrieve Indexes for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = [];

        $index = sprintf('IDX_%s', strtoupper($this->getAttribute()->getAttributeCode()));
        $indexes[$index] = ['type' => 'index', 'fields' => [$this->getAttribute()->getAttributeCode()]];

        $sortable = $this->getAttribute()->getUsedForSortBy();
        if ($sortable && $this->getAttribute()->getFrontend()->getInputType() != 'multiselect') {
            $index = sprintf('IDX_%s_VALUE', strtoupper($this->getAttribute()->getAttributeCode()));

            $indexes[$index] = [
                'type' => 'index',
                'fields' => [$this->getAttribute()->getAttributeCode() . '_value'],
            ];
        }

        return $indexes;
    }
}

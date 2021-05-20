<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class BillingPeriodUnitsOptions
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za
 * @link     https://www.payfast.co.za
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

<?php


namespace Payfast\Payfast\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Frequency extends AbstractSource
{
        const MONTHLY = 3; //- Monthly
        const QUATERLY = 4; //- Quarterly
        const BIANNUALLY = 5; // - Biannually
        const ANNUAL = 6 ;// - Annual

    public function getAllOptions()
    {

        $this->_options = [
            ['value' => self::MONTHLY, 'label' => __('Monthly')],
            ['value' => self::QUATERLY, 'label' => __('Quaterly')],
            ['value' => self::BIANNUALLY, 'label' => __('Biannually')],
            ['value' => self::ANNUAL, 'label' => __('Annual')],
        ];
        return $this->_options;
    }
}

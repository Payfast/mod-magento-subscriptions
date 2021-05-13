<?php


namespace Payfast\Payfast\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class SubscriptionType extends AbstractSource
{
    const RECURRING_SUBSCRIPTION = 1; // Recurring Subscription
    const RECURRING_ADHOC = 2; // Ad hoc

    const RECURRING_LABEL = [
        self::RECURRING_SUBSCRIPTION => 'Recurring Subscription',
        self::RECURRING_ADHOC => 'Recurring Adhoc'
    ];



    public function getAllOptions()
    {
        $this->_options = [
            ['value' => self::RECURRING_SUBSCRIPTION, 'label' => __(self::RECURRING_LABEL[self::RECURRING_SUBSCRIPTION])],
            ['value' => self::RECURRING_ADHOC, 'label' => __(self::RECURRING_LABEL[self::RECURRING_ADHOC])],
        ];

        return $this->_options;
    }

}

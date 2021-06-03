<?php namespace Payfast\Payfast\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Payfast\Payfast\Model\Config\Source\Frequency;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;


class UpgradeData implements UpgradeDataInterface
{
    private $categorySetupFactory;

    /** @var CollectionFactory $resourceModelSet */
    private $resourceModelSet;
    /**
     * Eav setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;


    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory,
        CollectionFactory $resourceModelAttributeSet
    ) {
        $this->eavSetupFactory = $eavSetupFactory;

        $this->categorySetupFactory = $categorySetupFactory;
        $this->resourceModelSet = $resourceModelAttributeSet;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $pre = __METHOD__ . ' : ';

        $setup->startSetup();
        $logger = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);

        $logger->debug($pre . ' setting up PayFast product recurring attributes.');

        // CODE FOR CREATING ATTRIBUTE SET

        $groupName = 'Paypal Recurring Payment';

        $attributes = [
            'is_paypal_recurring'       => [
                'group'                      => $groupName,
                'type'                       => 'int',
                'input'                      => 'select',
                'label'                      => 'Enable PayPal Recurring Payment',
                'required'                   => false,
                'source'                     => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default'                    => '0',
                'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                   => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                 => 1,
            ],
            'schedule_description'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'text',
                'label'                     => 'Schedule Description',
                'frontend_class'            => 'validate-length maximum-length-127',
                'backend'                   => \Sparsh\PaypalRecurringPayment\Model\Attribute\Backend\ScheduleDescription::class,
                'note'                      => 'Enter a short description of the recurring payment. Allowed max lenght  127.',
                'required'                  => false,
                'unique'                    => true,
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 2,
            ],
            'is_start_date_editable'       => [
                'group'                     => $groupName,
                'type'                      => 'int',
                'input'                     => 'select',
                'label'                     => 'Can Customer Define Billing Start Date?',
                'note'                      => 'Select whether customer can define the date when billing for the payment begins.',
                'required'                  => false,
                'source'                    => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default'                   => '0',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 3,
            ],
            'billing_period_unit'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'select',
                'label'                     => 'Billing Period Unit',
                'note'                      => 'This is the unit of measure for billing cycle.',
                'required'                  => false,
                'source'                    => \Sparsh\PaypalRecurringPayment\Model\Config\Source\BillingPeriodUnitsOptions::class,
                'default'                   => 'day',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 4,
            ],
            'billing_period_frequency'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'text',
                'label'                     => 'Billing Period Frequency',
                'note'                      => 'This is the number of billing periods that make up one billing cycle.The combination of billing frequency and billing period must be less than or equal to one year.',
                'required'                  => false,
                'default'                   => 1,
                'frontend_class'            => 'validate-digits',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 5,
            ],
            'billing_period_max_cycles'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'text',
                'label'                     => 'Maximum Billing Cycles',
                'note'                      => 'This is the total number of billing cycles for the payment period.If you specify a value 0, the payments continue until PayPal (or the buyer) cancels or suspends the profile.',
                'required'                  => false,
                'default'                   => '',
                'frontend_class'            => 'validate-digits',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 6,
            ],
            'is_trial_available'       => [
                'group'                     => $groupName,
                'type'                      => 'int',
                'input'                     => 'select',
                'label'                     => 'Is Trial Period Available?',
                'note'                      => 'Is trial period subscription available for this product?',
                'required'                  => false,
                'source'                    => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default'                   => '0',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 7,
            ],
            'trial_period_unit'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'select',
                'label'                     => 'Trial Period Unit',
                'note'                      => 'This is the unit of measure for trial period.',
                'required'                  => false,
                'source'                    => \Sparsh\PaypalRecurringPayment\Model\Config\Source\BillingPeriodUnitsOptions::class,
                'default'                   => 'day',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 8,
            ],
            'trial_period_frequency'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'text',
                'label'                     => 'Trial Period Frequency',
                'note'                      => 'This is the number of trial periods that make up one cycle.',
                'required'                  => false,
                'default'                   => 1,
                'frontend_class'            => 'validate-digits',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 9,
            ],
            'trial_period_amount'       => [
                'group'                     => $groupName,
                'type'                      => 'decimal',
                'input'                     => 'price',
                'label'                     => 'Trial Period Amount',
                'note'                      => 'This is the trial period amount.',
                'required'                  => false,
                'backend'                   => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                'attribute_model'           => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 10,
            ],
            'trial_period_max_cycles'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'text',
                'label'                     => 'Maximum Trial Cycles',
                'note'                      => 'This is the total number of trial cycles for the payment period.',
                'required'                  => false,
                'default'                   => '',
                'frontend_class'            => 'validate-digits',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 11,
            ],
            'initial_amount'       => [
                'group'                     => $groupName,
                'type'                      => 'decimal',
                'input'                     => 'price',
                'label'                     => 'Initial Amount',
                'note'                      => 'The initial, non-recurring payment amount is due immediately when the payment is created.',
                'required'                  => false,
                'backend'                   => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                'attribute_model'           => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 12,
            ],
            'allow_initial_amount_failure'       => [
                'group'                     => $groupName,
                'type'                      => 'int',
                'input'                     => 'select',
                'label'                     => 'Allow Initial Amount Failure',
                'note'                      => 'This sets whether to suspend the payment if the initial fee fails or, instead, add the failed payment amount to the outstanding balance due on this recurring payment profile.',
                'required'                  => false,
                'source'                    => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default'                   => '0',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 13,
            ],
            'max_allowed_payment_failures'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'text',
                'label'                     => 'Maximum Allowed Payment Failures',
                'note'                      => 'This is the number of failed payments allowed before profile is automatically suspended.',
                'required'                  => false,
                'default'                   => 0,
                'frontend_class'            => 'validate-digits',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 14,
            ],
            'auto_bill_failures'       => [
                'group'                     => $groupName,
                'type'                      => 'int',
                'input'                     => 'select',
                'label'                     => 'Auto Bill on Next Cycle',
                'note'                      => 'Use this to automatically bill the outstanding balance amount in the next billing cycle (if there were failed payments).',
                'required'                  => false,
                'source'                    => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default'                   => '0',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 15,
            ]
        ];

        $eavSetup = $this->eavSetupFactory->create();
        foreach ($attributes as $attrCode => $attr) {
            $eavSetup->removeAttribute(Product::ENTITY, $attrCode);
        }

        $groupName = 'PayFast Recurring Payment';
        $eavSetup = $this->eavSetupFactory->create();


        $attributes = [
            'pf_billing_period_max_cycles'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'text',
                'label'                     => 'Maximum Billing Cycles',
                'note'                      => 'This is the total number of billing cycles for the payment period.If you specify a value 0, the payments continue until PayFast (or the buyer) cancels or suspends the profile.',
                'required'                  => false,
                'default'                   => '',
                'frontend_class'            => 'validate-digits',
                'backend'                   => \Payfast\Payfast\Model\Attribute\Backend\ScheduleCycles::class,
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 6,
            ],
        ];

        foreach ($attributes as $attrCode => $attr) {
            $eavSetup->removeAttribute(Product::ENTITY, $attrCode);
            $eavSetup->addAttribute(
                Product::ENTITY,
                $attrCode,
                $attr
            );
        }

        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Default');

        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'is_payfast_recurring');

        $setup->endSetup();
    }

    public function unInstall()
    {
    }
}

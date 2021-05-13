<?php
/**
 * Class InstallData
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   Lefu Ntho
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class InstallData implements InstallDataInterface
{
    private $attributeSetFactory;
    private $categorySetupFactory;

    /**
     * Eav setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;

    }

    /**
     * Install
     *
     * @param ModuleDataSetupInterface $setup   setup
     * @param ModuleContextInterface   $context context
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->installEntities($setup);
        $setup->endSetup();
    }

    /**
     * Default entites and attributes
     *
     * @param array|null $setup setup

     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function installEntities($setup)
    {
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
                'backend'                   => \Payfast\Payfast\Model\Attribute\Backend\ScheduleDescription::class,
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
                'source'                    => \Payfast\Payfast\Model\Config\Source\BillingPeriodUnitsOptions::class,
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
                'source'                    => \Payfast\Payfast\Model\Config\Source\BillingPeriodUnitsOptions::class,
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
            $eavSetup->addAttribute(
                Product::ENTITY,
                $attrCode,
                $attr
            );
        }

        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Default');

        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'is_paypal_recurring');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'schedule_description');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'is_start_date_editable');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'billing_period_unit');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'billing_period_frequency');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'billing_period_max_cycles');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'is_trial_available');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'trial_period_unit');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'trial_period_frequency');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'trial_period_amount');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'trial_period_max_cycles');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'initial_amount');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'allow_initial_amount_failure');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'max_allowed_payment_failures');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'auto_bill_failures');

    }
}

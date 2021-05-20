<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Payfast\Payfast\Model\Config\Source\Frequency;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;

/**
 * Class InstallData
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za
 * @link     https://www.payfast.co.za
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
    ) {
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
        $pre = __METHOD__ . ' : ';

        $setup->startSetup();
        $logger = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);

        $logger->debug($pre . ' setting up PayFast product recurring attributes.');

        // CODE FOR CREATING ATTRIBUTE SET
        $groupName = 'PayFast Recurring Payment';
        $eavSetup = $this->eavSetupFactory->create();


        $attributes = [
            'is_payfast_recurring'       => [
                'group'                      => $groupName,
                'type'                       => 'int',
                'input'                      => 'select',
                'label'                      => 'Enable PayFast Recurring Payment',
                'required'                   => false,
                'source'                     => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'default'                    => '0',
                'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                   => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                 => 1,
            ],

            'subscription_type'       => [
                'group'                      => $groupName,
                'type'                       => 'int',
                'input'                      => 'select',
                'label'                      => 'Subscription Type',
                'required'                   => false,
                'source'                     => SubscriptionType::class,
                'default'                    => '1',
                'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                   => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                 => 1,
            ],
            'pf_schedule_description'       => [
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
            'pf_billing_period_frequency'       => [
                'group'                     => $groupName,
                'type'                      => 'int',
                'input'                     => 'select',
                'label'                     => 'Billing Period Frequency',
                'note'                      => 'This is the number of billing periods that make up one billing cycle.The combination of billing frequency and billing period must be less than or equal to one year.',
                'required'                  => false,
                'default'                   => 1,
                'source'                    => Frequency::class,
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 5,
            ],
            'pf_billing_period_max_cycles'       => [
                'group'                     => $groupName,
                'type'                      => 'varchar',
                'input'                     => 'text',
                'label'                     => 'Maximum Billing Cycles',
                'note'                      => 'This is the total number of billing cycles for the payment period.If you specify a value 0, the payments continue until PayFast (or the buyer) cancels or suspends the profile.',
                'required'                  => false,
                'default'                   => '',
                'frontend_class'            => 'validate-digits',
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 6,
            ],
            'pf_is_start_date_editable'       => [
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
            'pf_initial_amount'       => [
                'group'                     => $groupName,
                'type'                      => 'decimal',
                'input'                     => 'price',
                'label'                     => 'Initial Amount',
                'note'                      => 'The Initial amount payment amount is due immediately when the payment is created.',
                'required'                  => false,
                'backend'                   => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                'attribute_model'           => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class,
                'global'                    => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'apply_to'                  => 'simple,configurable,virtual,bundle,downloadable',
                'sort_order'                => 12,
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
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'pf_schedule_description');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'pf_initial_amount');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'pf_billing_period_frequency');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'pf_billing_period_max_cycles');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'pf_is_start_date_editable');

        $setup->endSetup();
    }
}

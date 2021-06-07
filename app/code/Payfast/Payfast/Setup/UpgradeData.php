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

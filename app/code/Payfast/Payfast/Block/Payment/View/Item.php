<?php
/**
 * Class Item
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Payment\View;

/**
 * Class Item
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Item extends \Payfast\Payfast\Block\Payment\View
{
    /**
     * Option

     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $option;

    /**
     * Product
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * OptionFactory

     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $quoteItemOptionFactory;

    /**
     * Json
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serialize;

    /**
     * PaymentModel
     *
     * @var $paymentModel
     */
    protected $paymentModel;

    /**
     * ObjectManager
     *
     * @var $objectManager
     */
    protected $objectManager;

    /**
     * Item constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context                context
     * @param \Magento\Framework\Registry                      $registry               registry
     * @param \Payfast\Payfast\Model\Payfast $paymentModel           paymentModel
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager           storeManager
     * @param \Magento\Catalog\Model\Product\Option            $option                 option
     * @param \Magento\Catalog\Model\Product                   $product                product
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory    $quoteItemOptionFactory quoteItemOptionFactory
     * @param \Magento\Framework\Serialize\Serializer\Json     $serialize              serialize
     * @param array                                            $data                   data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Payfast\Payfast\Model\Payfast $paymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Option $option,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Model\Quote\Item\OptionFactory $quoteItemOptionFactory,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentModel, $storeManager, $data);
        $this->option = $option;
        $this->product = $product;
        $this->quoteItemOptionFactory = $quoteItemOptionFactory;
        $this->serialize = $serialize;
    }

    /**
     * PrepareLayout
     *
     * @return \Payfast\Payfast\Block\Payment\View|void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->_shouldRenderInfo = true;

        $key = 'order_item_info';

        foreach ([
            'name' => __('Product Name'),
            'sku' => __('SKU'),
            'qty' => __('Quantity')
        ] as $itemKey => $label) {
            $value = $this->_payfastRecurringPayment->getInfoValue($key, $itemKey);
            if ($value) {
                $this->_addInfo(['label' => $label, 'value' => $value,]);
            }
        }

        $request = $this->_payfastRecurringPayment->getInfoValue($key, 'info_buyRequest');
        if (empty($request)) {
            return;
        }

        $request = $this->serialize->unserialize($request);
        if (empty($request['options'])) {
            return;
        }

        $options = $this->option->getCollection()
            ->addIdsToFilter(array_keys($request['options']))
            ->addTitleToResult($this->_payfastRecurringPayment->getInfoValue($key, 'store_id'))
            ->addValuesToResult();

        foreach ($options as $option) {
            $quoteItemOption = $this->quoteItemOptionFactory->create()->setId($option->getId());

            $group = $option->groupFactory($option->getType())
                ->setOption($option)
                ->setRequest(new \Magento\Framework\DataObject($request))
                ->setProduct($this->product)
                ->setUseQuotePath(true)
                ->setQuoteItemOption($quoteItemOption)
                ->validateUserValue($request['options']);

            $skipHtmlEscaping = false;
            if ('file' == $option->getType()) {
                $skipHtmlEscaping = true;

                $downloadParams = [
                    'id' => $this->_payfastRecurringPayment->getId(),
                    'option_id' => $option->getId(),
                    'key' => $request['options'][$option->getId()]['secret_key']
                ];
                $group->setCustomOptionDownloadUrl('sales/download/downloadProfileCustomOption')
                    ->setCustomOptionUrlParams($downloadParams);
            }

            $optionValue = $group->prepareForCart();

            $this->_addInfo(
                [
                    'label' => $option->getTitle(),
                    'value' => $group->getFormattedOptionValue($optionValue),
                    'skip_html_escaping' => $skipHtmlEscaping
                ]
            );
        }
    }
}

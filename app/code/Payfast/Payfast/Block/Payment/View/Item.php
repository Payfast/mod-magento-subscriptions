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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Payfast\Payfast\Block\Payment\View;
use Payfast\Payfast\Model\Payment;

/**
 * Class Item
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Item extends View
{
    /**
     * Option

     * @var Option
     */
    protected $option;

    /**
     * Product
     *
     * @var Product
     */
    protected $product;

    /**
     * OptionFactory

     * @var OptionFactory
     */
    protected $quoteItemOptionFactory;

    /**
     * Json
     *
     * @var Json
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
     * @param Context $context                context
     * @param Registry                      $registry               registry
     * @param Payment $paymentModel           paymentModel
     * @param StoreManagerInterface       $storeManager           storeManager
     * @param Option            $option                 option
     * @param Product                   $product                product
     * @param OptionFactory    $quoteItemOptionFactory quoteItemOptionFactory
     * @param Json     $serialize              serialize
     * @param array                                            $data                   data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Payment $paymentModel,
        StoreManagerInterface $storeManager,
        Option $option,
        Product $product,
        OptionFactory $quoteItemOptionFactory,
        Json $serialize,
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
     * @return View|void
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

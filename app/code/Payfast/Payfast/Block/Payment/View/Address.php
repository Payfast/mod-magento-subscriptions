<?php
/**
 * Class Address
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
 * Class Address
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Address extends \Payfast\Payfast\Block\Payment\View
{
    /**
     * AddressFactory
     *
     * @var \Magento\Sales\Model\Order\AddressFactory
     */
    protected $_addressFactory;

    /**
     * Renderer
     *
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

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
     * Address constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context         context
     * @param \Magento\Framework\Registry                      $registry        registry
     * @param \Payfast\Payfast\Model\Payfast $paymentModel    paymentModel
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager    storeManager
     * @param \Magento\Sales\Model\Order\AddressFactory        $addressFactory  addressFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer      $addressRenderer addressRenderer
     * @param array                                            $data            data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Payfast\Payfast\Model\Payfast $paymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\AddressFactory $addressFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentModel, $storeManager, $data);
        $this->_addressFactory = $addressFactory;
        $this->addressRenderer = $addressRenderer;
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
        if ('shipping' == $this->getAddressType()) {
            if ('1' == $this->_payfastRecurringPayment->getInfoValue('order_item_info', 'is_virtual')) {
                $this->getParentBlock()->unsetChild('sales.paypal_recurring_payment.view.shipping');
                return;
            }
            $key = 'shipping_address_info';
        } else {
            $key = 'billing_address_info';
        }
        $this->setIsAddress(true);
        $address = $this->_addressFactory->create(['data' => $this->_payfastRecurringPayment->getData($key)]);
        $this->_addInfo(['value' => preg_replace('/\\n{2,}/', "\n", $this->addressRenderer->format($address, 'text'))]);
    }
}

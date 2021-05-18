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

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\AddressFactory;
use Magento\Store\Model\StoreManagerInterface;
use Payfast\Payfast\Model\Payment;

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
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * Renderer
     *
     * @var Renderer
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
     * @param Context $context         context
     * @param Registry                      $registry        registry
     * @param Payment $paymentModel    paymentModel
     * @param StoreManagerInterface       $storeManager    storeManager
     * @param AddressFactory        $addressFactory  addressFactory
     * @param Renderer      $addressRenderer addressRenderer
     * @param array                                            $data            data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Payment $paymentModel,
        StoreManagerInterface $storeManager,
        AddressFactory $addressFactory,
        Renderer $addressRenderer,
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

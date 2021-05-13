<?php
/**
 * Class Fees
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
 * Class Fees
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Fees extends \Payfast\Payfast\Block\Payment\View
{
    /**
     * Data
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $amoutnRenderer;

    /**
     * Fields
     *
     * @var \Payfast\Payfast\Block\Fields
     */
    protected $fields;

    /**
     * PaymentModel
     *
     * @var
     */
    protected $paymentModel;

    /**
     * ObjectManager
     *
     * @var
     */
    protected $objectManager;

    /**
     * Fees constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context        context
     * @param \Magento\Framework\Registry                      $registry       registry
     * @param \Payfast\Payfast\Model\Payfast $paymentModel   paymentModel
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager   storeManager
     * @param \Magento\Framework\Pricing\Helper\Data           $amoutnRenderer amoutnRenderer
     * @param \Payfast\Payfast\Block\Fields  $fields         fields
     * @param array                                            $data           data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Payfast\Payfast\Model\Payfast $paymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $amoutnRenderer,
        \Payfast\Payfast\Block\Fields $fields,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentModel, $storeManager, $data);
        ;
        $this->amoutnRenderer = $amoutnRenderer;
        $this->fields = $fields;
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
        $this->_addInfo(
            [
            'label' => $this->fields->getFieldLabel('currency_code'),
            'value' => $this->_payfastRecurringPayment->getCurrencyCode()
            ]
        );
        $params = ['initial_amount', 'trial_period_amount', 'billing_amount', 'tax_amount', 'shipping_amount'];
        foreach ($params as $key) {
            $value = $this->_payfastRecurringPayment->getData($key);
            if ($value) {
                $this->_addInfo(
                    [
                    'label' => $this->fields->getFieldLabel($key),
                    'value' => $this->amoutnRenderer->currency($value, true, false),
                    'is_amount' => true,
                    ]
                );
            }
        }
    }
}

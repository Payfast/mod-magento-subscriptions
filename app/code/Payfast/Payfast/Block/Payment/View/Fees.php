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

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Payfast\Payfast\Block\Fields;
use Payfast\Payfast\Block\Payment\View;
use Payfast\Payfast\Model\Payfast;
use Payfast\Payfast\Model\Payment;

/**
 * Class Fees
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Fees extends View
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
     * @var Fields
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
     * @param Context $context        context
     * @param Registry                      $registry       registry
     * @param Payment $paymentModel   paymentModel
     * @param StoreManagerInterface       $storeManager   storeManager
     * @param \Magento\Framework\Pricing\Helper\Data           $amountRenderer amoutnRenderer
     * @param Fields  $fields         fields
     * @param array                                            $data           data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Payment $paymentModel,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Pricing\Helper\Data $amountRenderer,
        Fields $fields,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentModel, $storeManager, $data);
        ;
        $this->amoutnRenderer = $amountRenderer;
        $this->fields = $fields;
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

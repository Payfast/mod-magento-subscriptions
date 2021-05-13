<?php
/**
 * Class Reference
 *
 * PHP version 7
 ** @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block\Payment\View;

/**
 * Class Reference
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Reference extends \Payfast\Payfast\Block\Payment\View
{
    /**
     * Fields
     *
     * @var \Payfast\Payfast\Block\Fields
     */
    protected $fields;

    /**
     * Payment
     *
     * @var \Payfast\Payfast\Model\Payment
     */
    protected $paymentModel;

    /**
     * StoreManagerInterface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Reference constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context      context
     * @param \Magento\Framework\Registry                      $registry     registry
     * @param \Payfast\Payfast\Model\Payfast $paymentModel paymentModel
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager storeManager
     * @param \Payfast\Payfast\Block\Fields  $fields       fields
     * @param array                                            $data         data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Payfast\Payfast\Model\Payfast $paymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Payfast\Payfast\Block\Fields $fields,
        array $data = []
    ) {
        parent::__construct($context, $registry, $paymentModel, $storeManager, $data);
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
        foreach (['method_code', 'reference_id', 'schedule_description', 'state'] as $key) {
            $this->_addInfo(
                [
                'label' => $this->fields->getFieldLabel($key),
                'value' => $this->_payfastRecurringPayment->renderData($key),
                ]
            );
        }
    }
}

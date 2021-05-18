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

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Payfast\Payfast\Block\Fields;
use Payfast\Payfast\Model\Payment;

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
     * @var Fields
     */
    protected $fields;

    /**
     * Payment
     *
     * @var Payment
     */
    protected $paymentModel;

    /**
     * StoreManagerInterface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Reference constructor.
     *
     * @param Context $context      context
     * @param Registry                      $registry     registry
     * @param Payment $paymentModel paymentModel
     * @param StoreManagerInterface       $storeManager storeManager
     * @param Fields  $fields       fields
     * @param array                                            $data         data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Payment $paymentModel,
        StoreManagerInterface $storeManager,
        Fields $fields,
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

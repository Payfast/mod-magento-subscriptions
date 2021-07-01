<?php
/**
 * Class Schedule
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
use Payfast\Payfast\Model\Payment;

/**
 * Class Schedule
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Schedule extends View
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
     * ObjectManager
     *
     * @var $objectManager
     */
    protected $objectManager;

    /**
     * Schedule constructor.
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
     * @return View|void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->_shouldRenderInfo = true;
        foreach (['recurring_payment_start_date'] as $key) {
            $this->_addInfo(
                [
                'label' => $this->fields->getFieldLabel($key),
                'value' => $this->_payfastRecurringPayment->renderData($key),
                ]
            );
        }

        foreach ($this->_payfastRecurringPayment->exportPayfastRecurringPaymentScheduleInfo() as $info) {
            $this->_addInfo(
                [
                'label' => $info->getTitle(),
                'value' => $info->getSchedule(),
                ]
            );
        }
    }
}

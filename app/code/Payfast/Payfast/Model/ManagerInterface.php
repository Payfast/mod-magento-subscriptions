<?php
/**
 * Interface ManagerInterface
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Model;

use \Magento\Payment\Model\Info as PaymentInfo;
use \Magento\Framework\DataObject;

/**
 * Interface ManagerInterface
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
interface ManagerInterface
{
    /**
     * Validate data
     *
     * @param PayfastRecurringPayment $payment payment
     *
     * @return void
     *@throws \Magento\Framework\Exception
     *
     */
    public function validate(PayfastRecurringPayment $payment);

    /**
     * Submit to the gateway
     *
     * @param PayfastRecurringPayment $payment     payment
     * @param PaymentInfo            $paymentInfo paymentInfo
     *
     * @return void
     */
    public function submit(PayfastRecurringPayment $payment, PaymentInfo $paymentInfo);

    /**
     * Fetch details
     *
     * @param string                        $referenceId referenceId
     * @param \Magento\Framework\DataObject $result      result
     *
     * @return void
     */
    public function getDetails($referenceId, DataObject $result);

    /**
     * Check whether can get PayPal recurring payment details
     *
     * @return bool
     */
    public function canGetDetails();

    /**
     * Update data
     *
     * @param PayfastRecurringPayment $payment payment
     *
     * @return void
     */
    public function update(PayfastRecurringPayment $payment);

    /**
     * Manage status
     *
     * @param PayfastRecurringPayment $payment payment
     *
     * @return void
     */
    public function updateStatus(PayfastRecurringPayment $payment);

    /**
     * Get  Payment Method code
     *
     * @return string
     */
    public function getPaymentMethodCode();
}

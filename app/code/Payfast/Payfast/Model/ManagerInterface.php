<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Model;

use \Magento\Payment\Model\Info as PaymentInfo;
use \Magento\Framework\DataObject;

/**
 * Interface ManagerInterface
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za
 * @link     https://www.payfast.co.za
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

<?php
/**
 * Class Fields
 *
 * PHP version 7
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
namespace Payfast\Payfast\Block;

/**
 * Class Fields
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast <lefu.ntho@payfast.co.za>
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Fields extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * GetFieldLabel
     *
     * @param string $field field
     *
     * @return mixed
     */
    public function getFieldLabel($field)
    {
        switch ($field) {
            case 'order_item_id':
                return __('Purchased Item');
            case 'state':
                return __('Status');
            case 'created_at':
                return __('Created Date');
            case 'updated_at':
                return __('Updated Date');
            case 'subscriber_name':
                return __('Subscriber Name');
            case 'paypal_recurring_payment_start_date':
                return __('Recurring Start Date');
            case 'internal_reference_id':
                return __('Internal Reference ID');
            case 'profile_id':
                return __('Profile Id');
            case 'schedule_description':
                return __('Schedule Description');
            case 'max_allowed_payment_failures':
                return __('Maximum Allowed Payment Failures');
            case 'auto_bill_failures':
                return __('Auto Bill on Next Cycle');
            case 'billing_period_unit':
                return __('Billing Period Unit');
            case 'billing_period_frequency':
                return __('Billing Period Frequency');
            case 'billing_period_max_cycles':
                return __('Maximum Billing Cycles');
            case 'billing_amount':
                return __('Billing Amount');
            case 'trial_period_unit':
                return __('Trial Period Unit');
            case 'trial_period_frequency':
                return __('Trial Period Frequency');
            case 'trial_period_max_cycles':
                return __('Maximum Trial Cycles');
            case 'trial_period_amount':
                return __('Trial Period Amount');
            case 'currency_code':
                return __('Currency');
            case 'shipping_amount':
                return __('Shipping Amount');
            case 'tax_amount':
                return __('Tax Amount');
            case 'initial_amount':
                return __('Initial Amount');
            case 'allow_initial_amount_failure':
                return __('Allow Initial Amount Failure');
            case 'method_code':
                return __('Payment Method');
            case 'reference_id':
                return __('Paypal Reference #');
        }
    }

    /**
     * GetFieldComment
     *
     * @param string $field field
     *
     * @return mixed
     */
    public function getFieldComment($field)
    {
        switch ($field) {
            case 'order_item_id':
                return __('Original order item that PayPal recurring payment corresponds to');
            case 'subscriber_name':
                return __(
                    'Full name of the person receiving the product or service paid for by the PayPal recurring payment.'
                );
            case 'paypal_recurring_payment_start_date':
                return __('This is the date when billing for the payment begins.');
            case 'schedule_description':
                return __(
                    'Enter a short description of the recurring payment. Allowed max lenght 127.'
                );
            case 'billing_period_unit':
                return __('This is the unit of measure for billing cycle.');
            case 'billing_period_frequency':
                return __('This is the number of billing periods that make up one billing cycle.The combination of billing frequency and billing period must be less than or equal to one year.');
            case 'billing_period_max_cycles':
                return __('This is the total number of billing cycles for the payment period.If you specify a value 0, the payments continue until PayPal (or the buyer) cancels or suspends the profile.');
            case 'initial_amount':
                return __('The initial, non-recurring payment amount is due immediately when the payment is created.');
            case 'allow_initial_amount_failure':
                return __(
                    'This sets whether to suspend the payment if the initial fee fails or, instead, add the failed payment amount to the outstanding balance due on this recurring payment profile.'
                );
            case 'max_allowed_payment_failures':
                return __(
                    'This is the number of failed payments allowed before profile is automatically suspended.'
                );
            case 'auto_bill_failures':
                return __(
                    'Use this to automatically bill the outstanding balance amount in the next billing cycle (if there were failed payments).'
                );
        }
    }
}

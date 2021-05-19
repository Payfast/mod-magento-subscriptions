<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Model;

/**
 * Interface PaymentTypeInterface
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @author   PayFast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
interface PaymentTypeInterface
{
    /**
     * REGULAR
     */
    const RECURRING = 'recurring';

    /**
     * TRIAL
     */
    const TRIAL = 'trial';

    /**
     * INITIAL
     */
    const INITIAL = 'initial';
}

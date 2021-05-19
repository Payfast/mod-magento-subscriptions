<?php namespace Payfast\Payfast\Block;
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
/**
 * Class Info
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za  Open Software License (OSL 3.0)
 * @link     https://www.payfast.co.za
 */
class Info extends ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param  string $field
     * @return Phrase
     */
    protected function getLabel($field): Phrase
    {
        return __($field);
    }
}

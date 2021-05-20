<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Plugin;

/**
 * Class ProductTypeOptions
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za
 * @link     https://www.payfast.co.za
 */
class ProductTypeOptions
{
    /**
     * ProductTypeOptions aroundHasOptions function
     *
     * @param \Magento\Catalog\Model\Product\Type\AbstractType $subject subject
     * @param \Closure                                         $proceed proceed
     * @param \Magento\Catalog\Model\Product                   $product product
     *
     * @return mixed
     */
    public function aroundHasOptions(
        \Magento\Catalog\Model\Product\Type\AbstractType $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        return $product->getIsPayfastRecurring() ?: $proceed($product);
    }
}

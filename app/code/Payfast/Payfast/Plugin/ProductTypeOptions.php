<?php
/**
 * Class ProductTypeOptions
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Plugin;

/**
 * Class ProductTypeOptions
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
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

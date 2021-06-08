<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */

namespace Payfast\Payfast\Model\Totals;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Payfast\Payfast\Model\Config\Source\SubscriptionType;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\Serialize\Serializer\Json;

class PayfastInvoice extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    private PriceCurrencyInterface $priceCurrency;

    private LoggerInterface $_logger;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        ProductRepository $productRepository,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($data);

        $this->productRepository = $productRepository;

        $this->priceCurrency = $priceCurrency;

        $this->_logger = $logger;
    }

    /**
     * @param Invoice $invoice
     * @return $this|\Payfast\Payfast\Model\Totals\PayfastInvoice
     *
     */
    public function collect(Invoice $invoice)
    {
        $items = $invoice->getItems();
        if (!count($items)) {
            return $this;
        }

        parent::collect($invoice);
        $label = __('Subscription discount');
        $discountAmount = -$this->priceCurrency->convert($this->evaluateDiscount($invoice));


        if ($invoice->getDiscountDescription()) {
            // If a discount exists in cart and another discount is applied, the add both discounts.
            $discountAmount = $invoice->getDiscountAmount() + $discountAmount;
            $label = $invoice->getDiscountDescription() . ', ' . $label;
        }

        $invoice->setDiscountAmount($discountAmount);
        $invoice->setDiscountDescription($label);
        $invoice->setDiscountAmount($discountAmount);
        $invoice->setBaseDiscountAmount($discountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $discountAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $discountAmount);
//        $invoice->setSubtotalWithDiscount($invoice->getSubtotal() + $discountAmount);
//        $invoice->setBaseSubtotalWithDiscount($invoice->getBaseSubtotal() + $discountAmount);

//        if (!empty($appliedCartDiscount)) {
//            $invoice->addTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
//            $invoice->addBaseTotalAmount($this->getCode(), $discountAmount - $appliedCartDiscount);
//        } else {
//            $invoice->addTotalAmount($this->getCode(), $discountAmount);
//            $invoice->addBaseTotalAmount($this->getCode(), $discountAmount);
//        }

        return $this;
    }

    /**
     * Only one subscription is in the invoice order items so we'll only
     *
     * @param Invoice $invoice
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function evaluateDiscount(Invoice $invoice)
    {
        $baseDiscountAmount = 0;

        /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();

            $product = $this->productRepository->getById($orderItem->getProductId());

            if ((int)$product->getSubscriptionType() === SubscriptionType::RECURRING_SUBSCRIPTION &&
                !is_null($product->getPfInitialAmount())
            ) {
                $discountPercentage = (($product->getPrice() - $product->getPfInitialAmount()) / $product->getPrice(
                )) * 100;
                $baseDiscountAmount = ($discountPercentage / 100) * $product->getPrice();
            }
        }

        return $baseDiscountAmount;
    }
}

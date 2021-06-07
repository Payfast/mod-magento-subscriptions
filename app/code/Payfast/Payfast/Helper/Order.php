<?php


namespace Payfast\Payfast\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Payfast\Payfast\Model\Payment;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    private \Magento\Store\Model\StoreManagerInterface $_storeManager;

    /** @var ProductInterfaceFactory $productFactory */
    protected $productFactory;
    private CustomerRepositoryInterface $customerRepository;
    private ProductRepositoryInterface $productRepository;

    /**
     * @var ObjectManagerInterface and I could place this as property type but it will break lower php versions
     */
    protected ObjectManagerInterface $_objectManager;

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Magento\Catalog\Model\Product $product
     * @param Magento\Framework\Data\Form\FormKey $formKey $formkey,
     * @param Magento\Quote\Model\Quote $quote ,
     * @param Magento\Customer\Model\CustomerFactory $customerFactory ,
     * @param Magento\Sales\Model\Service\OrderService $orderService ,
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        ProductRepositoryInterface $productRepository,
        ObjectManagerInterface $objectManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->_product = $product;
        $this->productRepository = $productRepository;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * create order programmatically in Magento 2
     *
     * @param $orderData
     * @param Payment $payment
     * @return \Magento\Framework\Model\AbstractExtensibleModel|\Magento\Sales\Api\Data\OrderInterface|object|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createMageOrder($orderData, Payment $payment)
    {
        $store = $this->_storeManager->getStore((int)$payment->getStoreId());

        /** @var  $qoute Quote*/
        $quote = $this->quote->create(); //Create object of quote
        $quote->setStore($store); //set store for which you create quote

        /** @var Customer $customer */
        $customer = $this->customerRepository->getById($payment->getCustomerId());
        $quote->setStoreCurrencyCode($payment->getCurrencyCode());

        $quote->assignCustomer($customer); //Assign quote to customer

        //add items in quote
        foreach ($orderData as $item) {
            $product = $this->productRepository->getById($item['product_id']);
            $product->setPrice($item->getPrice());

            $quoteItem = $this->_objectManager->create(\Magento\Quote\Model\Quote\Item::class);

            $quoteItem->setProduct($product)
                ->setPrice($item->getPrice())
                ->setQty($item->getQty())
                ->setFreeShipping(true)
            ;

            $quote->addItem($quoteItem);
        }


        //Set Address to quote
        $quote->getBillingAddress()->addData($payment->getBillingAddressInfo());
        $quote->getShippingAddress()->addData($payment->getShippingAddressInfo());

        // Collect Rates and Set Shipping & Payment Method

        $shippingAddress = $quote->getShippingAddress();

        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate')
        ; //shipping method

        $quote->setPayFastTotalPaid(true);
        $quote->setPaymentMethod($payment->getMethodCode()); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory
        $quote->save(); //Now Save quote and your quote is ready

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $payment->getMethodCode()]);

        // Collect Totals & Save Quote
        $collectTotals = $quote->collectTotals();

        $collectTotals->save();

        // Create Order From Quote
        return $this->quoteManagement->submit($quote, ['amount' =>$payment->getBillingAmount()]);
    }
}

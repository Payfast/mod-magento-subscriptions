<?php


namespace Payfast\Payfast\Observer;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface as LoggerInterfaceAlias;


use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManagerInterface;

class AllowToAddOnlyOneRecurringPaymentProductToCart implements ObserverInterface
{
    /** @var LoggerInterfaceAlias  */
    private LoggerInterfaceAlias $_logger;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Cart
     *
     * @var CartInterface
     */
    protected $_cart;

    /**
     * RestrictAddToCart constructor.
     *
     * @param ProductRepository $productRepository
     * @param ManagerInterface $messageManager
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductRepository $productRepository,
        ManagerInterface $messageManager,
        CheckoutSession $checkoutSession,
        StoreManagerInterface $storeManager,
        LoggerInterfaceAlias $logger
    ) {
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->_logger = $logger;
    }


    /**
     * All we need to do is prevent adding more items when its a subscription
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $pre = __METHOD__ . ' : ';
        $this->_logger->debug($pre . 'bof');
        try {
            $productId = $observer->getRequest()->getParam('product');

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());

            $cartItemsAll = $this->checkoutSession->getQuote()->getAllItems();
            $items= [];
            foreach ($cartItemsAll as $item) {
                if ($item->getIsPayfastRecurring()) {
                    $items[] = $item->getProduct();
                }
            }

            if(count($items) >=1 && $product->getIsPayfastRecurring()) {
                $this->_logger->debug($pre. 'You can only add 1 recurring subscription Item to your shopping cart at a time.');
                $this->messageManager->addErrorMessage(__('You can only add 1 recurring subscription Item to your shopping cart at a time.'));
                $observer->getRequest()->setParam('product', false);
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        //set false if you not want to add product to cart

        $this->_logger->debug($pre. 'eof');
    }
}

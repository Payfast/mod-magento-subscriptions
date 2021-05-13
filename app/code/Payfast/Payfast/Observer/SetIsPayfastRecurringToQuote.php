<?php
/**
 * Class SetIsPaypalRecurringToQuote
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Payfast\Payfast\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface as LoggerInterfaceAlias;

/**
 * Class SetIsPaypalRecurringToQuote
 *
 * @category Sparsh
 * @package  Sparsh_PaypalRecurringPayment
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class SetIsPayfastRecurringToQuote implements ObserverInterface
{
    private $_logger;

    /**
     * Execute
     *
     * @param Observer $observer observer
     *
     * @return null
     */
    public function __construct( LoggerInterfaceAlias $logger)
    {
        $this->_logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->_logger->debug(__METHOD__. ' : '. 'bof');

        $quote = $observer->getEvent()->getQuoteItem();
        $product = $observer->getEvent()->getProduct();
        $quote->setIsPayfastRecurring($product->getIsPayfastRecurring());

        $this->_logger->debug(__METHOD__. ' : '. 'bof');
    }
}

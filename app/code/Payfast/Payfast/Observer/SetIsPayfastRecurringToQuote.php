<?php
/**
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 */
namespace Payfast\Payfast\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface as LoggerInterfaceAlias;

/**
 * Class SetIsPaypalRecurringToQuote
 *
 * @category Payfast
 * @package  Payfast_Payfast
 * @license  https://www.payfast.co.za
 * @link     https://www.payfast.co.za
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
    public function __construct(LoggerInterfaceAlias $logger)
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

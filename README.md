# mod-magento_2.4.0

PayFast Magento Module v2.4.0 for Magento 2.4.0
-----------------------------------------------------------------------------
Copyright (c) 2008 PayFast (Pty) Ltd
You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.

******************************************************************************
                                                                            
    Please see the URL below for all information concerning this module:

             https://www.payfast.co.za/shopping-carts/magento/
******************************************************************************

In order to use PayFast 2.4.0 with Magento 2.4.0 you will need a working Magento 2.4.0 installation. To install PayFast follow the below instructions:

1. Setup ZAR on your Magento site.
    In the admin panel navigate to 'Stores', and add ZAR under currency Symbols and Rates.
2. Copy the PayFast app folder to your root Magento folder.
    This will not override any files on your system.
    
    2.1 install PayFast SDK with this command.
    `composer require payfast/payfast-php-sdk`
3. You will now need to run the following commands in the given order:
    
    3.1 php ./bin/magento module:enable PayFast_Payfast
    
    3.2 php ./bin/magento setup:di:compile  
    
    3.3 php ./bin/magento setup:static-content:deploy 
    
    3.4 php ./bin/magento cache:clean
    
4. Log into the admin panel and navigate to 'Stores'>'Configuration'>'Sales'>'Payment Method' and click on Payfast
5. Enable the module, as well as debugging. To test in sandbox insert 'test' in the 'server' field and use the following credentials:

    Merchant ID: 10000100
    
    Merchant Key: 46f0cd694581a
    
    Leave the passphrase blank and setup the other options as required.
        
   ##NB: configure sending of emails by default magento source code does not allow sending of emails when a payment module does a redirect.
   
6. Click 'Save Config', you are now ready to test in sandbox, click 'Save Config'.

7. Once you are ready to go live, insert 'live' into the 'server' field and input your PayFast credentials. Set debug log to 'No', and the other options as required.
8. Click 'Save Config', you are now ready to process live transactions via PayFast.

9. When you want to setup Subscription product for PayFast.
    9.1 You'll need log into your Magento store admin dashboard and select or create a product where you can enable PayFast Recurring Payment.
    You can choose type of Subscription type to you, only 2 options Recurring/Adhoc.
    
    9.2 Save product.
    9.3 Note that your customers can only checkout with 1 product of subscription in the cart, they can add other products to their cart.



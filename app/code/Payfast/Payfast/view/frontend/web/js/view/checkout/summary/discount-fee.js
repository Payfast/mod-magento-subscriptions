define(
   [
       'jquery',
       'Magento_Checkout/js/view/summary/abstract-total',
       'Magento_Checkout/js/model/quote',
       'Magento_Checkout/js/model/totals'
   ],
   function ($,Component,quote,totals) {
       "use strict";
       return Component.extend({
           defaults: {
               template: 'Payfast_Payfast/checkout/summary/discount-fee'
           },
           totals: quote.getTotals(),
           isDisplayedDiscountTotal : function () {
               return (parseFloat(totals.totals().discount_amount) < 0);
           },
           getDiscountTotal : function () {
               var price = totals.getSegment('discount').value;
               return this.getFormattedPrice(price);
           }
       });
   }
);

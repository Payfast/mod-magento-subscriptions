define(
   [
       'Payfast_Payfast/js/view/checkout/summary/discount-fee'
   ],
   function (Component) {
       'use strict';
       return Component.extend({
           /**
            * @override
            */
           isDisplayed: function () {
               return true;
           }
       });
   }
);

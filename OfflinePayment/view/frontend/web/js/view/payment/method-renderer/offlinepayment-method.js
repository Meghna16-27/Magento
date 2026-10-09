define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            defaults: {
                template: 'Codilar_OfflinePayment/payment/offlinepayment'
            },

            getCode: function () {
                return 'offlinepayment';
            },

            getTitle: function () {
                return 'Offline Payment';
            }

        });
    }
);

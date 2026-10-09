define([
    'Magento_Ui/js/grid/columns/multiselect'
], function (Multiselect) {
    'use strict';

    return Multiselect.extend({
        select: function (rowIndex) {
            // Remove any previously selected products
            this.selected.removeAll();

            // Select the newly checked product
            return this._super(rowIndex);
        },

        selectAll: function () {
            // Do not allow multiple products to be selected
            return this;
        }
    });
});

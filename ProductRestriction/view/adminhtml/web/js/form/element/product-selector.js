
define([
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry'
], function (Abstract, registry) {
    'use strict';

    return Abstract.extend({
        _selectionSubscribed: false,

        openProductModal: function () {
            var self = this;

            registry.get(
                'codilar_product_restriction_form.codilar_product_restriction_form.product_selector_modal',
                function (modal) {
                    modal.openModal();

                    if (self._selectionSubscribed) {
                        return;
                    }

                    registry.get(
                        'codilar_product_restriction_product_listing.codilar_product_restriction_product_listing.columns.ids',
                        function (selectionColumn) {
                            if (!selectionColumn || !selectionColumn.selected) {
                                console.error('Product selection component not found.');
                                return;
                            }

                            self._selectionSubscribed = true;

                            selectionColumn.selected.subscribe(function (selectedIds) {
                                if (!selectedIds || !selectedIds.length) {
                                    return;
                                }

                                var selectedId = selectedIds[selectedIds.length - 1];

                                registry.get(
                                    'codilar_product_restriction_product_listing.codilar_product_restriction_product_listing_data_source',
                                    function (provider) {
                                        var items = provider.data && provider.data.items
                                            ? provider.data.items
                                            : [];

                                        var product = items.find(function (item) {
                                            return String(item.entity_id) === String(selectedId);
                                        });

                                        if (product && product.sku) {
                                            self.value(product.sku);
                                            modal.closeModal();
                                        } else {
                                            console.warn(
                                                'Selected product SKU was not found in the current data.',
                                                selectedId
                                            );
                                        }
                                    }
                                );
                            });
                        }
                    );
                }
            );
        }
    });
});

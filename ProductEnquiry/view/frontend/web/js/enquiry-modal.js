
define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/validation',
    'mage/url'
], function ($, modal, $t, validation, urlBuilder) {
    'use strict';

    return function (config, element) {
        var $enquiryBtn = $(element);
        var $modalContainer = $('#product-enquiry-modal-content');
        var $form = $('#product-enquiry-form');

        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: $t('Product Enquiry'),
            buttons: [{
                text: $t('Submit Enquiry'),
                class: 'action primary submit',
                click: function () {
                    $form.trigger('submit');
                }
            }]
        };

        modal(options, $modalContainer);

        $form.validation();

        $enquiryBtn.on('click', function () {
            $('#enquiry-message-container').html('');

            var currentSku = $(
                'div.product-info-price [itemprop="sku"]'
            ).text().trim() || config.defaultSku || '';

            $('#enquiry-product-sku').val(currentSku);
            $modalContainer.modal('openModal');
        });

        $form.off('submit.productEnquiry').on(
            'submit.productEnquiry',
            function (e) {
                e.preventDefault();

                if (!$form.validation('isValid')) {
                    return;
                }

                var input = {
                    name: $('#enquiry-name').val().trim(),
                    address: $('#enquiry-address').val().trim(),
                    email: $('#enquiry-email').val().trim(),
                    sku: $('#enquiry-product-sku').val().trim(),
                    qty: parseInt($('#enquiry-qty').val(), 10)
                };

                var mutation = `
                    mutation SubmitProductEnquiry(
                        $input: ProductEnquiryInput!
                    ) {
                        submitProductEnquiry(input: $input) {
                            success
                            message
                        }
                    }
                `;

                console.log('Submitting product enquiry through GraphQL', input);

                $.ajax({
                    url: config.graphqlUrl || urlBuilder.build('graphql'),
                    type: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    showLoader: true,
                    data: JSON.stringify({
                        query: mutation,
                        variables: {
                            input: input
                        }
                    }),

                    success: function (response) {
                        if (response.errors && response.errors.length) {
                            console.error('GraphQL errors:', response.errors);
                            alert(response.errors[0].message);
                            return;
                        }

                        var result = response.data &&
                            response.data.submitProductEnquiry;

                        if (result && result.success) {
                            $modalContainer.modal('closeModal');
                            $form[0].reset();

                            var successHtml =
                                '<div class="message-success success message" ' +
                                'style="margin: 15px 0;"><div>' +
                                $t(
                                    'Thank you! Your product enquiry has been ' +
                                    'submitted successfully. We will get back to ' +
                                    'you shortly.'
                                ) +
                                '</div></div>';

                            if ($('.product-info-main').length) {
                                $('.product-info-main').prepend(successHtml);
                            } else {
                                $('main.page-main').prepend(successHtml);
                            }

                            setTimeout(function () {
                                $('.product-info-main .message-success')
                                    .fadeOut('slow', function () {
                                        $(this).remove();
                                    });
                            }, 6000);
                        } else {
                            alert(
                                (result && result.message) ||
                                $t('Unable to submit the enquiry.')
                            );
                        }
                    },

                    error: function (xhr) {
                        console.error(
                            'Product enquiry GraphQL request failed:',
                            xhr.status,
                            xhr.responseText
                        );

                        var message = $t(
                            'Something went wrong while submitting the enquiry.'
                        );

                        try {
                            var response = JSON.parse(xhr.responseText);

                            if (response.errors && response.errors.length) {
                                message = response.errors[0].message;
                            }
                        } catch (error) {
                            // Keep the generic error message.
                        }

                        alert(message);
                    }
                });
            }
        );
    };
});

require(
    [
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
    ], function ($, confirm) {
        'use strict';

        function getNotificationForm(url)
        {
            return $(
                '<form>', {
                    'action': url,
                    'method': 'POST'
                }
            ).append(
                $(
                    '<input>', {
                        'name': 'form_key',
                        'value': window.FORM_KEY,
                        'type': 'hidden'
                        }
                )
            );
        }
    
        $('#notification-edit-delete-button').click(
            function () {
                var confirmationMsg = $.mage.__('Are you sure you want to do this?');
                var deleteUrl = $('#notification-edit-delete-button').data('url');
        
                confirm(
                    {
                        'content': confirmationMsg,
                        'actions': {
                            confirm: function () {
                                getNotificationForm(deleteUrl).appendTo('body').submit();
                            }
                        }
                    }
                );

                return false;
            }
        );
    }
);

/**
 * @category   Webkul
 * @package    Webkul_MpFavouriteSeller
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

/*jshint jquery:true*/
define(
    [
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    ],
    function ($,$t,alert) {
        'use strict';
        $.widget(
            'mage.favouriteSellerList',
            {
                _create: function () {
                    var self = this;
                    $('#mass-delete-butn').click(
                        function (e) {
                            var flag =0;
                            $('.mpcheckbox').each(
                                function () {
                                    if (this.checked === true) {
                                        flag =1;
                                    }
                                }
                            );
                            if (flag === 0) {
                                alert({content : $t(' No Checkbox is checked ')});
                                return false;
                            } else {
                                var dicisionapp=confirm($t(" Are you sure you want to delete these seller ? "));
                                if (dicisionapp === true) {
                                    $('#form-favouriteseller-massdelete').submit();
                                } else {
                                    return false;
                                }
                            }
                        }
                    );

                    $('#mpselecctall').click(
                        function (event) {
                            if (this.checked) {
                                $('.mpcheckbox').each(
                                    function () {
                                        this.checked = true;
                                    }
                                );
                            } else {
                                $('.mpcheckbox').each(
                                    function () {
                                        this.checked = false;
                                    }
                                );
                            }
                        }
                    );

                    $('.mp-delete').click(
                        function () {
                            var dicisionapp=confirm($t(" Are you sure you want to delete this seller ? "));
                            if (dicisionapp === true) {
                                var $url=$(this).attr('data-url');
                                window.location = $url;
                            }
                        }
                    );
                }
            }
        );
        return $.mage.favouriteSellerList;
    }
);
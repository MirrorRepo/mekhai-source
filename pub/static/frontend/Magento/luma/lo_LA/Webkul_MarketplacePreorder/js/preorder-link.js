/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
        "jquery",
        'mage/translate',
        "mage/template",
        "mage/mage",
    ], function ($, $t,mageTemplate, alert) {
        'use strict';
        $.widget('mage.preorderLink', {
            options: {
                submenuTemp : '#preorder_submenu',
                mainMenu : '.main-menu',
                otherMenu : '.item'
            },
            _create: function () {
                var self = this;
                $(self.options.mainMenu).on('mouseover', function () {
                    $('.sub-menu').remove();
                    $('#mppreorder_block').hide();
                    var progressTmpl = mageTemplate(self.options.submenuTemp),
                                  tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        $('#mppreorder_block').append(tmpl);
                        $('#mppreorder_block').show();
                });
                $(self.options.otherMenu).on('mouseleave', function () {
                    $('.sub-menu').remove();
                    $('#mppreorder_block').hide();
                });
                $(self.options.mainMenu).on('mouseleave', function () {
                    $('.sub-menu').remove();
                    $('#mppreorder_block').hide();
                });
            },
        });
        return $.mage.preorderLink;
    });
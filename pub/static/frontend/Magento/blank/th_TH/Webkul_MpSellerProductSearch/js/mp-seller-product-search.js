/**
* Webkul Software
* @category Webkul
* @package Webkul_MpSellerProductSearch
* @author Webkul
* @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
* @license https://store.webkul.com/license.html
*/

/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function ($) {
    'use strict';
    $.widget('mage.mpSellerProductSearch', {
        options: {
            searchForm: '.wk_search',
            banner: '.collection-banner',
        },
        _create: function () {
            var self = this;
            $(self.options.banner).before($(self.options.searchForm));
            $(self.options.searchForm).show();
        },
    });
    return $.mage.mpSellerProductSearch;
});

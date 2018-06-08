/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mobikul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define(
    [
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'mage/calendar'
    ], function ($, $t, alert) {
        'use strict';
        $.widget(
            'mage.categoryImages', {
                options: {
                    ajaxErrorMessage: $t('There was error during fetching results.')
                },
                _create: function () {
                    var self = this;
                    $('body').on(
                        'change','.wk-elements',function () {
                            var category_id=$(this).val();
                            if (this.checked === true) {
                                var $obj = $('<input/>').attr('type','hidden')
                                            .attr('name','mobikul_categoryimages[category_id]')
                                            .attr('id','wk-cat-hide'+category_id)
                                            .attr('value',category_id);
                                $('.wk-for-validation').append($obj);
                            } else {
                                $('#wk-cat-hide'+category_id).remove();
                            }
                        }
                    );
                    $("body").delegate(
                        '.wk-plus ,.wk-plusend,.wk-minus, .wk-minusend ',"click",function () {
                            var thisthis=$(this);
                            if (thisthis.hasClass("wk-plus") || thisthis.hasClass("wk-plusend")) {
                                if (thisthis.hasClass("wk-plus")) {
                                    thisthis.removeClass('wk-plus').addClass('wk-plus_click');
                                }
                                if (thisthis.hasClass("wk-plusend")) {
                                    thisthis.removeClass('wk-plusend').addClass('wk-plusend_click');
                                }
                                thisthis.prepend("<span class='wk-node-loader'></span>");
                                self.callCategoryTreeAjaxFunction(thisthis);
                            }
                            if (thisthis.hasClass("wk-minus") || thisthis.hasClass("wk-minusend")) {
                                self.callRemoveCategoryNodeFunction(thisthis);
                            }
                        }
                    );
                },
                callCategoryTreeAjaxFunction: function (thisthis) {
                    var self = this;
                    var i, len, name, id, checkn;
                    $.ajax(
                        {
                            url     :   self.options.categoryTreeAjaxUrl,
                            type    :   "POST",
                            data    :   {
                                parentCategoryId : thisthis.siblings("input").val(),
                                categoryIds :   self.options.categories
                            },
                            dataType:   "html",
                            success :   function (content) {
                                var newdata=  $.parseJSON(content);
                                len = newdata.length;
                                var pxl= parseInt(thisthis.parent(".wk-cat-container").css("margin-left").replace("px",""))+20;
                                thisthis.find(".wk-node-loader").remove();
                                if (thisthis.attr("class") == "wk-plus") {
                                    thisthis.attr("class","wk-minus");
                                }
                                if (thisthis.attr("class") == "wk-plusend") {
                                    thisthis.attr("class","wk-minusend");
                                }
                                if (thisthis.attr("class") == "wk-plus_click") {
                                    thisthis.attr("class","wk-minus");
                                }
                                if (thisthis.attr("class") == "wk-plusend_click") {
                                    thisthis.attr("class","wk-minusend");
                                }
                                for (i=0; i<len; i++) {
                                    id=newdata[i].id;
                                    checkn=newdata[i].check;
                                    name=newdata[i].name;
                                    if (checkn==1) {
                                        if (newdata[i].counting === 0) {
                                            thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-no"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements" type="radio" name="mobikul_categoryimages[category_id]" data-form-part="mobikul_categoryimages_form" checked value='+ id+'></div>');
                                        } else {
                                            thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-plusend"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements" type="radio" name="mobikul_categoryimages[category_id]" data-form-part="mobikul_categoryimages_form" checked value='+ id +'></div>');
                                        }
                                    } else {
                                        if (newdata[i].counting === 0) {
                                            thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-no"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements" type="radio" name="mobikul_categoryimages[category_id]" data-form-part="mobikul_categoryimages_form" value='+ id+'></div>');
                                        } else {
                                            thisthis.parent(".wk-cat-container").after('<div class="wk-removable wk-cat-container" style="display:none;margin-left:'+pxl+'px;"><span  class="wk-plusend"></span><span class="wk-foldersign"></span><span class="wk-elements wk-cat-name">'+ name +'</span><input class="wk-elements" type="radio" name="mobikul_categoryimages[category_id]" data-form-part="mobikul_categoryimages_form" value='+ id +'></div>');
                                        }
                                    }
                                }
                                thisthis.parent(".wk-cat-container").nextAll().slideDown(300);
                            },
                            error: function (response) {
                                alert(
                                    {
                                        content: self.options.ajaxErrorMessage
                                    }
                                );
                            }
                        }
                    );
                },
                callRemoveCategoryNodeFunction: function (thisthis) {
                    if (thisthis.attr("class") == "wk-minus") {
                        thisthis.attr("class","wk-plus");
                    }
                    if (thisthis.attr("class") == "wk-minusend") {
                        thisthis.attr("class","wk-plusend");
                    }
                    var thiscategory = thisthis.parent(".wk-cat-container");
                    var marg= parseInt(thiscategory.css("margin-left").replace("px",""));
                    while (thiscategory.next().hasClass("wk-removable")) {
                        if (parseInt(thiscategory.next().css("margin-left").replace("px",""))>marg) {
                            thiscategory.next().slideUp(
                                "slow",function () {
                                    $(this).remove();
                                }
                            );
                        }
                        thiscategory = thiscategory.next();
                        if (typeof thiscategory.next().css("margin-left")!= "undefined") {
                            if (marg == thiscategory.next().css("margin-left").replace("px","")) {
                                break;
                            }
                        }
                    }
                }
            }
        );
        return $.mage.categoryImages;
    }
);

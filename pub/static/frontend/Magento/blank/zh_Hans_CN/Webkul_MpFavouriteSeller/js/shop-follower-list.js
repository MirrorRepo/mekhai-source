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
    'mage/mage',
    'Magento_Ui/js/modal/alert',
    'mage/calendar'
    ],
    function ($, $t, mage, alert) {
        'use strict';
        var globalThis,emailDataForm;
        $.widget(
            'mage.shopFollowerList',
            {
                options :{
                    messageSend : $t('Mail sent')
                },
                _create: function () {
                    globalThis = this;
                    emailDataForm = $(globalThis.options.emailFormSelector);
                    emailDataForm.mage('validation', {});
                    $("#from-date").calendar({'dateFormat':'mm/dd/yy'});
                    $("#to-date").calendar({'dateFormat':'mm/dd/yy'});
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
                                alert({content : $t('No Checkbox is checked')});
                                return false;
                            } else {
                                var dicisionapp=confirm($t("Are you sure you want to delete these customer ?"));
                                if (dicisionapp === true) {
                                    $('#form-shopfollower-massdelete').submit();
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
                            var dicisionapp=confirm($t("Are you sure you want to delete this customer ?"));
                            if (dicisionapp === true) {
                                var $url=$(this).attr('data-url');
                                window.location = $url;
                            }
                        }
                    );

                    $('body').append($(globalThis.options.wkEmailDataContainer));

                    $(globalThis.options.sendMailSelector).on(
                        'click',
                        function () {
                            globalThis.clickedSendButton();
                        }
                    );

                    $(globalThis.options.formCloseSelector).on(
                        'click',
                        function () {
                            globalThis.clickedCloseForm();
                        }
                    );

                    $(globalThis.options.sendMailButtonSelector).on(
                        'click',
                        function (e) {
                            globalThis.clickedFormSendButton(e);
                        }
                    );

                    $(globalThis.options.resetButtonSelector).on(
                        'click',
                        function () {
                            globalThis.clickedResetButton();
                        }
                    );

                    $('body').on('click','.action-primary.action-accept', function(){
                        globalThis.clickedResetButton();
                        globalThis.clickedCloseForm();
                    });
                },
                clickedSendButton : function () {
                    $('#email-form input,#email-form textarea').removeClass('mage-error');
                    $('.page-wrapper').css('opacity','0.4');
                    $('.wk-mp-model-popup').addClass('_show');
                    $(globalThis.options.wkEmailDataContainer).show();

                },
                clickedCloseForm : function () {
                    $('.page-wrapper').css('opacity','1');
                    globalThis.clickedResetButton();
                    $(globalThis.options.wkEmailDataContainer).hide();
                    $('#email-form .validation-failed').each(
                        function () {
                            $(this).removeClass('validation-failed');
                        }
                    );
                    $('#email-form .validation-advice').each(
                        function () {
                            $(this).remove();
                        }
                    );
                },
                clickedFormSendButton : function (thisEvent) {
                    thisEvent.preventDefault();
                    if (emailDataForm.valid()!=false) {
                        $(globalThis.options.wkEmailDataContainer).addClass('mail-procss');
                        $.ajax(
                            {
                                url:globalThis.options.sendUrl,
                                data:$(globalThis.options.emailFormSelector).serialize(),
                                type:'post',
                                showLoader : true,
                                dataType : 'json',
                                success:function (response) {
                                    $(globalThis.options.wkEmailDataContainer).removeClass('mail-procss');
                                    console.log(response.error);
                                    if (response.error) {
                                        alert(
                                            {
                                                content:$t(response.msg)
                                            }
                                        );
                                    } else {
                                       alert(
                                            {
                                                content:globalThis.options.messageSend
                                            }
                                        ); 
                                    }
                                }
                            }
                        );
                    }
                },
                clickedResetButton : function () {
                    $(globalThis.options.subjectInputSelector).val("");
                    $(globalThis.options.messageFieldSelector).val("");
                    tinyMCE.activeEditor.setContent("");
                }
            }
        );
        return $.mage.shopFollowerList;
    }
);
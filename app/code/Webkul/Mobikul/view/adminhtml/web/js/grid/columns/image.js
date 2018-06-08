/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mobikul
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "./column",
    "jquery",
    "mage/template",
    "text!Webkul_Mobikul/templates/grid/cells/preview.html",
    "Magento_Ui/js/modal/modal"
    ], function (Column, $, mageTemplate, thumbnailPreviewTemplate) {
        "use strict";
        return Column.extend(
            {
                defaults: {
                    bodyTmpl: "ui/grid/cells/thumbnail",
                    fieldClass: {
                        "data-grid-thumbnail-cell": true
                    }
                },
                getSrc: function (row) {
                    return row[this.index + "_src"]
                },
                getAlt: function (row) {
                    return row[this.index + "_src"]
                },
                preview: function (row) {
                    var modalHtml = mageTemplate(
                        thumbnailPreviewTemplate,
                        {
                            src: this.getSrc(row),
                            alt: this.getAlt(row)
                        }
                    );
                    var previewPopup = $("<div/>").html(modalHtml);
                    previewPopup.modal(
                        {
                            //title: this.getAlt(row),
                            innerScroll: true,
                            modalClass: "_image-box",
                            buttons: []
                        }
                    ).trigger("openModal");
                },
                getFieldHandler: function (row) {
                    return this.preview.bind(this, row);
                }
            }
        );
    }
);

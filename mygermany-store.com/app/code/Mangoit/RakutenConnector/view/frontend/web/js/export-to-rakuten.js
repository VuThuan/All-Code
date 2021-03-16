/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
/*jshint jquery:true*/
define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert'
], function ($,$t,alert) {
    'use strict';
    var skipCount,total;
    $.widget('rakutenconnect.syncToRakuten', {
        _create: function () {
            var self = this;
            skipCount = 0;
            var total = self.options.amzProductCount;
            var productList = JSON.parse(self.options.productJson);
            if (total > 0) {
                importProduct(1,productList[0]);
            }
            function importProduct(count,product)
            {
                count = count;
                $.ajax({
                    type: 'get',
                    url:self.options.importUrl,
                    async: true,
                    dataType: 'json',
                    data : { count:count,
                    'product':product },
                    success:function (data) {
                        if (data['error'] == 1) {
                            $(self.options.fieldsetSelector).append($('<div />')
                                                    .addClass('message message-error error')
                                                    .text(data['msg']));
                            skipCount++;
                        }
                        var width = (100/total)*count;
                        $(self.options.progressBarSelector).animate({width: width+"%"},'slow', function () {
                            if (count == total) {
                                finishImporting(count,productList[count-1], skipCount);
                            } else {
                                count++;
                                $(self.options.currentSelector).text(count);
                                importProduct(count,productList[count-1]);
                            }
                        });
                    }
                });
            }
            function finishImporting(count, product, skipCount)
            {
                var total = count-skipCount;
                $(self.options.fieldsetSelector).append($('<div />')
                                .addClass('wk-mu-success wk-mu-box')
                                .text('Total '+ total +' product(s) imported to Rakuten.'))
                              .append($('<div />')
                                    .addClass('wk-mu-note wk-mu-box')
                                    .text('Finished Execution'))
                              .append($('<a/>').attr('href',self.options.backUrl)
                                .append($('<button/>').addClass('wk-back-button primary')
                                    .append($('<span/>').addClass('button')
                                        .append($('<span/>').text('Back')))));
                $(self.options.infoBarSelector).text('Completed');
            }
        }
    });
    return $.rakutenconnect.syncToRakuten;
});

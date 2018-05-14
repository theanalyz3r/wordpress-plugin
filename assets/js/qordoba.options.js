/*
 *
 * PHP version 5 and 7
 *
 * @author Qordoba Team <support@qordoba.com>
 * @copyright 2018 Qordoba Team
 *
 */

'use strict';

(function ($) {

    var isBulkSend = function (action) {
        return 'qordoba_send_bulk' === action.toString().trim();
    };
    var isBulkPendingSend = function (action) {
        return 'qordoba_send_bulk_pending' === action.toString().trim();
    };
    var isBulkDraftSend = function (action) {
        return 'qordoba_send_bulk_draft' === action.toString().trim();
    };

    var isBulkDownload = function (action) {
        return 'qordoba_download_bulk' === action.toString().trim();
    };

    var sendQordobaItem = function (id, type, qor_nonce) {
        $.ajax({
            url: ajaxurl,
            async: false,
            data: {
                action: 'qordoba_send_item',
                object_id: id,
                object_type: type,
                qor_nonce: qor_nonce
            }
        });
    };

    var downloadQordobaItem = function (id, type, qor_nonce) {
        $.ajax({
            url: ajaxurl,
            async: false,
            data: {
                action: 'qordoba_download_item',
                object_id: id,
                object_type: type,
                qor_nonce: qor_nonce
            }
        });
    };

    $('.qordoba-bulk').each(function () {
        var progressBarTest = false;

        var self = this,
            items = $('.qordoba-status', self).data('items'),
            remaining = items,
            info = $('.qordoba-info', self),
            progress = $('.qordoba-progress', self),
            action = $(this).data('action'),
            timestamp = $(this).data('timestamp'),
            qor_nonce = $("#qor_nonce").val(),
            update = function (response) {
                if (response && response.error) {
                    stop(response.errorMessage);
                    return;
                } else if (!response || !response.hasOwnProperty('updated')) {
                    stop('Error: got wrong response from background process');
                    return;
                }

                remaining -= response.updated;
                var percent = 100 - ((remaining * 100) / items);

                progress.css('width', percent + '%');

                if (remaining > 0 && response.total > 0) {
                    request();
                } else {
                    stop('Done.');
                }
            };

        var request = function () {

            if (isBulkSend(action)) {
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'qordoba_get_published_content',
                        max_items: remaining,
                        timestamp: timestamp,
                        qor_nonce: qor_nonce
                    }
                }).success(function (response) {
                    if (response.posts && (0 < response.posts.length)) {
                        for (var i = 0; i < response.posts.length; i++) {
                            sendQordobaItem(response.posts[i], 'post', qor_nonce);
                        }
                    }
                    if (response.terms && (0 < response.terms.length)) {
                        for (var i = 0; i < response.terms.length; i++) {
                            sendQordobaItem(response.terms[i], 'term', qor_nonce);
                        }
                    }
                    stop('Done.');
                })
            } else if (isBulkPendingSend(action)) {
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'qordoba_get_pending_content',
                        max_items: remaining,
                        timestamp: timestamp,
                        qor_nonce: qor_nonce
                    }
                }).success(function (response) {
                    if (response.posts && (0 < response.posts.length)) {
                        for (var i = 0; i < response.posts.length; i++) {
                            sendQordobaItem(response.posts[i], 'post', qor_nonce);
                        }
                    }
                    stop('Done.');
                })
            } else if (isBulkDraftSend(action)) {
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'qordoba_get_draft_content',
                        max_items: remaining,
                        timestamp: timestamp,
                        qor_nonce: qor_nonce
                    }
                }).success(function (response) {
                    if (response.posts && (0 < response.posts.length)) {
                        for (var i = 0; i < response.posts.length; i++) {
                            sendQordobaItem(response.posts[i], 'post', qor_nonce);
                        }
                    }
                    stop('Done.');
                })
            } else if (isBulkDownload(action)) {
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'qordoba_get_sent_content',
                        qor_nonce: qor_nonce,
                        timestamp: timestamp
                    }
                }).success(function (response) {
                    if (response.posts && (0 < response.posts.length)) {
                        for (var i = 0; i < response.posts.length; i++) {
                            downloadQordobaItem(response.posts[i], 'post', qor_nonce);
                        }
                    }
                    if (response.terms && (0 < response.terms.length)) {
                        for (var i = 0; i < response.terms.length; i++) {
                            downloadQordobaItem(response.terms[i], 'term', qor_nonce);
                        }
                    }
                    stop('Done.');
                })
            } else {
                if (progressBarTest) {
                    var updated = Math.floor(Math.random() * remaining + 1);
                    setTimeout(function () {
                        update({updated: updated, total: remaining});
                    }, 2000);
                } else {
                    $.ajax({
                        url: ajaxurl,
                        data: {
                            action: action,
                            max_items: remaining,
                            timestamp: timestamp,
                            qor_nonce: qor_nonce
                        }
                    })
                        .success(update)
                        .fail(function () {
                            stop('Error: failed to send request.');
                        });
                }
            }
        };

        var start = function (text) {
            var info_text = text ? text : 'Processing...';
            $('.qordoba-loading', self).show();
            info.text(info_text);
            progress.css('border-width', 1);

            request();
        };

        var stop = function (text) {
            var info_text = text ? text : '';
            info.text(info_text);
            $('.qordoba-loading', self).hide();

            progress.hide();
        };

        $('.qordoba_request', self).click(function () {
            $(this).attr('disabled', 'disabled');
            start();
        });
    });

}(jQuery));

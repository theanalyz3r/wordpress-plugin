'use strict';
(function(){
  if (qordoba && qordoba.ajaxurl) {
    jQuery.ajax({
      url: qordoba.ajaxurl,
      data: {action: 'qordoba_cron', pll_ajax_backend: 1}
    });
  }
}());

(function ($) {

  $.ADMShortcode = function (base, message) {
    // properties
    var self = this;
    self.base  = base;
    self.message  = message;

    // Ajax Call Flag.
    self.call = 0;

    // init logic
    var defaults = $.ADMShortcode.defaults;

    if ($(defaults.giftVoucherFormAmountValidate).length > 0) {
      $(defaults.giftVoucherFormAmountValidate).on(
        'change keyup',
        function(e){
          var element = $(this);
          self.isValidGiftVoucherInput(element);
        }
      );
    }

    if ($(defaults.giftVoucherFormBtn).length > 0) {
      $(defaults.giftVoucherFormBtn).on(
        'click',
        function(e){
          e.preventDefault();
          var button = $(this);
          self.addGiftVoucher(button);
        }
      );
    }

    if ($(defaults.bundledLpsAjax).length > 0) {
      self.getBundledLps($(defaults.bundledLpsAjax));
    }

    $('body').on(
      'click',
      defaults.bundledLpsAjaxLoadMore,
      function(e){
        e.preventDefault();
        self.getBundledLps($(this));
      }
    );
  };

  $.ADMShortcode.prototype = {
    isValidGiftVoucherInput: function(element){
      var self = this;
      var defaults = $.ADMShortcode.defaults;
      var parent = element.parents(defaults.giftVoucherForm);
      var button = $(defaults.giftVoucherFormBtn, parent);
      var message = $(defaults.giftVoucherMessage, parent).html('');
      var value = element.val();
      button.prop('disabled', true);
      if (!$.isNumeric(value)) {
        message.html(admwpp.giftVoucher.error.notNumber).addClass('admwpp-error');
        return false;
      }
      if (value <= 0) {
        message.html(admwpp.giftVoucher.error.emptyAmount).addClass('admwpp-error');
        return false;
      }
      if (value > admwpp.giftVoucher.maxAmount) {
        message.html(admwpp.giftVoucher.error.maxAmount).addClass('admwpp-error');
        return false;
      }
      button.prop('disabled', false);
      return true;
    },
    addGiftVoucher: function(button){
      var self = this;
      var defaults = $.ADMShortcode.defaults;

      var parent = button.parents(defaults.giftVoucherForm);
      var message = $(defaults.giftVoucherMessage, parent).html('')
      .removeClass('admwpp-error')
      .removeClass('admwpp-success');

      if (parent.hasClass('admwpp-loading')) {
        return;
      }

      var amount = $("input[name='" + defaults.giftVoucherFormAmount + "']", parent);
      if (!self.isValidGiftVoucherInput(amount)) {
        return;
      }

      parent.addClass('admwpp-loading');
      button.prop('disabled', true);

      var data = {
        "action" : "addGiftVoucher",
        "amount" : amount.val(),
        "productOptionId": button.data('options_id'),
        "cartId" : "",
        "portalToken" : ""
      };

      //weblink:portalAddress:cartId
      //weblink:portalAddress:portalToken
      if (weblink != undefined) {
        data['cartId'] = localStorage.getItem('weblink:' + webLinkConfig.portalAddress + ':cartId');
        data['portalToken'] = localStorage.getItem('weblink:' + webLinkConfig.portalAddress + ':portalToken');
        data['portal'] = webLinkConfig.portalAddress;
      } else {
        message.html(admwpp.giftVoucher.error.weblink).addClass('admwpp-error');
        return;
      }

      $.ajax({
        type: "post",
        url: admwpp.ajaxUrl,
        data: data,
        dataType: "json",
        success: function (response) {

          message.html(response.message);

          if ("success" === response.status) {
            message.addClass('admwpp-success');

            // Set weblink Cart ID
            if (weblink != undefined && response.cartId) {
              weblink.cartId = response.cartId;
              localStorage.setItem('weblink:' + webLinkConfig.portalAddress + ':cartId', response.cartId);
            }

            if ($(".weblink-Basket").length > 0) {
              self.message.display_message({
                message: response.message,
                success: true,
              });
              location.reload();
            }

          } else {
            message.addClass('admwpp-error');
          }

          amount.val('');
          parent.removeClass('admwpp-loading');
          button.prop('disabled', true);
        }
      });

    },

    getBundledLps: function(button){
      var self = this;
      var defaults = $.ADMShortcode.defaults;
      var baseDefaults  = $.ADMBase.defaults;

      var page = button.data('page');
      var parentId = button.data('container');
      var parent    = $('#' + parentId);

      if (button.hasClass(baseDefaults.loadingClass)) {
        return;
      }

      button.addClass(baseDefaults.loadingClass);

      var data = {
        "action" : "getBundledLpsAjax",
        "page" : button.data('page'),
        "per_page": button.data('per_page'),
        "post_id": button.data('post_id')
      };

      $.ajax({
        type: "get",
        url: admwpp.ajaxUrl,
        data: data,
        dataType: "json",
        success: function (response) {

          if (page == 1) {
            $(parent).html(response.html);
          } else {
            $('tbody', parent).append(response.html);

            if (response.hasNextPage) {
              button.data('page', response.hasNextPage + 1);
            } else {
              button.remove();
            }
          }

          button.removeClass(baseDefaults.loadingClass);
        }
      });

    }
  };

  $.ADMShortcode.defaults = {
    giftVoucherForm: '.admwpp-add-gift-voucher-form',
    giftVoucherFormBtn: '.admwpp-add-gift-voucher-btn',
    giftVoucherFormAmount: 'admwpp-gift-voucher-amount',
    giftVoucherFormAmountValidate: '.admwpp-gift-voucher-amount-validate',
    giftVoucherMessage: '.admwpp-message',
    bundledLpsAjax: '.admwpp-bundled-lps-ajax',
    bundledLpsAjaxLoadMore: '.admwpp-bundled-loadmore-btn',
    bundledLpsAjaxWrapper: '.admwpp-bundled-lps',
  };

}(jQuery));

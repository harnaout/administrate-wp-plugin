(function ($) {

  $.ADMShortcode = function () {
    // properties
    var self = this;

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
  };

  $.ADMShortcode.prototype = {
    isValidGiftVoucherInput: function(element){
      var self = this;
      var defaults = $.ADMShortcode.defaults;
      var parent = element.parents(defaults.giftVoucherForm);
      var button = $(defaults.giftVoucherFormBtn, parent);
      var message = $(defaults.giftVoucherMessage, parent).html('');
      var value = element.val();
      button.prop('disabled', false);
      if (!$.isNumeric(value)) {
        message.html(admwpp.giftVoucher.error.notNumber).addClass('admwpp-error');
        button.prop('disabled', true);
        return false;
      }
      if (value <= 0) {
        message.html(admwpp.giftVoucher.error.emptyAmount).addClass('admwpp-error');
        button.prop('disabled', true);
        return false;
      }
      if (value > admwpp.giftVoucher.maxAmount) {
        message.html(admwpp.giftVoucher.error.maxAmount).addClass('admwpp-error');
        button.prop('disabled', true);
        return false;
      }
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
      parent.addClass('admwpp-loading');
      button.prop('disabled', true);

      var amount = $("input[name='" + defaults.giftVoucherFormAmount + "']", parent);

      if (!self.isValidGiftVoucherInput(amount)) {
        button.prop('disabled', false);
        return;
      }

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
              location.reload();
            }

          } else {
            message.addClass('admwpp-error');
          }

          amount.val('');
          parent.removeClass('admwpp-loading');
          button.prop('disabled', false);
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
  };

}(jQuery));

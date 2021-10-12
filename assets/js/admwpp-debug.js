(function ($) {
  $.ADMMessage = function () {

    var self = this;

    // Init logic
    var defaults = $.ADMMessage.defaults;

    // Add confirm dialog html.
    if ($(defaults.confirm_dialog).length == 0) {
      var html = "<div id='admwpp-dialog-confirm' class='admwpp-dialog-confirm'>";
      html += "<p><span id='admwpp-confirm-dialog-message'></span></p>";
      html += "</div>";
      $("body").append(html);
    }


    // Reposition message box
    if ($(defaults.message_box + '.show').length > 0) {
      $(defaults.message_box).appendTo("body");
      self.message_position(defaults.message_box);
      setTimeout(function() {
        $(defaults.message_box).slideUp('slow', function() {
          $(this).removeClass('show');
        });
      }, 2 * 1000);
    }

  };

  $.ADMMessage.prototype = {

    display_message: function(options) {
      var self      = this;
      var defaults  = $.ADMMessage.defaults;

      // Merge passed options with default options without overriding the defaults.
      var opts = $.extend(false, {}, defaults.message_options, options);

      // Add message box html if it doesn't exist.
      if ($(defaults.message_box).length === 0) {
        $("<div id='admwpp-message-box'></div>").appendTo("body");
      } else {
        $(defaults.message_box).appendTo("body");
      }

      $(defaults.message_box).each(function() {

        var message_box = $(this);

        self.message_position(message_box);

        // Set the message as the body of the element,
        message_box.html(options.message);

        if (opts.success) {
          message_box.removeClass('error');
          message_box.addClass('success');
        } else {
          message_box.removeClass('success');
          message_box.addClass('error');
        }

        // Show the message with a slow slide down effect.
        message_box.slideDown('slow');

        // Auto hide the message.
        if (opts.auto_hide) {
          setTimeout(function() {
            message_box.slideUp('slow');
          }, 5 * 1000);
        }
      });
    },

    message_position: function(message_box) {
      var defaults = $.ADMMessage.defaults;

      if ($(defaults.admin_bar).length > 0 && $(defaults.message_box).length > 0) {
        $(defaults.message_box).css('top', $(defaults.admin_bar).height() + 'px');
      }
    },

    confirm_dialog: function(options) {
      var self         = this;
      var defaults     = $.ADMMessage.defaults;

      // Merge passed options with default options without overriding the defaults.
      var opts = $.extend(false, {}, defaults.confirm_options, options);

      $(opts.id + ' ' + opts.message_id).html(opts.message);

      if (opts.width > opts.maxWidth) {
        opts.width = opts.maxWidth;
      }

      $(opts.id).dialog({
        resizable:    false,
        minHeight:    200,
        modal:        true,
        draggable:    false,
        buttons:      opts.buttons,
        title:        opts.title,
        dialogClass:  opts.dialogClass,
        show:         opts.show,
        width:        opts.width,
        close:        opts.close,
      });
    },

  };

  $.ADMMessage.defaults = {
    confirm_options: {
      id:           '#admwpp-dialog-confirm',
      width:        300,
      maxWidth:     1000,
      dialogClass:  'admwpp-dialog',
      message:      'Are you sure?',
      message_id:   '#admwpp-confirm-dialog-message',
      title:        'Are you sure?',
      show: {
        effect:     'fade',
        duration:   300
      },
      close: function(){},
    },
    message_options:  {
      message:    'Success!',
      success:    true,
      auto_hide:  true,
    },
    admin_page:               '.wp-admin',
    admin_bar:                 '#wpadminbar',
    message_box:               '#admwpp-message-box',
    confirm_dialog:            '.admwpp-dialog-confirm',
  };
}(jQuery));

(function ($) {

  $.ADMShortcode = function (message) {
    // properties
    var self = this;
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

/**
 * Main Administrate JS file
 *
 * @depend common/message.js
 *
 * @depend admwpp/shortcode.js
 *
 */
(function ($) {
    $(document).ready(function () {

      var message = new $.ADMMessage();
      var shortcode = new $.ADMShortcode(message);

      // Selectric
      if(jQuery().selectric) {
         $('.admwpp-custom-select').selectric();
      }

      var from = $( ".admwpp-from-date" ).datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: admwpp.search.dateFormat
         });
      var to = $( ".admwpp-to-date" ).datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1,
            dateFormat: admwpp.search.dateFormat
         });
    });
  }(jQuery));

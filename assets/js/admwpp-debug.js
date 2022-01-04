(function ($) {
  $.ADMBase = function (message) {

    var self = this;

    self.message = message;

    // Init logic
    var defaults = $.ADMBase.defaults;

    // Init Clippy if on page.
    if ($('.clippy').length > 0) {
      // to copy to clipboard
      $('.clippy').clippy({
        clippy_path: defaults.clippy_swf
      });
    }

    $("body").on({
      ajaxStart: function () {
        $(this).css({'cursor': 'progress'});
      },
      ajaxStop: function () {
        $(this).css({'cursor': 'default'});
      }
    });

    if($(defaults.selectable).length > 0) {
      $(defaults.selectable).on('click', function() {
        $(this).select();
      });
    }

  };

  $.ADMBase.prototype = {

    highest_zindex: function() {
      var highest = -999;
      $("*").each(function() {
          var current = parseInt($(this).css("z-index"), 10);
          if(current && highest < current) highest = current;
      });
      return parseInt(highest);
    },

    blink_element: function (element, blink_class) {
      if (blink_class !== '') {
        element.addClass('admwpp-blink').addClass(blink_class);
      } else {
        element.addClass('admwpp-blink');
      }
      setTimeout(function () {
        if (blink_class !== '') {
          element.removeClass('admwpp-blink').removeClass(blink_class);
        } else {
          element.removeClass('admwpp-blink');
        }
      }, 800);
    },

    validate: function () {
      var self      = this;
      var defaults  = $.ADMBase.defaults;
      var inputs    = $("input[required='true']");
      var validated = true;

      var input, type, target;

      inputs.each(function () {
        input = $(this);

        type  = input.attr('type');

        switch (type) {
        case 'hidden':
          target = $('#' + input.attr('target'));
          if (!input.val()) {
            validated = validated && false;
            target.addClass(defaults.required);
          } else {
            validated = validated && true;
            target.removeClass(defaults.required);
          }
          break;

        case 'radio':
          if ($(':radio:checked[name="' + input.attr('name') + '"]')[0]) {
            validated = validated && true;
            var parent = input.closest('fieldset');
            //input.removeClass(defaults.required);
            parent.removeClass(defaults.required);
          } else {
            validated = validated && false;
            var parent = input.closest('fieldset');
            //input.addClass(defaults.required);
            parent.addClass(defaults.required);
          }
          break;

        default:
          if (!input.val()) {
            validated = validated && false;
            input.addClass(defaults.required);
          } else {
            validated = validated && true;
            input.removeClass(defaults.required);
          }
          break;
        }
      });

      $('.' + defaults.required).effect('highlight', {}, 1000);

      if (!validated) {
        self.message.display_message({
          message: 'Please fill required fields.',
          success: false,
        });
      }

      return validated;
    },

    is_int: function (value) {
      if ((parseFloat(value) === parseInt(value, 10)) && !isNaN(value)) {
        return true;
      }
      return false;
    },

    is_active_editor: function () {
      var is_active_editor = false;
      if (tinymce.activeEditor !== null) {
        if (!tinymce.activeEditor.isHidden()) {
          is_active_editor = true;
        }
      }
      return is_active_editor;
    },

    url_param: function (key) {
      var regexp = new RegExp(key + '=' + '(.+?)(&|$)');
      return decodeURI((regexp.exec(location.search) || [, null])[1]);
    },

    url_params: function () {
      var params = [];
      var chunk;

      var href   = window.location.href;
      var hashes = href.slice(href.indexOf('?') + 1).split('&');

      var i;
      for (i = 0; i < hashes.length; i++) {
        chunk = hashes[i].split('=');
        params.push(chunk[0]);
        params[chunk[0]] = chunk[1];
      }

      return params;
    },

    trim_text: function (text, max_length, ending) {
      return (text.length > max_length)
        ? $.trim(text).substring(0, max_length).split(" ").slice(0, -1).join(" ") + ending
        : text;
    },

  };

  $.ADMBase.defaults = {
    clippy_swf: admwpp.baseUrl + 'assets/js/plugins/clippy/clippy.swf',
    admin_bar: '#wpadminbar',
    required: 'admwpp-required',
    loader: '.admwpp-loader-image',
    meta_loader: '.admwpp-loader',
    selectable: '.admwpp-selectable',
    loadingClass: 'admwpp-loading',
  };
}(jQuery));

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

  $.ADMSearch = function (base, message) {
    // properties
    var self = this;
    self.base  = base;
    self.message  = message;

    // Ajax Call Flag.
    self.autocompleteCall = 0;

    // init logic
    var defaults = $.ADMSearch.defaults;

    var from = $(defaults.fromDate).datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 1,
          dateFormat: admwpp.search.dateFormat
       });
    var to = $(defaults.toDate).datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 1,
          dateFormat: admwpp.search.dateFormat
       });

    // Setup the auto complete for search input box
    var autoCompleteElement = $(defaults.searchInputAuto).autocomplete({
          source: function (request, response) {
            self.autocomplete(request, response);
          },
          delay: 500,
          appendTo: defaults.searchInputWrapper,
          minLength: 3,
          classes: {
            "ui-autocomplete": defaults.searchAutoCompleteDropdownClass
          },
          select: function(event, ui) {
            if (ui.item.value) {
              $(defaults.searchInputAuto).val(ui.item.value);
              $(defaults.searchBtn).trigger('click');
            }
            $(defaults.searchInputAuto).removeClass(defaults.searchAutoCompleteLoaderClass);
          },
          close: function() {
            $(defaults.searchInputAuto).removeClass(defaults.searchAutoCompleteLoaderClass);
          },
      });

      // Process autoComplete data HTML output
      var autoCompleteElementData = autoCompleteElement.data("ui-autocomplete") || autoCompleteElement.data("autocomplete");
      if (autoCompleteElementData) {
        autoCompleteElementData._renderItem = function (ul, item) {
          var newText = String(item.value).replace(
                  new RegExp(this.term, "gi"),
                  "<span class='ui-state-highlight'>$&</span>");

          return $("<li></li>")
              .data("item.autocomplete", item)
              .append("<div>" + newText + "</div>")
              .appendTo(ul);
        };
      }


  };

  $.ADMSearch.prototype = {
    autocomplete: function (request, response) {

      var self = this;
      var defaults = $.ADMSearch.defaults;

      // To avoid clashes, abort previous call to autocomplete,
      // if there is one pending.
      if (self.autocompleteCall && self.autocompleteCall.readystate !== 4) {
        self.autocompleteCall.abort();
        self.autocompleteCall = null;
      }

      // AJAX call
      self.autocompleteCall = $.ajax({
        type: "GET",
        url: admwpp.routeUrl,
        data: {
          '_uri':     'search/auto-complete',
          'query':    request.term
        },
        contentType:  "application/json; charset=utf-8",
        dataType:     "json",
        success: function (data) {
          if (data.length > 0) {
            response(data);
          }
          $(defaults.searchInputAuto).removeClass(defaults.searchAutoCompleteLoaderClass);
        }
      });

    },
  };

  $.ADMSearch.defaults = {
    searchAutoCompleteLoaderClass: 'ui-autocomplete-loading',
    searchAutoCompleteDropdownClass: 'adwmpp-autocomplete-dropdown',
    searchInputWrapper: '.adwmpp-input-wrapper',
    searchBtn: '.adwmpp-search-btn',
    searchInputAuto: '.adwmpp-search-auto-complete',
    fromDate: ".admwpp-from-date",
    toDate: ".admwpp-to-date",
  };

}(jQuery));

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

/**
 * Main Administrate JS file
 *
 * @depend common/base.js
 * @depend common/message.js
 *
 * @depend admwpp/shortcode.js
 * @depend admwpp/search.js
 *
 */
(function ($) {
    $(document).ready(function () {
      var message = new $.ADMMessage();
      var base = new $.ADMBase(message);
      var shortcode = new $.ADMShortcode(base, message);
      var search = new $.ADMSearch(base, message);

      // Selectric
      if(jQuery().selectric) {
         $('.admwpp-custom-select').selectric();
      }
    });
  }(jQuery));

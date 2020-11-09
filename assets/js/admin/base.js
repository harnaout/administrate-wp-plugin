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
    clippy_swf: admwpp_base_url + 'assets/js/plugins/clippy/clippy.swf',
    admin_bar: '#wpadminbar',
    required: 'admwpp-required',
    loader: '.admwpp-loader-image',
    meta_loader: '.admwpp-loader',
    selectable: '.admwpp-selectable',
    loadingClass: 'admwpp-loading',
  };
}(jQuery));

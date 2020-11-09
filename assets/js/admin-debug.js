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

(function ($) {

  $.ADMSettings = function (base, message) {
    // properties
    var self      = this;
    self.base     = base;
    self.message  = message;

    var defaults = $.ADMSettings.defaults;
    var baseDefaults  = $.ADMBase.defaults;

    // Ajax Call Flag.
    self.call = 0;

    // init logic
    var defaults = $.ADMSettings.defaults;

    $(defaults.importCategoriesBtn).on(
      'click',
      function(){

        var button = $(this);

        if (button.hasClass(baseDefaults.loadingClass)) {
          return;
        }

        button.data('page', 1);
        button.data('imported', 0);
        button.data('exists', 0);

        self.importCategories(button);
      }
    );

    $(defaults.importCoursesBtn).on(
      'click',
      function(){

        var button = $(this);

        if (button.hasClass(baseDefaults.loadingClass)) {
          return;
        }

        button.data('page', 1);
        button.data('imported', 0);
        button.data('exists', 0);

        self.importCourses(button);
      }
    );


  };

  $.ADMSettings.prototype = {
    importCategories: function(button) {
      var self = this;
      var defaults = $.ADMSettings.defaults;
      var baseDefaults  = $.ADMBase.defaults;

      if (button.hasClass(baseDefaults.loadingClass)) {
        return;
      }

      button.addClass(baseDefaults.loadingClass);

      var page = parseInt(button.data('page'));
      var per_page = parseInt(button.data('per_page'));
      var imported = parseInt(button.data('imported'));
      var exists = parseInt(button.data('exists'));

      var data = {
        "_uri" : "settings/importLearningCategories",
        "page" : page,
        "per_page" : per_page,
        "imported" : imported,
        "exists" : exists,
      };

      $.ajax({
        type: "get",
        url: admwpp_route_url,
        data: data,
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        success: function (response) {

          button.removeClass(baseDefaults.loadingClass);

          button.data('imported', response.imported);
          button.data('exists', response.exists);

          if (response.hasNextPage) {
            page = page + 1;
            button.data('page', page);
            self.importCategories(button);
          }

          if (response.message) {
            $(defaults.importInfoCat).html(response.message);
          }
        }
      });
    },

    importCourses: function(button) {
      var self = this;
      var defaults = $.ADMSettings.defaults;
      var baseDefaults  = $.ADMBase.defaults;

      if (button.hasClass(baseDefaults.loadingClass)) {
        return;
      }

      button.addClass(baseDefaults.loadingClass);

      var page = parseInt(button.data('page'));
      var per_page = parseInt(button.data('per_page'));
      var imported = parseInt(button.data('imported'));
      var exists = parseInt(button.data('exists'));

      var data = {
        "_uri" : "settings/importCourses",
        "page" : page,
        "per_page" : per_page,
        "imported" : imported,
        "exists" : exists,
      };

      $.ajax({
        type: "get",
        url: admwpp_route_url,
        data: data,
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        success: function (response) {

          button.removeClass(baseDefaults.loadingClass);

          button.data('imported', response.imported);
          button.data('exists', response.exists);

          if (response.hasNextPage) {
            page = page + 1;
            button.data('page', page);
            self.importCourses(button);
          }

          if (response.message) {
            $(defaults.importInfoCourses).html(response.message);
          }
        }
      });
    }
  };

  $.ADMSettings.defaults = {
    importCategoriesBtn: '#admwpp-import-categories-button',
    importCoursesBtn: '#admwpp-import-courses-button',
    importLearningPathBtn: '#admwpp-import-learning-path-button',
    importInfoCat: '#admwpp-import-info-categories',
    importInfoCourses: '#admwpp-import-info-courses',
    importInfoLp: '#admwpp-import-info-learning-path',
  };

}(jQuery));

/**
 * Main Administrate JS file
 *
 * @depend admin/base.js
 * @depend admin/settings.js
 *
 * @depend common/message.js
 *
 */
(function ($) {
  $(document).ready(function () {
    var message = new $.ADMMessage();
    var base = new $.ADMBase(message);
    var settings = new $.ADMSettings(base, message);
  });
}(jQuery));

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

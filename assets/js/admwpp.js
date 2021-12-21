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

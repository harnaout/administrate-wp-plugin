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

/**
 * Main Administrate JS file
 *
 * @depend admwpp/shortcode.js
 *
 */
(function ($) {
    $(document).ready(function () {

      var shortcode = new $.ADMShortcode();

      // Selectric
      if(jQuery().selectric) {
         $('.admwpp-custom-select').selectric();
      }

      var dateFormat = admwpp.search.dateFormat,
         from = $( ".admwpp-from-date" ).datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1
         }).on( "change", function() {
            to.datepicker( "option", "minDate", getDate( this ) );
         }),
         to = $( ".admwpp-to-date" ).datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1
         }).on( "change", function() {
            from.datepicker( "option", "maxDate", getDate( this ) );
         });

      function getDate( element ) {
         var date;
         try {
         date = $.datepicker.parseDate( dateFormat, element.value );
         } catch( error ) {
         date = null;
         }

         return date;
      }

    });
  }(jQuery));

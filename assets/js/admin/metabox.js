(function ($) {

  $.ADMMetabox = function (base, message) {
    // properties
    var self      = this;
    self.base     = base;
    self.message  = message;

    var defaults = $.ADMMetabox.defaults;
    var baseDefaults  = $.ADMBase.defaults;

    // Ajax Call Flag.
    self.call = 0;

    // init logic
    var defaults = $.ADMMetabox.defaults;

    $(defaults.partnersInput).autocomplete({
        source: function (request, response) {
            var data = {
              "_uri" : "search/partners",
              "query" : request.term,
            };

            // If another AJAX call is still not done, abort it.
            if (self.call && self.call.readystate !== 4) {
              self.call.abort();
              self.call = null;
            }

            self.call = $.ajax({
              type: "get",
              url: admwpp.routeUrl,
              data: data,
              dataType: "json",
              success: function (data) {
                if (data) {
                  response(data);
                }
              }

            });
        },
        minLength: 3,
        select: function(event, ui) {
          if (ui.item.value) {
            $(defaults.partnerIdHidden).val(ui.item.value);
            $(defaults.partnerNameHidden).val(ui.item.label);
            $(defaults.partnerNameDisplay).html(ui.item.label);
          }
        },
        close: function() {
          $(defaults.partnersInput).val('').removeClass('ui-autocomplete-loading');
        },
    });


  };

  $.ADMMetabox.prototype = {

  };

  $.ADMMetabox.defaults = {
    partnersInput: '#admwpp-partners-search',
    partnerIdHidden: '#admwpp-partner-id',
    partnerNameHidden: '#admwpp-partner-name',
    partnerNameDisplay: '#admwpp-selected-partner-name',
  };

}(jQuery));

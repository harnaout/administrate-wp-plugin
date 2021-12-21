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

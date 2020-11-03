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

        if (button.hasClass(baseDefaults.loaddingClass)) {
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

        if (button.hasClass(baseDefaults.loaddingClass)) {
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

      if (button.hasClass(baseDefaults.loaddingClass)) {
        return;
      }

      button.addClass(baseDefaults.loaddingClass);

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

          button.removeClass(baseDefaults.loaddingClass);

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

      if (button.hasClass(baseDefaults.loaddingClass)) {
        return;
      }

      button.addClass(baseDefaults.loaddingClass);

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

          button.removeClass(baseDefaults.loaddingClass);

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

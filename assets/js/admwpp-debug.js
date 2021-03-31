(function ($) {

  $.ADMShortcode = function () {
    // properties
    var self = this;

    // Ajax Call Flag.
    self.call = 0;

    // init logic
    var defaults = $.ADMShortcode.defaults;

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
  };

  $.ADMShortcode.prototype = {
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
      parent.addClass('admwpp-loading');
      button.prop('disabled', true);

      var amount = $("input[name='" + defaults.giftVoucherFormAmount + "']", parent);

      if (amount.val() <= 0) {
        message.html(admwpp.giftVoucher.error.emptyAmount).addClass('admwpp-error');
        parent.removeClass('admwpp-loading');
        button.prop('disabled', false);
        return;
      }

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
              if (weblink != undefined) {
                $(".weblink-Basket").each(function(){
                  var basketId = $(this).attr('id');
                  var basketElement = document.getElementById(basketId);
                  window.WebLink.unmount(basketElement);

                  weblink.mount(
                    basketElement,
                    'Basket',
                    {
                      "showBasketPopover":true,
                      "cartUrl":response.cartUrl
                    }
                  );
                });
              }

            }

          } else {
            message.addClass('admwpp-error');
          }

          amount.val('');
          parent.removeClass('admwpp-loading');
          button.prop('disabled', false);
        }
      });

    }
  };

  $.ADMShortcode.defaults = {
    giftVoucherForm: '.admwpp-add-gift-voucher-form',
    giftVoucherFormBtn: '.admwpp-add-gift-voucher-btn',
    giftVoucherFormAmount: 'admwpp-gift-voucher-amount',
    giftVoucherMessage: '.admwpp-message',
  };

}(jQuery));

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

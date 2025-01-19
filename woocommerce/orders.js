(function ($) {
  $(document).ready(function () {
    $('.masterstudy-button_icon-print').on('click', function () {
      window.print();
    });

    let referrerUrl = document.referrer
    if (referrerUrl) {
      let button = $('.masterstudy-orders-details a.masterstudy-button_icon-arrow-left');

      if (button.length) {
        button.attr('href', referrerUrl);
      }
    }
  });
})(jQuery);

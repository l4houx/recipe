'use strict';

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});
  
$(window).on('load', function () {
  //===== Preloader
  $('#preloader').delay(500).fadeOut(500);

  //===== Popup
  if ($('.popup-wrapper').length > 0) {
    let $firstPopup = $('.popup-wrapper').eq(0);

    appearPopup($firstPopup);
  }
});

function setMarginTop() {
  if ($(".course-details-sidebar").length > 0 && $(".course-details-area").length > 0) {
    if ($(window).width() > 991) {
      let marginTop = $(".course-title-content").offset().top - $(".course-details-items").offset().top;
      $(".course-details-area .course-details-sidebar").css("margin-top", marginTop + 'px');
    } else {
      $(".course-details-area .course-details-sidebar").css("margin-top", '40px');
    }
  }
}

$(function () {

  //===== 01. Main Menu
  function mainMenu() {
    // Variables
    var var_window = $(window),
      navContainer = $('.header-navigation'),
      navbarToggler = $('.navbar-toggler'),
      navMenu = $('.nav-menu'),
      navMenuLi = $('.nav-menu ul li ul li'),
      closeIcon = $('.navbar-close');

    // navbar toggler
    navbarToggler.on('click', function () {
      navbarToggler.toggleClass('active');
      navMenu.toggleClass('menu-on');
    });

    // close icon
    closeIcon.on('click', function () {
      navMenu.removeClass('menu-on');
      navbarToggler.removeClass('active');
    });

    // adds toggle button to li items that have children
    navMenu.find('li a').each(function () {
      if ($(this).next().length > 0) {
        $(this).parent('li').append('<span class="dd-trigger"><i class="far fa-angle-down"></i></span>');
      }
    });

    // expands the dropdown menu on each click
    navMenu.find('li .dd-trigger').on('click', function (e) {
      e.preventDefault();

      $(this).parent('li').children('ul').stop(true, true).slideToggle(350);
      $(this).parent('li').toggleClass('active');
    });

    // check browser width in real-time
    function breakpointCheck() {
      var windoWidth = window.innerWidth;

      if (windoWidth <= 991) {
        navContainer.addClass('breakpoint-on');
      } else {
        navContainer.removeClass('breakpoint-on');
      }
    }

    breakpointCheck();

    var_window.on('resize', function () {
      breakpointCheck();
    });
  };

  // Document Ready
  $(document).ready(function () {
    mainMenu();
  });

  //===== seller active slick slider
  $('.courses-active').slick({
    dots: false,
    infinite: true,
    autoplay: true,
    autoplaySpeed: 5000,
    arrows: true,
    prevArrow: '<span class="prev"><i class="fal fa-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="fal fa-arrow-right"></i></span>',
    speed: 1500,
    slidesToShow: 3,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1215,
        settings: {
          slidesToShow: 2,
        }
      },
      {
        breakpoint: 1007,
        settings: {
          slidesToShow: 1,
        }
      },
      {
        breakpoint: 591,
        settings: {
          slidesToShow: 1,
          arrows: false,
        }
      }
    ],
    rtl: langDir == 1 ? true : false
  });

  //===== seller active slick slider
  $('.courses-active-3').slick({
    dots: false,
    infinite: true,
    autoplay: true,
    autoplaySpeed: 5000,
    arrows: true,
    prevArrow: '<span class="prev"><i class="fal fa-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="fal fa-arrow-right"></i></span>',
    speed: 1500,
    slidesToShow: 3,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1201,
        settings: {
          slidesToShow: 3,
        }
      },
      {
        breakpoint: 992,
        settings: {
          slidesToShow: 2,
        }
      },
      {
        breakpoint: 768,
        settings: {
          slidesToShow: 1,
          arrows: false,
        }
      },
      {
        breakpoint: 576,
        settings: {
          slidesToShow: 1,
          arrows: false,
        }
      }
    ],
    rtl: langDir == 1 ? true : false
  });

  //===== testimonial slick slider
  $('.testimonials-active').slick({
    dots: false,
    infinite: true,
    autoplay: true,
    autoplaySpeed: 5000,
    arrows: true,
    prevArrow: '<span class="prev"><i class="fal fa-arrow-left"></i></span>',
    nextArrow: '<span class="next"><i class="fal fa-arrow-right"></i></span>',
    speed: 1500,
    slidesToShow: 1,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 992,
        settings: {
          arrows: false,
        }
      }
    ],
    rtl: langDir == 1 ? true : false
  });

  //===== testimonial slick slider
  $('.testimonials-2-active').slick({
    dots: true,
    infinite: true,
    autoplay: true,
    autoplaySpeed: 5000,
    arrows: false,
    speed: 1500,
    slidesToShow: 3,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1216,
        settings: {
          slidesToShow: 2,
        }
      },
      {
        breakpoint: 784,
        settings: {
          slidesToShow: 1,
        }
      }
    ]
  });

  //===== shop active slick slider
  $('.shop-active').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    fade: false,
    asNavFor: '.shop-thumb-active'
  });

  $('.shop-thumb-active').slick({
    slidesToShow: 3,
    slidesToScroll: 1,
    asNavFor: '.shop-active',
    dots: false,
    centerMode: true,
    arrows: false,
    centerPadding: "0",
    focusOnSelect: true
  });

  //===== Isotope Project 1
  $('.container').imagesLoaded(function () {
    var $grid = $('.grid').isotope({
      // options
      transitionDuration: '1s'
    });

    // filter items on button click
    $('.project-menu ul').on('click', 'li', function () {
      var filterValue = $(this).attr('data-filter');

      $grid.isotope({
        filter: filterValue
      });
    });

    //for menu active class
    $('.project-menu ul li').on('click', function (event) {
      event.preventDefault();

      $(this).siblings('.active').removeClass('active');
      $(this).addClass('active');
    });
  });

  //===== Projects Slide
  $('.brand-active').owlCarousel({
    loop: true,
    items: 5,
    dots: false,
    autoplay: true,
    autoplayTimeout: 5000,
    smartSpeed: 2000,
    rtl: true,
    nav: false,
    responsive: {
      0: {
        items: 2
      },
      768: {
        items: 3
      },
      992: {
        items: 4
      },
      1200: {
        items: 5
      },
      1600: {
        items: 5
      }
    }
  })

  //====== Magnific Popup
  $('.video-popup').magnificPopup({
    type: 'iframe'
  });

  //===== Magnific Popup
  $('.image-popup').magnificPopup({
    type: 'image',
    gallery: {
      enabled: true
    }
  });

  //===== counter up
  $('.counter').counterUp({
    delay: 10,
    time: 2000
  });

  //===== back to top
  $(window).on('scroll', function () {
    if ($(this).scrollTop() > 600) {
      $('.back-to-top').fadeIn(200);
    } else {
      $('.back-to-top').fadeOut(200);
    }
  });

  //===== animate the scroll to top
  $('.back-to-top').on('click', function (event) {
    event.preventDefault();

    $('html, body').animate({
      scrollTop: 0,
    }, 1500);
  });

  //===== niceSelect js
  $('select').niceSelect();

  //===== uploaded image preview
  if ($('.upload').length > 0) {
    $('.upload').on('change', function (event) {
      let file = event.target.files[0];
      let reader = new FileReader();

      reader.onload = function (e) {
        $('.user-photo').attr('src', e.target.result);
      };

      reader.readAsDataURL(file);
    });
  }

  //===== initialize bootstrap dataTable
  $('#user-dataTable').DataTable({
    ordering: false,
    responsive: true
  });

  //===== course navigation
  $('.course-nav-btn').on('click', function (event) {
    $('.course-videos-sidebar').slideToggle((300));
  });

  //===== lazy load init
  new LazyLoad({});

  // format date & time for announcement popup
  $('.offer-timer').each(function () {
    let $this = $(this);

    let date = new Date($this.data('end_date'));
    let year = parseInt(new Intl.DateTimeFormat('en', { year: 'numeric' }).format(date));
    let month = parseInt(new Intl.DateTimeFormat('en', { month: 'numeric' }).format(date));
    let day = parseInt(new Intl.DateTimeFormat('en', { day: '2-digit' }).format(date));

    let time = $this.data('end_time');
    time = time.split(':');
    let hour = parseInt(time[0]);
    let minute = parseInt(time[1]);

    $this.syotimer({
      year: year,
      month: month,
      day: day,
      hour: hour,
      minute: minute
    });
  });

  // add user email for subscribe
  $('.subscriptionForm').on('submit', function (event) {
    event.preventDefault();

    let formURL = $(this).attr('action');
    let formMethod = $(this).attr('method');

    let formData = new FormData($(this)[0]);

    $.ajax({
      url: formURL,
      method: formMethod,
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (response) {
        $('input[name="email_id"]').val('');

        toastr['success'](response.success);
      },
      error: function (errorData) {
        toastr['error'](errorData.responseJSON.error.email_id[0]);
      }
    });
  });

  /*------------------------
   Highlight Js
  -------------------------- */
  hljs.initHighlightingOnLoad();

  setMarginTop();
});

$(window).on('resize', function() {
  setMarginTop();
});

function appearPopup($this) {
  let closedPopups = [];

  if (sessionStorage.getItem('closedPopups')) {
    closedPopups = JSON.parse(sessionStorage.getItem('closedPopups'));
  }

  // if the popup is not in closedPopups Array
  if (closedPopups.indexOf($this.data('popup_id')) == -1) {
    $('#' + $this.attr('id')).show();

    let popupDelay = $this.data('popup_delay');

    setTimeout(function () {
      jQuery.magnificPopup.open({
        items: { src: '#' + $this.attr('id') },
        type: 'inline',
        callbacks: {
          afterClose: function () {
            // after the popup is closed, store it in the sessionStorage & show next popup
            closedPopups.push($this.data('popup_id'));
            sessionStorage.setItem('closedPopups', JSON.stringify(closedPopups));

            if ($this.next('.popup-wrapper').length > 0) {
              appearPopup($this.next('.popup-wrapper'));
            }
          }
        }
      }, 0);
    }, popupDelay);
  } else {
    if ($this.next('.popup-wrapper').length > 0) {
      appearPopup($this.next('.popup-wrapper'));
    }
  }
}

// count total view of an advertisement
function adView($id) {
  let url = baseURL + '/advertisement/' + $id + '/total-view';

  let data = {
    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
  };

  $.post(url, data, function (response) {
    if ('success' in response) {
    } else {
    }
  });
}

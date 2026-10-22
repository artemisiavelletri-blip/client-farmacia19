<html lang="it">

    <head>
        <!-- meta tags -->
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- title -->
        <title>Farmacia19</title>

        <!-- favicon -->
        <link rel="icon" type="image/x-icon" href="{{ asset('/img/logo/favicon.png') }}">

        <!-- css -->
        <link rel="stylesheet" href="{{ asset('/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/all-fontawesome.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/animate.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/magnific-popup.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/owl.carousel.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/jquery-ui.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/nice-select.min.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/main.css') }}">
        @yield('css')

        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-TFB6HTNV');</script>
        <!-- End Google Tag Manager -->

    </head>

    <body class="home-3">

        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TFB6HTNV"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->

        <!-- preloader -->
        <div class="preloader">
            <div class="loader-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
        <!-- preloader end -->
        <style>
            /* MINI CARRELLO FARMACIA19 */

            .dropdown-cart-list {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
                box-sizing: border-box;
            }

            .dropdown-cart-list > li {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .dropdown-cart-list .dropdown-cart-item {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0;
                box-sizing: border-box;
            }

            /* Immagine */
            .dropdown-cart-list .cart-img {
                flex: 0 0 60px !important;
                width: 60px !important;
                min-width: 60px !important;
                max-width: 60px !important;
                overflow: hidden;
            }

            .dropdown-cart-list .cart-img a {
                display: flex;
                width: 100%;
                justify-content: center;
                align-items: center;
            }

            .dropdown-cart-list .cart-img img {
                width: 100% !important;
                max-width: 100% !important;
                height: 60px !important;
                object-fit: contain !important;
            }

            /* Testo prodotto */
            .dropdown-cart-list .cart-info {
                flex: 1 1 0 !important;
                width: 0 !important;
                min-width: 0 !important;
                max-width: 100%;
            }

            .dropdown-cart-list .cart-info h4 {
                margin-bottom: 6px;
                overflow-wrap: anywhere;
                line-height: 1.3;
            }

            .dropdown-cart-list .cart-info h4 a {
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
                overflow: hidden;
            }

            .dropdown-cart-list .cart-qty {
                margin: 0;
            }

            /* Pulsante elimina */
            .dropdown-cart-list .cart-remove {
                position: static !important;
                transform: none !important;
                flex: 0 0 20px !important;
                width: 20px !important;
                min-width: 20px !important;
                margin: 0 !important;
                display: flex !important;
                justify-content: center;
                align-items: center;
            }

            /* Mobile */
            @media (max-width: 480px) {
                .dropdown-cart-list .dropdown-cart-item {
                    gap: 6px !important;
                }

                .dropdown-cart-list .cart-img {
                    flex-basis: 55px !important;
                    width: 55px !important;
                    min-width: 55px !important;
                }

                .dropdown-cart-list .cart-img img {
                    height: 55px !important;
                }

                .dropdown-cart-list .cart-info h4 {
                    font-size: 12px;
                }
            }
        </style>
        @include('navbar')
        @yield('content')
        @include('footer')
    </body>
    <script src="{{ asset('/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('/js/modernizr.min.js') }}"></script>
    <script src="{{ asset('/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('/js/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('/js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('/js/jquery.appear.min.js') }}"></script>
    <script src="{{ asset('/js/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('/js/counter-up.js') }}"></script>
    <script src="{{ asset('/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('/js/countdown.min.js') }}"></script>
    <script src="{{ asset('/js/wow.min.js') }}"></script>
    @if(Route::currentRouteName() != 'cart.shop_cart')
        <script src="{{ asset('/js/main.js') }}"></script>
    @else
        <script src="{{ asset('/js/mainNoQty.js') }}"></script>
    @endif
    <script type="text/javascript">
       $(document).on('click', '.cart-remove', function (e) {
            e.preventDefault();

            const itemId = $(this).data('id');
            const $button = $(this);

            if ($button.prop('disabled')) return;

            $button.prop('disabled', true);
            $('.preloader').show();

            $.ajax({
                url: '/cart/remove/' + itemId,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {

                    $('#cart-item-' + itemId).remove();

                    const selectors = [
                        '#cart-wrapper',
                        '#table-cart-wrapper',
                        '#shop-cart-summary',
                        '#shop-cart-id',
                        '#cart-mobile-counter'
                    ];

                    const requests = selectors.map(function (selector) {
                        const deferred = $.Deferred();
                        const $element = $(selector);

                        if (!$element.length) {
                            deferred.resolve();
                            return deferred.promise();
                        }

                        $element.load(
                            window.location.href + ' ' + selector + ' > *',
                            function (response, status) {
                                if (status === 'error') {
                                    deferred.reject();
                                } else {
                                    deferred.resolve();
                                }
                            }
                        );

                        return deferred.promise();
                    });

                    $.when.apply($, requests)
                        .done(function () {
                            $(document).trigger('cart:updated');
                        })
                        .fail(function () {
                            console.error('Errore durante aggiornamento carrello');
                        })
                        .always(function () {
                            $('.preloader').hide();
                            $button.prop('disabled', false);
                        });
                },
                error: function () {
                    $('.preloader').hide();
                    $button.prop('disabled', false);
                    alert('Errore durante la rimozione dal carrello.');
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {

            const forms = document.querySelectorAll("form");
            const preloader = document.querySelector(".preloader");

            forms.forEach(form => {
                form.addEventListener("submit", function () {
                    preloader.style.display = "flex";
                });
            });

        });
        
        setInterval(() => {
            fetch('/refresh-csrf')
                .then(res => res.json())
                .then(data => {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                });
        }, 300000); // ogni 5 minuti
    </script>
    @yield('js')
</html>
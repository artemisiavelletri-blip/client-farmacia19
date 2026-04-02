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

    </head>

    <body class="home-3">

        <!-- preloader -->
        <div class="preloader">
            <div class="loader-ripple">
                <div></div>
                <div></div>
            </div>
        </div>
        <!-- preloader end -->
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
    <script src="{{ asset('/js/main.js') }}"></script>
    <script type="text/javascript">
        $(document).on('click', '.cart-remove', function () {
            let itemId = $(this).data('id');

            $.ajax({
                url: '/cart/remove/' + itemId,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {

                    // Rimuove la riga dalla tabella
                    $('#cart-item-' + itemId).remove();

                    $('#cart-wrapper').load(window.location.href + ' #cart-wrapper > *');
                    $('#table-cart-wrapper').load(window.location.href + ' #table-cart-wrapper > *');
                    $('#shop-cart-summary').load(window.location.href + ' #shop-cart-summary > *');
                    $('#prod-info').load(window.location.href + ' #prod-info > *');
                    $('#shop-cart-id').load(window.location.href + ' #shop-cart-id > *');
                    $('#cart-mobile-counter').load(window.location.href + ' #cart-mobile-counter > *');
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
    </script>
    @yield('js')
</html>
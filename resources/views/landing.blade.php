<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ env('APP_NAME') . ' - ' . $title }}</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ $app['fav'] ?? asset('favicon.png') }}" rel="icon">

    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">
    <link href="{{ asset('css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/boxicons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('css/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    {!! htmlScriptTagJsApi() !!}
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
</head>

<body>
    <div id="overlay">
        <div id="text-overlay"><img src="{{ asset('img/loading-screen-iziklaim.gif') }}" class="img-fluid"></div>
    </div>
    @include('landing.header')
    <main id="main">
        @include('landing.hero')
        @include('landing.about-us')
        @include('landing.team')
        @include('landing.solution')
        @include('landing.portfolio')
        @include('landing.clients')
        @include('landing.events')
        @include('landing.news')
        @include('landing.contact')
    </main>
    @include('landing.footer')
    <div id="preloader"></div>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>
    <script src="{{ asset('js/jquery-3.5.1.js') }}"></script>
    <script src="{{ asset('js/purecounter_vanilla.js') }}"></script>
    <script src="{{ asset('js/aos.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/glightbox.min.js') }}"></script>
    <script src="{{ asset('js/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('js/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery.validate.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script src="{{ asset('js/contact-form.js') }}"></script>
</body>

</html>

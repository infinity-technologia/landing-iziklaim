<header id="header" class="fixed-top">
    <div class="container d-flex align-items-center justify-content-between">
        <h1 class="logo">
            <a href="{{ route('home') }}">
                <img class="img-fluid" src="{{ asset('img/logo_iziklaim.png') }}" alt="">
            </a>
        </h1>
        <nav id="navbar" class="navbar">
            <ul>
                <li>
                    <a class="nav-link scrollto" href="{{ route('home') }}#hero">Beranda</a>
                </li>
                <li>
                    <a class="nav-link scrollto" href="{{ route('home') }}#about-us">Tentang Kami</a>
                </li>
                <li>
                    <a class="nav-link scrollto" href="{{ route('home') }}#solution">Solusi Kami</a>
                </li>
                <li>
                    <a class="nav-link scrollto o" href="{{ route('home') }}#portfolio">Provider</a>
                </li>
                <li>
                    <a class="nav-link scrollto o" href="{{ route('home') }}#clients">Klien</a>
                </li>
                <li>
                    <a class="nav-link scrollto" href="{{ route('home') }}#events">Acara</a>
                </li>
                <li>
                    <a class="nav-link scrollto" href="{{ route('home') }}#news">News & Updates</a>
                </li>
                <li>
                    <a class="nav-link scrollto" href="{{ route('home') }}#contact">Hubungi Kami</a>
                </li>
                <li>
                    <a class="getstarted" href="{{ route('login') }}">Masuk</a>
                </li>
            </ul>
            <i class="bi bi-list mobile-nav-toggle"></i>
        </nav>
    </div>
</header>

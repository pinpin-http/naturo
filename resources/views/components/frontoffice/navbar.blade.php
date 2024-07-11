<!-- Navbar Start -->
<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg bg-white navbar-light py-3 py-lg-0 px-lg-5">
        <a href="{{ url('/') }}" class="navbar-brand ml-lg-3">
            <h1 class="m-0 text-primary"><span class="text-dark">SPA</span> Center</h1> <!-- A CHANGER PAR LE LOGO-->
        </a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between px-lg-3" id="navbarCollapse">
            <div class="navbar-nav m-auto py-0">
                <a href="{{ url('/') }}" class="nav-item nav-link {{ request()->is('/') ? 'active-page' : '' }}">Home</a>
                <a href="{{ url('/about') }}" class="nav-item nav-link {{ request()->is('about') ? 'active-page' : '' }}">About</a>
                <a href="{{ url('/service') }}" class="nav-item nav-link {{ request()->is('service') ? 'active-page' : '' }}">Services</a>
                <a href="{{ url('/price') }}" class="nav-item nav-link {{ request()->is('price') ? 'active-page' : '' }}">Pricing</a>   
                <a href="{{ url('/contact') }}" class="nav-item nav-link {{ request()->is('contact') ? 'active-page' : '' }}">Contact</a>
            </div>
            <a href="" class="btn btn-primary d-none d-lg-block">Book Now</a>
        </div>
    </nav>
</div>
<!-- Navbar End -->

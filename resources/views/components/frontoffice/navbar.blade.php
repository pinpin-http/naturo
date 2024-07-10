

<!-- Navbar Start -->
<div class="container-fluid p-0">
    <nav class="navbar navbar-expand-lg bg-white navbar-light py-3 py-lg-0 px-lg-5">
        <a href="{{ url('/') }}" class="navbar-brand ml-lg-3">
            <h1 class="m-0 text-primary"><span class="text-dark">SPA</span> Center</h1>
        </a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between px-lg-3" id="navbarCollapse">
            <div class="navbar-nav m-auto py-0">
                <a href="{{ url('/') }}" class="nav-item nav-link active">Home</a>
                <a href="{{ url('/about') }}" class="nav-item nav-link">About</a>
                <a href="{{ url('/service') }}" class="nav-item nav-link">Services</a>
                <a href="{{ url('/price') }}" class="nav-item nav-link">Pricing</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Pages</a>
                    <div class="dropdown-menu rounded-0 m-0">
                        <a href="{{ url('/appointment') }}" class="dropdown-item">Appointment</a>
                        <a href="{{ url('/opening') }}" class="dropdown-item">Open Hours</a>
                        <a href="{{ url('/team') }}" class="dropdown-item">Our Team</a>
                        <a href="{{ url('/testimonial') }}" class="dropdown-item">Testimonial</a>
                    </div>
                </div>
                <a href="{{ url('/contact') }}" class="nav-item nav-link">Contact</a>
            </div>
            <a href="" class="btn btn-primary d-none d-lg-block">Book Now</a>
        </div>
    </nav>
</div>
<!-- Navbar End -->

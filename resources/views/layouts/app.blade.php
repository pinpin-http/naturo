<!DOCTYPE html>
<html lang="en">

@include('components.frontoffice.head')

<body>
    @include('components.frontoffice.navbar')

    @yield('content')

    @include('components.frontoffice.footer')


    <script src="{{ mix('js/frontoffice/app.js') }}"></script>

</body>
</html>

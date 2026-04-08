<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/app.css') }}" />
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/font-awesome.css') }}" />
    <link rel="stylesheet" href="/mvc-app/public{{ mix('css/custom.css') }}" />
    @stack('style-css')
    <title>Allocate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="/mvc-app/public{{mix('js/app.js')}}" type="text/javascript"></script>
</head>
<div id="banner" style="background-color:#{{config('app.navbar_bg')}};">
    <img src="/images/BBC-Allocate-white.png" class="site-logo" width="100" height="13" alt="BBC Allocate">
</div>
@include('includes.menus')
<body style="margin: unset;">
    <main>
        <div id="container">
            @yield('content')
        </div>
    </main>
</body>
@stack('scripts')
</html>
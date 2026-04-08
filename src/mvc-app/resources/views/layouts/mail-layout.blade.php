<html>
<head>
    <style>
        {!! file_get_contents(public_path('css/email.css')) !!}
    </style>
</head>
<body>
    <main>
        <div id="container">
            <div>
                <div style="background-color: #4B72BF"><img src="{{ $message->embed(public_path('images/po_banner.png')) }}" /></div>
            </div>
            <div style="padding: 4px">
                @yield('content')
            </div>
            <div>
                <p align="center">
                    <img src="{{ $message->embed(public_path('images/BBC.png')) }}">
                    <br>
                    <font size="1">BBC {{App\Helpers\RomanNumber::numberToRoman(date("Y"))}} </font>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
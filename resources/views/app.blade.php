<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WINCH — Order Assignment System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('imgs/favicon.png') }}">
</head>
<body class="bg-gray-50 antialiased">
    <div id="app"></div>
</body>
</html>

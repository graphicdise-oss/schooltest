<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
</head>

<body>

    <div class="layout">
        @include('layouts.sidebar')

        <main class="content">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>

</html>
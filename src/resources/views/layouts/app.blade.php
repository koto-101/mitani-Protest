<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtechフリマ</title>
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
    />

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    @yield('css')

</head>
<body>
    <header>
        <div class="logo">
            <a href="/"><img src="{{ asset('images/logo.svg') }}" alt="Logo"></a>
        </div>

        {{-- 画面によってサイドバー（ナビバー）を出す --}}
        @if (!empty($useSidebar))
            @include('layouts.sidebar')
        @endif
    </header>

    <main>
        
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
<div class="nav-bar">
    <div class="nav-section left-area"></div> 

    <div class="nav-section center-area">
        <form action="/" method="GET" class="search-form">
            <input type="hidden" name="tab" value="{{ request('tab') }}">
            <input type="text" name="keyword" placeholder="なにをお探しですか？" class="search-input" value="{{ request('keyword') }}">
        </form>
    </div>

    <div class="nav-section right-area">
        @auth
            <form action="/logout" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="nav-link logout-button">ログアウト</button>
            </form>
        @endauth

        @guest
            <a href="/login" class="nav-link">ログイン</a>
        @endguest

        <a href="/mypage?page=buy" class="nav-link">マイページ</a>
        <a href="/sell" class="nav-link sell-button">出品</a>
    </div>
</div>
@extends('layouts.app') 

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}" />
@endsection

@section('content')
<div class="login-container">
    <h2>ログイン</h2>

    <form method="POST" action="/login">
        @csrf

        <div class="form-control-group">
            <label for="email">メールアドレス</label>
            <input type="text" name="email" id="email" value="{{ old('email') }}" autofocus>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-control-group">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password" >
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>


        <button type="submit">ログイン</button>
    </form>

    <a href="/register">会員登録はこちら</a>
</div>
@endsection
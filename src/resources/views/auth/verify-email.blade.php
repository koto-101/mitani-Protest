@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify-email.css') }}" />
@endsection

@section('content')

<div class="container">
    <div class="message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メールに記載されているリンクをクリックして、メール認証を完了してください。
    </div>
    
    <div style="margin-top: 20px;">
        <a href="http://localhost:8025" class="button">認証はこちらから</a>
    </div>

    <form method="POST" action="{{ route('verification.send') }}" style="margin-top: 20px;">
        @csrf
        <button type="submit" class="resend">認証メールを再送する</button>
    </form>
</div>
@endsection

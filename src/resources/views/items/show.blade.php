@php($useSidebar = true)
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/item.css') }}" />
@endsection

@section('content')
<div class="item-container">
    <div class="item-layout">
        {{-- 左側：商品画像 --}}
        <div class="item-column image-column">
            <img src="{{ asset('storage/' . $item->images->first()->image_path) }}" alt="{{ $item->title }}">
        </div>

        {{-- 右側：商品情報 --}}
        <div class="item-column info-column">
            <h2>{{ $item->title }}</h2>

            @if ($item->status === '売却済み')
                <span class="sold-label">sold</span>
            @endif

            <p class="brand-text">{{ $item->brand }}</p>
            <h4 class="price-text">¥{{ number_format($item->price) }} <small>（税込）</small></h4>

            {{-- アクションアイコン --}}
            <div class="icon-group">
                <form action="/item/{{ $item->id }}/like-toggle" method="POST">
                    @csrf
                    <button type="submit" class="like-button {{ $item->likes->contains('user_id', auth()->id()) ? 'liked' : '' }}">
                        <span>☆</span>
                        <span class="icon-count">{{ $item->likes->count() }}</span>
                    </button>
                </form>

                <a href="#comment-section" class="comment-link">
                    <span>💬</span>
                    <span class="icon-count">{{ $item->comments()->count() }}</span>
                </a>
            </div>

            {{-- 購入ボタン --}}
            <div class="purchase-button">
                <a href="/purchase/{{ $item->id }}" class="btn-red">購入手続きへ</a>
            </div>

            {{-- エラーメッセージ --}}
            @if(session('error'))
                <div class="error-box">{{ session('error') }}</div>
            @endif

            {{-- 商品説明 --}}
            <div class="section description">
                <h5>商品説明</h5>
                <p>{{ $item->description }}</p>
            </div>

            {{-- 商品情報 --}}
            <div class="section info">
                <h5>商品の情報</h5>
                <ul class="info-list">
                    <li>
                        カテゴリ:
                        @foreach($item->categories as $category)
                            <span class="category-badge">{{ $category->name }}</span>
                        @endforeach
                    </li>
                    <li>状態: {{ $item->condition }}</li>
                </ul>
            </div>

            {{-- コメント欄 --}}
            <div id="comment-section" class="section comments">
                <h4>コメント（{{ $item->comments()->count() }}件）</h4>

                {{-- コメント一覧 --}}
                @foreach($item->comments as $comment)
                    <div class="comment-card">
                        <div class="comment-body">
                            <img src="{{ $comment->user->profile_image_url }}" alt="ユーザー画像" class="profile-img">
                            <div class="comment-content">
                                <strong>{{ $comment->user->name }}</strong><br>
                                <span>{{ $comment->content }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- コメント投稿 --}}
                <form action="/item/{{ $item->id }}/comment" method="POST" class="comment-form">
                    @csrf
                    <div class="form-group">
                        <textarea name="content" class="comment-input" rows="3" placeholder="コメントを入力してください"></textarea>
                    </div>
                    <button type="submit" class="btn-red">コメントを送信する</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

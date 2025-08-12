@php($useSidebar = true)
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}" />
@endsection

@section('content')
<div class="container mypage">

    {{-- プロフィール情報 --}}
    <div class="profile-info">
        @if($user->avatar_path)
            <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="プロフィール画像" class="profile-avatar">
        @else
            <img src="{{ asset('images/default-avatar.png') }}" alt="デフォルト画像" class="profile-avatar">
        @endif

        <h3>{{ $user->name }}</h3>

        <a href="{{ url('/mypage/profile') }}" class="edit-profile-btn">プロフィールを編集</a>
    </div>

    {{-- タブ切り替え --}}
    <div class="tab-menu mt-3">
        <a href="{{ url('/mypage?page=buy') }}" class="tab-link {{ request('page') === 'buy' || !request('page') ? 'active' : '' }}">
            出品した商品
        </a>
        <a href="{{ url('/mypage?page=sell') }}" class="tab-link {{ request('page') === 'sell' ? 'active' : '' }}">
            購入した商品
        </a>
    </div>

    @foreach($items as $item)
        <div class="item-card">
            <a href="{{ url('/item/' . $item->id) }}">
                <img src="{{ asset('storage/' . optional($item->item_images->first())->image_path) }}" alt="{{ $item->title }}">
                <p>{{ $item->title }}</p>
                <p>¥{{ number_format($item->price) }}</p>
            </a>
        </div>
    @endforeach

</div>
@endsection

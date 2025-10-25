@php
    $useSidebar = true;
@endphp
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}" />
@endsection

@section('content')
<div class="container mypage">

    {{-- プロフィール情報 --}}
    <div class="d-flex align-items-center justify-content-between profile-header mb-4">
        <div class="d-flex align-items-center">
            <div class="profile-avatar-wrapper me-3">
                @if($user->avatar_path)
                    <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="プロフィール画像" class="profile-avatar">
                @else
                    <img src="{{ asset('images/default-avatar.png') }}" alt="デフォルト画像" class="profile-avatar">
                @endif
            </div>
            <div>
                <h3 class="mb-1">{{ $user->name }}</h3>
                @if(!is_null($user->average_rating))
                    <div class="rating-stars">
                        @php
                            $roundedRating = round($user->average_rating ?? 0);
                        @endphp
                        @for ($i = 1; $i <= 5; $i++)
                            @if($i <= $roundedRating)
                                <span class="star filled">★</span>
                            @else
                                <span class="star">☆</span>
                            @endif
                        @endfor
                    </div>
                @endif
            </div>
        </div>

        <a href="{{ url('/mypage/profile') }}" class="btn btn-outline-primary">プロフィールを編集</a>
    </div>

    {{-- タブメニュー --}}
    <div class="tab-menu mt-3">
        <a href="{{ url('/mypage?page=sell') }}" class="tab-link {{ $currentPage === 'sell' ? 'active' : '' }}">
            出品した商品
        </a>
        <a href="{{ url('/mypage?page=buy') }}" class="tab-link {{ $currentPage === 'buy' ? 'active' : '' }}">
            購入した商品
        </a>
        <a href="{{ url('/mypage?page=transaction') }}" class="tab-link {{ $currentPage === 'transaction' ? 'active' : '' }}">
            取引中の商品
            @if($totalUnreadCount > 0)
                <span class="badge bg-danger ms-1">{{ $totalUnreadCount }}</span>
            @endif
        </a>
    </div>

    {{-- 商品一覧 --}}
    <div class="item-list d-flex flex-wrap">
        @foreach($items as $item)
            <div class="item-card position-relative me-3 mb-4">
                @if($currentPage === 'transaction')
                    @if ($item->chatRoom)
                        {{-- <a href="{{ route('chat.show', ['chatRoom' => $item->chatRoom->id]) }}"> --}}
                            <a href="{{ url('/mypage/chat/' . $item->chatRoom->id) }}">
                            <img src="{{ asset('storage/' . optional($item->item_images->first())->image_path) }}" alt="{{ $item->title }}" class="item-image">
                            {{-- メッセージ数バッジ --}}
                            @if($item->unread_count > 0)
                                <span class="badge bg-danger position-absolute top-0 start-0 translate-middle badge-rounded">
                                    {{ $item->unread_count }}
                                </span>
                            @endif
                        </a>
                    @endif
                @else
                    <a href="{{ url('/item/' . $item->id) }}">
                        <img src="{{ asset('storage/' . optional($item->item_images->first())->image_path) }}" alt="{{ $item->title }}" class="item-image">
                    </a>
                @endif
                <p class="item-title mt-2">{{ $item->title }}</p>
                <p class="item-price">¥{{ number_format($item->price) }}</p>
            </div>
        @endforeach
    </div>

</div>
@endsection

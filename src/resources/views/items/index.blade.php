@php($useSidebar = true)
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}" />
@endsection

@section('content')
    <div class="container">
        <div class="tab-menu">
            
            <a href="{{ request()->fullUrlWithQuery(['tab' => null]) }}"
                class="{{ request('tab') !== 'mylist' ? 'active' : '' }}">おすすめ</a>

            <a href="{{ request()->fullUrlWithQuery(['tab' => 'mylist']) }}"
                class="{{ request('tab') === 'mylist' ? 'active' : '' }}">マイリスト</a>
        </div>

        <!-- 商品一覧 -->
        <div class="product-grid">
            @foreach ($items as $item)
                <div class="product-card">
                    <a href="/item/{{ $item->id }}">
                        @if ($item->images->isNotEmpty())
                           <img src="{{ Storage::url($item->images->first()->image_path) }}" alt="{{ $item->title }}">
                        @endif

                        @if ($item->status === '売却済み')
                            <span class="sold-label">sold</span>
                        @endif

                        <p class="product-title">{{ $item->title }}</p>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
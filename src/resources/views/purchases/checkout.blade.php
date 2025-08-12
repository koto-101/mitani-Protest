@php($useSidebar = true)
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}" />
@endsection

@section('content')
<div class="checkout-container">

    {{-- 商品情報 --}}
    <div class="product-info">
        @if ($item->images->isNotEmpty())
            <img src="{{ Storage::url($item->images->first()->image_path) }}" alt="{{ $item->title }}" style="max-width: 300px;">
        @endif

        <h2>{{ $item->title ?? '商品名なし' }}</h2>
        <p> ¥{{ number_format($item->price) }}</p>
    </div>

    {{-- 支払い方法 & 配送先 --}}
    <form action="{{ route('purchase.stripe.checkout', ['item_id' => $item->id]) }}" method="POST">
        @csrf

        {{-- 支払い方法選択 --}}
        <label>支払い方法</label>
        <details class="custom-dropdown" style="width: 200px;">
        <summary style="cursor: pointer; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            @if($paymentMethod === 'card')
            カード払い
            @elseif($paymentMethod === 'convenience')
            コンビニ払い
            @else
            -- 支払い方法を選択 --
            @endif
        </summary>
        <div class="custom-dropdown-menu" style="border: 1px solid #ccc; border-radius: 4px; margin-top: 4px; background: white;">
            <a href="{{ route('purchase.checkout', ['item_id' => $item->id, 'payment_method' => 'card']) }}" style="display: block; padding: 8px; text-decoration: none; color: black;">カード払い</a>
            <a href="{{ route('purchase.checkout', ['item_id' => $item->id, 'payment_method' => 'convenience']) }}" style="display: block; padding: 8px; text-decoration: none; color: black;">コンビニ払い</a>
        </div>
        </details>
        
        @error('payment_method')
            <div class="error" style="color: red; margin-top: 4px;">{{ $message }}</div>
        @enderror

        <input type="hidden" name="payment_method" value="{{ $paymentMethod }}">

        {{-- 配送先情報 --}}
        <div class="form-field">
            <label>配送先</label>
            <p>{{ $shippingAddress->postal_code }}<br>
            {{ $shippingAddress->address }}{{ $shippingAddress->building_name }}</p>
            <input type="hidden" name="shipping_address_id" value="{{ $shippingAddress->id ?? 'user' }}">
            @error('shipping_address_id')
                <div class="error">{{ $message }}</div>
            @enderror
            <a href="{{ url('/purchase/address/' . $item->id) }}" class="btn-secondary-custom">変更する</a>
        </div>

            <div class="payment-summary-box">
                <p><strong>商品代金</strong>　￥{{ number_format($item->price) }}</p>
                <p><strong>支払い方法</strong>　
                    @if($paymentMethod === 'card')
                        カード払い
                    @elseif($paymentMethod === 'convenience')
                        コンビニ払い
                    @endif
                </p>
            </div>

        {{-- 購入ボタン --}}
        <div class="form-field">
            <button type="submit" class="btn-primary-custom">購入する</button>
        </div>
    </form>
</div>
@endsection
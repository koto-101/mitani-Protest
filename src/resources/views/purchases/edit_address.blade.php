@php($useSidebar = true)
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/edit_address.css') }}" />
@endsection

@section('content')
<div class="edit-address-container">
    <h2>住所の変更</h2>

    {{-- 住所変更フォーム --}}
    <form method="POST" action="{{ route('purchase.update_address', ['item_id' => $item->id]) }}">
        @csrf

        <div class="form-field">
            <label for="postal_code">郵便番号</label>
            <input type="text" name="postal_code" class="input-text" value="{{ old('postal_code', $shippingAddress->postal_code ?? '') }}" required>
            @error('postal_code')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-field">
            <label for="address">住所</label>
            <input type="text" name="address" class="input-text" value="{{ old('address', $shippingAddress->address ?? '') }}" required>
            @error('address')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-field">
            <label for="building_name">建物名</label>
            <input type="text" name="building_name" class="input-text" value="{{ old('building_name', $shippingAddress->building_name ?? '') }}">
            @error('building_name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-primary-custom">更新する</button>
    </form>
</div>
@endsection

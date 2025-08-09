@php($useSidebar = true)
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/edit.css') }}" />
@endsection

@section('content')
<div class="profile-container">
    <h2>プロフィール設定</h2>

    <form action="/mypage/profile" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <div class="form-group avatar-group">
            <img src="{{ $user->avatar_path ? asset('storage/' . $user->avatar_path) : asset('images/default-avatar.png') }}"
                alt="プロフィール画像" class="profile-avatar">

            <div class="avatar-button-wrapper">
                <label for="avatar" class="file-input-label">画像を選択する</label>
                <input type="file" name="avatar" id="avatar" class="file-input-hidden" accept="image/*">
                @error('avatar')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="name">ユーザー名</label>
            <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}">
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="postal_code">郵便番号</label>
            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', auth()->user()->postal_code ?? '') }}">
            @error('postal_code')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="address">住所</label>
            <input type="text" name="address" id="address" value="{{ old('address', auth()->user()->address ?? '') }}">
            @error('address')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="building_name">建物名</label>
            <input type="text" name="building_name" id="building_name" value="{{ old('building_name', auth()->user()->building_name ?? '') }}">
            @error('building_name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn">更新する</button>
    </form>
</div>
@endsection
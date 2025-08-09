@php($useSidebar = true)
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/exhibition.css') }}" />
@endsection

@section('content')
<div class="container">
    <h2>商品の出品</h2>

    <form action="/sell" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-block">
            <label class="form-title">商品画像</label>
            <label for="images" class="file-upload-label">画像を選択する</label>
            <input type="file" name="images[]" id="images" multiple accept="image/*" class="file-upload-input">

            @error('images')
                <div class="error">{{ $message }}</div>
            @enderror
            @error('images.*')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <h3>商品の詳細</h3>
        <div class="form-block">
            <label>カテゴリー</label>
            <div class="checkbox-inline">
                @foreach($categories as $category)
                    <div class="checkbox-group">
                        <input class="checkbox-input" type="checkbox" name="categories[]" id="category_{{ $loop->index }}" value="{{ $category->id }}"
                        {{ (is_array(old('categories')) && in_array($category->id, old('categories'))) ? 'checked' : '' }}>
                        <label class="checkbox-label" for="category_{{ $loop->index }}">{{ $category->name }}</label>
                    </div>
                @endforeach
            </div>
            @error('categories')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-block">
            <label for="condition">商品の状態</label>
            <select name="condition" id="condition" class="select-input">
                <option value="" disabled selected>選択してください</option>
                <option value="良好" {{ old('condition') == '良好' ? 'selected' : '' }}>良好</option>
                <option value="目立った傷や汚れなし" {{ old('condition') == '目立った傷や汚れなし' ? 'selected' : '' }}>目立った傷や汚れなし</option>
                <option value="やや傷や汚れあり" {{ old('condition') == 'やや傷や汚れあり' ? 'selected' : '' }}>やや傷や汚れあり</option>
                <option value="状態が悪い" {{ old('condition') == '状態が悪い' ? 'selected' : '' }}>状態が悪い</option>
            </select>
            @error('condition')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <h3>商品と説明</h3>
        <div class="form-block">
            <label for="title">商品名</label>
            <input type="text" name="title" id="title" class="text-input" value="{{ old('title') }}">
            @error('title')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-block">
            <label for="brand">ブランド名</label>
            <input type="text" name="brand" id="brand" class="text-input" value="{{ old('brand') }}">
            @error('brand')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-block">
            <label for="description">商品の説明</label>
            <textarea name="description" id="description" rows="5" class="textarea-input">{{ old('description') }}</textarea>
            @error('description')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-block">
            <label for="price">販売価格</label>
            <input type="number" name="price" id="price" class="text-input" value="{{ old('price') }}" min="0">
            @error('price')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="submit-button">出品する</button>
    </form>
</div>
@endsection

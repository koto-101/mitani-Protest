<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

    class ProfileController extends Controller
{
    // 編集画面表示（GET）
    public function edit()
    {
        $user = Auth::user();

        return view('profiles.edit', compact('user'));
    }

    // 更新処理（PATCH）
    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        // プロフィール画像の保存
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        // ユーザー情報の更新
        $user->name = $request->input('name');
        $user->postal_code = $request->input('postal_code');
        $user->address = $request->input('address');
        $user->building_name = $request->input('building_name');
        $user->save();

        return redirect()->route('mypage.show');
    }

    public function show(Request $request)
    {
         $user = Auth::user();

    // クエリパラメータが "sell" の場合 → 購入商品
    if ($request->query('page') === 'sell') {
        $items = $user->purchases()->with('item.item_images')->get()->pluck('item');
    } else {
        // デフォルトは出品商品
        $items = $user->items()->with('item_images')->get();
    }

    return view('profiles.show', compact('user', 'items'));
    }
}

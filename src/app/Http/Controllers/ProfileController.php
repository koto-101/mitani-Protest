<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

    class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('profiles.edit', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

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

    if ($request->query('page') === 'sell') {
        $items = $user->purchases()->with('item.item_images')->get()->pluck('item');
    } else {
        $items = $user->items()->with('item_images')->get();
    }

    return view('profiles.show', compact('user', 'items'));
    }
}

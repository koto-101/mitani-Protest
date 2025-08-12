<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;

class PurchaseController extends Controller
{
    // 購入確認画面表示
    public function checkout(Request $request, $item_id)
    {
        $item = Item::with('images')->findOrFail($item_id);
        
        if ($item->user_id === Auth::id()) {
            return redirect()->back()->with('error', '自分の商品は購入できません。');
        }

        if ($item->status === '売却済み') {
            return redirect()->route('items.show', ['item' => $item->id])
                ->with('error', 'この商品はすでに売却済みです。');
        }

        $image_path = optional($item->images->first())->image_path;

        // ストレージのURLを生成（publicストレージの場合）
        $image_url = $image_path ? asset('storage/' . $image_path) : null;
        
        $user = Auth::user();

        $shippingAddress = ShippingAddress::where('item_id', $item->id)->first();

        if (!$shippingAddress) {
            // user テーブルの住所を使ったダミーオブジェクトを生成
            $shippingAddress = (object)[
                'postal_code' => $user->postal_code,
                'address' => $user->address,
                'building_name' => $user->building_name,
                'id' => null,
            ];
        }

        // id が null の場合は 'user' を使う
        $shipping_address_id = $shippingAddress->id ?? 'user';

        $paymentMethod = $request->query('payment_method');

        return view('purchases.checkout', compact('item', 'shippingAddress', 'paymentMethod'));
    }

    public function editAddress($item_id)
    {
        $item = Item::findOrFail($item_id);

        // すでに登録されている住所があれば取得
        $shippingAddress = ShippingAddress::where('item_id', $item_id)->first();

        return view('purchases.edit_address', compact('item', 'shippingAddress'));
    }

    // 住所更新処理
    public function updateAddress(AddressRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        // 住所の新規作成 or 更新（item_idで1件のみ）
        ShippingAddress::updateOrCreate(
            ['item_id' => $item->id],
            [
                'user_id'       => $user->id, 
                'item_id'       => $item->id,
                'postal_code'   => $request->postal_code,
                'address'       => $request->address,
                'building_name' => $request->building_name,
            ]
        );

        return redirect()->route('purchase.checkout', ['item_id' => $item->id]);
    }
}

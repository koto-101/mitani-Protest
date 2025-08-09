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

    // 購入処理
    // public function processPurchase(PurchaseRequest $request, $item_id)
    // {
    //     $item = Item::findOrFail($item_id);
    //     $user = Auth::user();

    //     $shipping_address_id = $request->input('shipping_address_id');

    //     $data = [
    //         'shipping_address_id' => $shipping_address_id === 'user' ? null : $shipping_address_id,
    //         // 他のデータもここでセット
    //     ];

    //     // 配送先の処理
    //     if ($request->shipping_address_id === 'user') {
    //         // ユーザー登録住所を使って配送先情報を構築
    //         $postal_code = $user->postal_code;
    //         $address = $user->address;
    //         $building_name = $user->building_name;
    //     } else {
    //         // 指定された shipping_addresses を使う
    //         $shippingAddress = ShippingAddress::findOrFail($request->shipping_address_id);

    //         // ユーザー本人のものでなければエラー（セキュリティ対策）
    //         if ($shippingAddress->user_id !== $user->id) {
    //             abort(403, '不正な配送先住所です');
    //         }

    //         $postal_code = $shippingAddress->postal_code;
    //         $address = $shippingAddress->address;
    //         $building_name = $shippingAddress->building_name;
    //     }

    //     // 購入情報保存（適宜 Purchase モデルに合わせて）
    //     Purchase::create([
    //         'user_id' => $user->id,
    //         'item_id' => $item->id,
    //         'payment_method' => $request->payment_method,
    //         'purchase_postal_code' => $postal_code,
    //         'purchase_address' => $address,
    //         'purchase_building_name' => $building_name,
    //         'shipping_address_id' => $shipping_address_id === 'user' ? null : $shipping_address_id,
    //     ]);

    //     $item->status = '売却済み';
    //     $item->save();

    //     return redirect('/');
    // }

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

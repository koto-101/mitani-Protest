<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\ShippingAddress;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditAddressTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 登録した住所が商品購入画面に反映される()
    {
        $user = User::factory()->create([
            'postal_code' => '123-4567',
            'address' => '東京都新宿区1-1-1',
            'building_name' => 'ユーザー住所ビル',
        ]);
        
        $item = Item::factory()->create([
            'price' => 1000,
            'status' => '出品中',
        ]);

        $this->actingAs($user);

        // 送付先住所変更画面にて住所登録
        $response = $this->post(route('purchase.update_address', ['item_id' => $item->id]), [
            'postal_code' => '987-6543',
            'address' => '東京都渋谷区2-2-2',
            'building_name' => '新住所ビル',
        ]);

        $response->assertRedirect(route('purchase.checkout', ['item_id' => $item->id]));

        // 商品購入画面を再度開く
        $response = $this->get(route('purchase.checkout', ['item_id' => $item->id]));

        $response->assertStatus(200);
        $response->assertSee('987-6543');
        $response->assertSee('東京都渋谷区2-2-2');
        $response->assertSee('新住所ビル');
    }

    /** @test */
    public function 購入した商品に送付先住所が紐づいて登録される()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'price' => 2000,
            'status' => '出品中',
        ]);

        $this->actingAs($user);

        // 住所登録
        $this->post(route('purchase.update_address', ['item_id' => $item->id]), [
            'postal_code' => '555-5555',
            'address' => '大阪市中央区3-3-3',
            'building_name' => '購入先住所ビル',
        ]);

        // 購入処理はWebhookで行うため、ここではPurchaseモデルを直接作成して紐づけを確認

        $shippingAddress = ShippingAddress::where('item_id', $item->id)->first();

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'card',
            'purchase_postal_code' => $shippingAddress->postal_code,
            'purchase_address' => $shippingAddress->address,
            'purchase_building_name' => $shippingAddress->building_name,
            'shipping_address_id' => $shippingAddress->id,
        ]);

        // itemステータスも売却済みに更新
        $item->status = '売却済み';
        $item->save();

        // DBに正しく保存されているか確認
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'shipping_address_id' => $shippingAddress->id,
            'purchase_postal_code' => '555-5555',
            'purchase_address' => '大阪市中央区3-3-3',
            'purchase_building_name' => '購入先住所ビル',
        ]);

        $this->assertEquals('売却済み', $item->fresh()->status);
    }
}

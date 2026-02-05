<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function createItemWithImage()
    {
        $item = Item::factory()->create();
        ItemImage::factory()->create(['item_id' => $item->id]);
        return $item;
    }

    /** @test */
    public function webhook_marks_item_as_sold()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyStripeWebhookSignature::class);

        $item = Item::factory()->create(['status' => '販売中']);

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'metadata' => [ 
                        'item_id' => $item->id,
                    ],
                    'client_reference_id' => 1,
                ],
            ],
        ];

        // ← JSON文字列で送る、Content-Typeをapplication/jsonに
        $response = $this->post('/stripe/webhook', json_encode($payload), [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => '売却済み',
        ]);
    }
    
    /** @test */
    public function purchased_item_shows_sold_label_in_list()
    {
        $item = $this->createItemWithImage();
        $item->update(['status' => '売却済み']);

        $response = $this->get('/');

        $response->assertSee('sold');
    }

    /** @test */
    public function purchased_item_appears_in_user_profile()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = $this->createItemWithImage();

        // 購入処理
        $this->post("/purchase/{$item->id}");

        // ProfileControllerで購入商品をビューに渡すように修正が必要
        // 例:
        // $userPurchases = Purchase::where('user_id', Auth::id())->with('item')->get();
        // return view('mypage.profile', compact('userPurchases'));

        $response = $this->get("/mypage/profile");

        $response->assertSee($item->title);
    }

    /** @test */
    public function selected_payment_method_is_reflected_in_checkout_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();

        $response = $this->get("/purchase/{$item->id}?payment_method=card");

        $response->assertSee('カード払い');
    }
}

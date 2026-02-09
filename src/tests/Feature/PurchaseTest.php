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
        $user = User::factory()->create(['id' => 1]);
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

        $response = $this->postJson('/stripe/webhook', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'status' => '売却済み',
        ]);

        $this->assertDatabaseHas('purchases', [
            'item_id' => $item->id,
            'user_id' => 1,
            'payment_method' => 'stripe',
        ]);

        $this->assertDatabaseHas('transactions', [
            'status' => 'in_progress',
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
        
        \App\Models\Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->get('/mypage?page=buy');

        $response->assertStatus(200);
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use Stripe\StripeClient;
use App\Models\Item;
use \App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    public function createCheckoutSession(PurchaseRequest $request, $item_id)
    {
        $item = Item::findOrFail($item_id);

        $paymentMethod = $request->input('payment_method');

        $userId = Auth::id();

        session(['payment_method' => $paymentMethod]);

        $paymentMethods = [];
        if ($paymentMethod === 'card') {
            $paymentMethods = ['card'];
        } elseif ($paymentMethod === 'convenience') {
            $paymentMethods = ['konbini'];
        } else {
            $paymentMethods = ['card'];
        }

        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => $paymentMethods,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'unit_amount' => $item->price,
                    'product_data' => [
                        'name' => $item->title,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase.stripe.cancel'),

            'metadata' => [
                'item_id' => $item->id,
            ],
            'client_reference_id' => $userId,
        ]);

        return redirect($session->url);
    }


    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) {
            return redirect('/')->with('error', 'セッションIDが見つかりません');
        }

        return redirect()->route('mypage.show')
                         ->with('success', '決済が完了しました。取引画面からやり取りを開始してください。');
    }

    public function cancel()
    {
        return redirect()->back()->with('error', '支払いがキャンセルされました');
    }

    public function handleWebhook(Request $request)
    {
        logger('Webhook accessed');
        $payload = $request->getContent();
        logger('RAW payload: ' . $payload);

        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload');
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature');
            return response('Invalid signature', 400);
        }

        logger('Event type: ' . $event->type);

        if ($event->type === 'checkout.session.completed') {
            try {
                $session = $event->data->object;

                $item_id = $session->metadata->item_id ?? null;
                $buyer_id = $session->client_reference_id ?? null;

                if (!$item_id || !$buyer_id) {
                    logger('Missing item_id or buyer_id in session metadata');
                    return response('Missing data', 400);
                }

                $item = Item::find($item_id);

                if (!$item) {
                    logger("Item not found: {$item_id}");
                    return response('Item not found', 404);
                }

                if ($item->status === '売却済み') {
                    logger("Item already sold: {$item_id}");
                    return response('Item already sold', 400);
                }

                // 売却済みに変更
                $item->status = '売却済み';
                $item->save();

                logger("Item ID {$item_id} marked as sold.");

                // 購入レコード作成
                $purchase = Purchase::create([
                    'user_id' => $buyer_id,
                    'item_id' => $item_id,
                    'payment_method' => 'stripe',
                ]);

                logger("Purchase record created for user {$buyer_id} and item {$item_id}.");

                Transaction::create([
                    'purchase_id' => $purchase->id,
                    'status' => 'in_progress',
                ]);

                logger("Transaction record created for purchase ID {$purchase->id}");

                // 取引チャットルーム作成
                try {
                    $chatRoom = \App\Models\ChatRoom::create([
                        'item_id' => $item->id,
                        'buyer_id' => $buyer_id,
                    ]);

                    logger("Chat room created for item {$item_id}, users: {$buyer_id} and {$item->user_id}");
                } catch (\Exception $e) {
                    Log::error("Failed to create chat room: " . $e->getMessage());
                }

            } catch (\Exception $e) {
                Log::error('Webhook processing failed: ' . $e->getMessage());
                return response('Webhook processing error', 500);
            }
        }

        return response('Webhook handled', 200);
    }
}
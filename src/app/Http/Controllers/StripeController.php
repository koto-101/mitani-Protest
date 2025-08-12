<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use Stripe\StripeClient;
use App\Models\Item;
use \App\Models\Purchase;
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

        return redirect('/');
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

        if (false)  {
            $event = json_decode($payload, true);
            logger('Event type: ' . $event['type']);

            return response()->json(['status' => 'ok'], 200);
        }

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
            $session = $event->data->object;

            logger('Metadata item_id: ' . ($session->metadata->item_id ?? 'none'));

            $item_id = $session->metadata->item_id ?? null;
            $user_id = $session->client_reference_id ?? null;

            if ($item_id && $user_id) {
                $item = Item::find($item_id);
                if ($item && $item->status !== '売却済み') {
                    $item->status = '売却済み';
                    $item->save();
                    logger("Item ID {$item_id} marked as sold.");

                    Purchase::create([
                        'user_id' => $user_id,
                        'item_id' => $item_id,
                        'payment_method' => 'stripe',
                    ]);
                    logger("Purchase record created for user {$user_id} and item {$item_id}.");
                }
            }
        }

        return response('Webhook handled', 200);
    }

}
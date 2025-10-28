<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        $status = $this->faker->randomElement(['in_progress', 'completed', 'buyer_completed', 'seller_completed']);
        
        // `completed_at` は `status` によって設定
        $completedAt = $status === 'in_progress' ? null : $this->faker->dateTime();

        // 評価に関しても `status` に応じて設定
        $buyerEvaluated = in_array($status, ['completed', 'buyer_completed']) ? $this->faker->boolean : null;
        $sellerEvaluated = in_array($status, ['completed', 'seller_completed']) ? $this->faker->boolean : null;

        return [
            'purchase_id' => Purchase::factory(),  // Purchase モデルを関連付けてファクトリーで作成
            'status' => $status,
            'completed_at' => $completedAt,
            'buyer_evaluated' => $buyerEvaluated,
            'seller_evaluated' => $sellerEvaluated,
        ];
    }
}

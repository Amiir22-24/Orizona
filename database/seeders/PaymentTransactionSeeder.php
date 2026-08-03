<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Subscription for Agent 2
        DB::table('subscriptions')->updateOrInsert(
            ['user_id' => 2],
            [
                'stripe_subscription_id' => 'sub_test_12345',
                'amount' => 50000.00,
                'status' => 'active',
                'ends_at' => now()->addMonths(10),
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ]
        );

        // Récupérer l'ID de la subscription
        $subscription = DB::table('subscriptions')->where('user_id', 2)->first();
        $subscriptionId = $subscription->id;

        // Payment
        DB::table('payments')->updateOrInsert(
            ['user_id' => 2, 'subscription_id' => $subscriptionId],
            [
                'stripe_charge_id' => 'ch_test_12345',
                'amount' => 50000.00,
                'status' => 'succeeded',
                'created_at' => now()->subMonths(2),
                'updated_at' => now(),
            ]
        );

        $payment = DB::table('payments')
            ->where('user_id', 2)
            ->where('subscription_id', $subscriptionId)
            ->first();
        $paymentId = $payment->id;

        // Transaction
        DB::table('transactions')->updateOrInsert(
            ['user_id' => 2, 'type' => 'commission', 'property_id' => 1],
            [
                'amount' => 25000.00,
                'currency' => 'XOF',
                'stripe_payment_intent_id' => null,
                'status' => 'succeeded',
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ]
        );

        $transaction = DB::table('transactions')
            ->where('user_id', 2)
            ->where('type', 'commission')
            ->where('property_id', 1)
            ->first();
        $transactionId = $transaction->id;

        // Commission
        DB::table('commissions')->updateOrInsert(
            ['agent_id' => 2, 'property_id' => 1],
            [
                'transaction_id' => $transactionId,
                'amount' => 25000.00,
                'rate' => 10.00,
                'status' => 'paid',
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ]
        );

        // Receipt
        DB::table('receipts')->updateOrInsert(
            ['receipt_number' => 'REC-2025-001'],
            [
                'user_id' => 2,
                'transaction_id' => $transactionId,
                'pdf_url' => 'receipts/REC-2025-001.pdf',
                'amount' => 25000.00,
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ]
        );

        echo "✅ Paiements/Transactions créés (Sub:{$subscriptionId}, Trans:{$transactionId})\n";
    }
}


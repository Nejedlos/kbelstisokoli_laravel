<?php

namespace App\Services\Finance;

use App\Models\FinanceCharge;
use App\Models\FinancePayment;
use App\Models\ChargePaymentAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Alokuje částku z platby na konkrétní předpis.
     */
    public function allocate(FinancePayment $payment, FinanceCharge $charge, float $amount, ?string $note = null): ChargePaymentAllocation
    {
        return DB::transaction(function () use ($payment, $charge, $amount, $note) {
            // Validace zbývajících částek by měla proběhnout i v UI, zde jako pojistka
            $availableInPayment = $payment->amount_available;
            $remainingInCharge = $charge->amount_remaining;

            if ($amount > $availableInPayment + 0.01) { // Malá tolerance pro float
                throw new \Exception("Částka k alokaci ({$amount}) převyšuje dostupnou částku v platbě ({$availableInPayment}).");
            }

            if ($amount > $remainingInCharge + 0.01) {
                // Zde můžeme povolit přeplatek, pokud je to žádoucí, ale pro MVP budeme striktní
                // throw new \Exception("Částka k alokaci ({$amount}) převyšuje zbývající dluh v předpisu ({$remainingInCharge}).");
            }

            $allocation = ChargePaymentAllocation::create([
                'finance_payment_id' => $payment->id,
                'finance_charge_id' => $charge->id,
                'amount' => $amount,
                'allocated_at' => now(),
                'note' => $note,
            ]);

            $this->syncChargeStatus($charge);

            return $allocation;
        });
    }

    /**
     * Synchronizuje status předpisu na základě zaplacené částky.
     */
    public function syncChargeStatus(FinanceCharge $charge): void
    {
        if ($charge->status === 'cancelled' || $charge->status === 'draft') {
            return;
        }

        $paid = $charge->amount_paid;
        $total = (float) $charge->amount_total;

        if ($paid >= $total - 0.01) {
            $charge->status = 'paid';
        } elseif ($paid > 0) {
            $charge->status = 'partially_paid';
        } else {
            // Zkontrolovat, zda není po splatnosti
            if ($charge->due_date && $charge->due_date->isPast()) {
                $charge->status = 'overdue';
            } else {
                $charge->status = 'open';
            }
        }

        $charge->save();
    }

    /**
     * Získá ekonomický souhrn pro člena.
     */
    public function getMemberSummary(User $user): array
    {
        $charges = FinanceCharge::where('user_id', $user->id)
            ->where('is_visible_to_member', true)
            ->whereIn('status', ['open', 'partially_paid', 'overdue'])
            ->withSum('allocations as paid_sum', 'amount')
            ->get();

        $totalToPay = 0;
        $overdueAmount = 0;

        foreach ($charges as $charge) {
            $remaining = $charge->amount_remaining;
            $totalToPay += $remaining;

            if ($charge->is_overdue) {
                $overdueAmount += $remaining;
            }
        }

        $paidTotal = FinanceCharge::where('user_id', $user->id)
            ->where('status', 'paid')
            ->sum('amount_total');

        return [
            'total_to_pay' => $totalToPay,
            'overdue_amount' => $overdueAmount,
            'paid_total' => $paidTotal,
            'open_charges_count' => $charges->count(),
        ];
    }

    /**
     * Získá globální ekonomický souhrn pro administraci.
     */
    public function getAdminSummary(): array
    {
        // Optimalizováno: suma amount_total - suma alokací
        $totalChargesSum = (float) FinanceCharge::whereIn('status', ['open', 'partially_paid', 'overdue'])->sum('amount_total');
        $totalAllocationsSum = (float) DB::table('charge_payment_allocations')
            ->whereIn('finance_charge_id', function($q) {
                $q->select('id')->from('finance_charges')->whereIn('status', ['open', 'partially_paid', 'overdue']);
            })
            ->sum('amount');

        $overdueChargesSum = (float) FinanceCharge::where('status', 'overdue')->sum('amount_total');
        $overdueAllocationsSum = (float) DB::table('charge_payment_allocations')
            ->whereIn('finance_charge_id', function($q) {
                $q->select('id')->from('finance_charges')->where('status', 'overdue');
            })
            ->sum('amount');

        return [
            'total_receivables' => $totalChargesSum - $totalAllocationsSum,
            'total_overdue' => $overdueChargesSum - $overdueAllocationsSum,
            'payments_received_month' => (float) FinancePayment::where('paid_at', '>=', now()->startOfMonth())->sum('amount'),
            'active_charges_count' => FinanceCharge::whereIn('status', ['open', 'partially_paid', 'overdue'])->count(),
        ];
    }
}

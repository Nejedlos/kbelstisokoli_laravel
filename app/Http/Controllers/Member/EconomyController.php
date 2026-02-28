<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\FinanceCharge;
use App\Models\FinancePayment;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EconomyController extends Controller
{
    /**
     * Zobrazí přehled plateb a příspěvků člena.
     */
    public function index(Request $request, FinanceService $financeService): View
    {
        $user = auth()->user();

        // 1. Souhrnné údaje
        $summary = $financeService->getMemberSummary($user);

        // 2. Otevřené předpisy (k úhradě)
        $openCharges = FinanceCharge::where('user_id', $user->id)
            ->where('is_visible_to_member', true)
            ->whereIn('status', ['open', 'partially_paid', 'overdue'])
            ->withSum('allocations as paid_sum', 'amount')
            ->orderBy('due_date', 'asc')
            ->get();

        // 3. Historie (uhrazené předpisy a platby)
        $paidCharges = FinanceCharge::where('user_id', $user->id)
            ->where('is_visible_to_member', true)
            ->where('status', 'paid')
            ->withSum('allocations as paid_sum', 'amount')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $recentPayments = FinancePayment::where('user_id', $user->id)
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get();

        return view('member.economy.index', [
            'summary' => $summary,
            'openCharges' => $openCharges,
            'paidCharges' => $paidCharges,
            'recentPayments' => $recentPayments,
        ]);
    }
}

<?php

namespace App\Services\Dashboard;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Models\Payment;
use App\Models\PaymentRequest;
use App\Models\RepaymentSchedule;
use Illuminate\Support\Carbon;

class DashboardMetrics
{
    /**
     * @return array<string, mixed>
     */
    public function summary(?Carbon $asOf = null): array
    {
        $asOf ??= today();

        $activeLoans = Loan::query()->whereIn('status', ['active', 'disbursed', 'approved'])->count();
        $outstandingBalance = (float) Loan::query()->whereIn('status', ['active', 'disbursed', 'approved'])->sum('outstanding_balance');
        $arrearsAmount = (float) RepaymentSchedule::query()
            ->whereDate('due_date', '<', $asOf)
            ->whereIn('status', ['pending', 'partial'])
            ->selectRaw('COALESCE(SUM(principal_due + interest_due + fees_due + penalty_due - amount_paid), 0) as total')
            ->value('total');
        $portfolioAtRisk = $outstandingBalance > 0 ? round(($arrearsAmount / $outstandingBalance) * 100, 2) : 0.0;

        return [
            'borrowers' => Borrower::query()->count(),
            'active_loans' => $activeLoans,
            'outstanding_balance' => $outstandingBalance,
            'portfolio_quality' => max(0, round(100 - $portfolioAtRisk, 2)),
            'due_today' => (float) RepaymentSchedule::query()
                ->whereDate('due_date', $asOf)
                ->selectRaw('COALESCE(SUM(principal_due + interest_due + fees_due + penalty_due - amount_paid), 0) as total')
                ->value('total'),
            'paid_today' => (float) Payment::query()->whereDate('paid_at', $asOf)->sum('amount'),
            'arrears_amount' => $arrearsAmount,
            'portfolio_at_risk' => $portfolioAtRisk,
            'pending_payment_requests' => PaymentRequest::query()->whereIn('status', ['pending', 'requested'])->count(),
            'workflow' => [
                'initiator' => LoanApplication::query()->where('status', 'draft')->count(),
                'authorizer' => LoanApplication::query()->where('status', 'submitted')->count(),
                'validator' => LoanApplication::query()->where('status', 'under_review')->count(),
            ],
        ];
    }
}

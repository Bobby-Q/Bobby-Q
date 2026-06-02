<?php

namespace App\Http\Controllers\LoanProducts;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LoanProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoanProductController extends Controller
{
    public function index(): View
    {
        return view('loan-products.index', [
            'products' => LoanProduct::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('loan-products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160', 'unique:loan_products,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'min_principal' => ['nullable', 'numeric', 'min:0'],
            'max_principal' => ['nullable', 'numeric', 'min:0', 'gte:min_principal'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'repayment_period' => ['required', 'integer', 'min:1', 'max:240'],
            'repayment_period_type' => ['required', 'in:day,week,month,year'],
        ]);

        $product = LoanProduct::create($validated + [
            'principal_type' => 'range',
            'interest_method' => 'flat',
            'interest_period' => 'month',
            'status' => 'active',
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => 'loan_product.created',
            'auditable_type' => LoanProduct::class,
            'auditable_id' => $product->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
            'new_values' => $product->only(['name', 'min_principal', 'max_principal', 'interest_rate', 'status']),
        ]);

        return redirect()->route('loan-products.index')->with('status', 'Loan product created successfully.');
    }
}

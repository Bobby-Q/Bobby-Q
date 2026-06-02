<?php

namespace App\Http\Controllers\Borrowers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Borrower;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BorrowerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $borrowers = Borrower::query()
            ->with(['assignedUser', 'organizationUnit'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('other_name', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('national_id_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('borrowers.index', compact('borrowers', 'search'));
    }

    public function create(): View
    {
        return view('borrowers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'other_name' => ['nullable', 'string', 'max:120'],
            'national_id_number' => ['nullable', 'string', 'max:64', 'unique:borrowers,national_id_number'],
            'phone_number' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'physical_address' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:32'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ]);

        $borrower = Borrower::create($validated + [
            'account_number' => $this->nextAccountNumber(),
            'assigned_user_id' => $request->user()->id,
            'status' => 'active',
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => 'borrower.created',
            'auditable_type' => Borrower::class,
            'auditable_id' => $borrower->id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
            'new_values' => $borrower->only(['account_number', 'first_name', 'other_name', 'phone_number', 'status']),
        ]);

        return redirect()->route('borrowers.index')->with('status', 'Borrower created successfully.');
    }

    private function nextAccountNumber(): string
    {
        do {
            $accountNumber = 'BRW-'.now()->format('ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Borrower::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }
}

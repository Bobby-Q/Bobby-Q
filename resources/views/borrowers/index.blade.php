<x-page title="Borrowers" heading="Borrowers" eyebrow="Customer management">
    <div class="toolbar-row">
        <form method="GET" action="{{ route('borrowers.index') }}" class="search-form">
            <input name="search" value="{{ $search }}" placeholder="Search name, phone, ID or account">
            <button class="primary-button" type="submit">Search</button>
        </form>
        <a class="primary-link" href="{{ route('borrowers.create') }}">New borrower</a>
    </div>

    <section class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>ID Number</th>
                    <th>Status</th>
                    <th>Assigned</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($borrowers as $borrower)
                    <tr>
                        <td>{{ $borrower->account_number }}</td>
                        <td>{{ trim($borrower->first_name.' '.$borrower->other_name) }}</td>
                        <td>{{ $borrower->phone_number }}</td>
                        <td>{{ $borrower->national_id_number ?? '—' }}</td>
                        <td><span class="status-pill">{{ ucfirst($borrower->status) }}</span></td>
                        <td>{{ $borrower->assignedUser?->name ?? 'Unassigned' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No borrowers found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $borrowers->links() }}
    </section>
</x-page>

<x-page title="Loan Products" heading="Loan Products" eyebrow="Product setup">
    <div class="toolbar-row"><a class="primary-link" href="{{ route('loan-products.create') }}">New product</a></div>
    <section class="table-card">
        <table>
            <thead><tr><th>Name</th><th>Principal</th><th>Interest</th><th>Term</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ number_format((float) $product->min_principal, 2) }} - {{ number_format((float) $product->max_principal, 2) }}</td>
                        <td>{{ $product->interest_rate }}% / {{ $product->interest_period }}</td>
                        <td>{{ $product->repayment_period }} {{ $product->repayment_period_type }}(s)</td>
                        <td><span class="status-pill">{{ ucfirst($product->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5">No loan products configured.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $products->links() }}
    </section>
</x-page>

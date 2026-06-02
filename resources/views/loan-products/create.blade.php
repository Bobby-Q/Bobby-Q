<x-page title="New loan product" heading="New loan product" eyebrow="Product setup">
    <section class="form-card">
        @if ($errors->any())
            <div class="alert danger"><strong>Check the form</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('loan-products.store') }}" class="auth-form">
            @csrf
            <label>Name</label><input name="name" value="{{ old('name') }}" required>
            <label>Description</label><input name="description" value="{{ old('description') }}">
            <label>Minimum principal</label><input type="number" step="0.01" name="min_principal" value="{{ old('min_principal') }}">
            <label>Maximum principal</label><input type="number" step="0.01" name="max_principal" value="{{ old('max_principal') }}">
            <label>Interest rate (%)</label><input type="number" step="0.0001" name="interest_rate" value="{{ old('interest_rate') }}" required>
            <label>Repayment period</label><input type="number" name="repayment_period" value="{{ old('repayment_period', 1) }}" required>
            <label>Repayment period type</label>
            <select name="repayment_period_type" required><option>day</option><option>week</option><option selected>month</option><option>year</option></select>
            <button class="primary-button" type="submit">Create product</button>
        </form>
    </section>
</x-page>

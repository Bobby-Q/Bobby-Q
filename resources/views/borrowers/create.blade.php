<x-page title="New borrower" heading="New borrower" eyebrow="Borrower onboarding">
    <section class="form-card">
        @if ($errors->any())
            <div class="alert danger"><strong>Check the form</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('borrowers.store') }}" class="auth-form">
            @csrf
            <label>First name</label><input name="first_name" value="{{ old('first_name') }}" required>
            <label>Other name</label><input name="other_name" value="{{ old('other_name') }}">
            <label>Phone number</label><input name="phone_number" value="{{ old('phone_number') }}" required>
            <label>National ID number</label><input name="national_id_number" value="{{ old('national_id_number') }}">
            <label>Email</label><input type="email" name="email" value="{{ old('email') }}">
            <label>Gender</label><input name="gender" value="{{ old('gender') }}">
            <label>Date of birth</label><input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
            <label>Physical address</label><input name="physical_address" value="{{ old('physical_address') }}">
            <button class="primary-button" type="submit">Create borrower</button>
        </form>
    </section>
</x-page>

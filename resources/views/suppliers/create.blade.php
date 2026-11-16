@extends('layouts.app')
@section('title','New Supplier')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New Supplier</h1>
        <p>Add a new vendor or supplier</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('suppliers.index') }}" class="btn">Cancel</a>
    </div>
</div>

<form method="POST" action="{{ route('suppliers.store') }}">
@csrf
<div class="form-section">
    <div class="form-section-header"><h3>Supplier Information</h3></div>
    <div class="form-section-body">
        <div class="form-grid">
            <div class="field">
                <label class="required">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contact person name">
                @error('name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label>Company</label>
                <input type="text" name="company" value="{{ old('company') }}" placeholder="Company / business name">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="supplier@company.com">
                @error('email')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 234 567 8900">
            </div>
            <div class="field">
                <label>Tax Number</label>
                <input type="text" name="tax_number" value="{{ old('tax_number') }}" placeholder="VAT / TIN number">
            </div>
            <div class="field">
                <label class="required">Payment Terms</label>
                <select name="payment_terms">
                    @foreach(['Net 15','Net 30','Net 45','Net 60','Immediate','COD'] as $t)
                        <option value="{{ $t }}" @selected(old('payment_terms',$t) == $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="required">Status</label>
                <select name="status">
                    <option value="active" @selected(old('status','active')=='active')>Active</option>
                    <option value="inactive" @selected(old('status')=='inactive')>Inactive</option>
                </select>
            </div>
            <div class="field full-width">
                <label>Address</label>
                <textarea name="address" rows="2" placeholder="Street, city, country">{{ old('address') }}</textarea>
            </div>
            <div class="field full-width">
                <label>Notes</label>
                <textarea name="notes" rows="2" placeholder="Internal notes...">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Supplier</button>
        <a href="{{ route('suppliers.index') }}" class="btn">Cancel</a>
    </div>
</div>
</form>
@endsection

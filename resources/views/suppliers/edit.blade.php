@extends('layouts.app')
@section('title','Edit Supplier')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Supplier</h1>
        <p>{{ $supplier->name }}</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('suppliers.show',$supplier) }}" class="btn">Cancel</a>
    </div>
</div>

<form method="POST" action="{{ route('suppliers.update',$supplier) }}">
@csrf @method('PUT')
<div class="form-section">
    <div class="form-section-header"><h3>Supplier Information</h3></div>
    <div class="form-section-body">
        <div class="form-grid">
            <div class="field">
                <label class="required">Name</label>
                <input type="text" name="name" value="{{ old('name',$supplier->name) }}" required>
                @error('name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label>Company</label>
                <input type="text" name="company" value="{{ old('company',$supplier->company) }}">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email',$supplier->email) }}">
            </div>
            <div class="field">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone',$supplier->phone) }}">
            </div>
            <div class="field">
                <label>Tax Number</label>
                <input type="text" name="tax_number" value="{{ old('tax_number',$supplier->tax_number) }}">
            </div>
            <div class="field">
                <label class="required">Payment Terms</label>
                <select name="payment_terms">
                    @foreach(['Net 15','Net 30','Net 45','Net 60','Immediate','COD'] as $t)
                        <option value="{{ $t }}" @selected(old('payment_terms',$supplier->payment_terms)==$t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="required">Status</label>
                <select name="status">
                    <option value="active" @selected(old('status',$supplier->status)=='active')>Active</option>
                    <option value="inactive" @selected(old('status',$supplier->status)=='inactive')>Inactive</option>
                </select>
            </div>
            <div class="field full-width">
                <label>Address</label>
                <textarea name="address" rows="2">{{ old('address',$supplier->address) }}</textarea>
            </div>
            <div class="field full-width">
                <label>Notes</label>
                <textarea name="notes" rows="2">{{ old('notes',$supplier->notes) }}</textarea>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('suppliers.show',$supplier) }}" class="btn">Cancel</a>
    </div>
</div>
</form>
@endsection

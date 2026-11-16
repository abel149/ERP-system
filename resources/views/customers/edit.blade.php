@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="card" style="max-width:640px">
    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf @method('PUT')
        @include('customers._form')
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn" href="{{ route('customers.index') }}">Cancel</a>
    </form>
</div>
@endsection

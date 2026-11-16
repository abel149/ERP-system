@extends('layouts.app')

@section('title', 'New Customer')

@section('content')
<div class="card" style="max-width:640px">
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        @include('customers._form')
        <button type="submit" class="btn btn-primary">Create Customer</button>
        <a class="btn" href="{{ route('customers.index') }}">Cancel</a>
    </form>
</div>
@endsection

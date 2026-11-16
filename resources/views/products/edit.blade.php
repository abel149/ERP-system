@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf @method('PUT')
        @include('products._form')
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn" href="{{ route('products.index') }}">Cancel</a>
    </form>
</div>
@endsection

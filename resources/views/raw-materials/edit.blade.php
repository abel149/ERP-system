@extends('layouts.app')

@section('title', 'Edit Raw Material')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>Edit Raw Material</h1>
        <p>{{ $rawMaterial->name }} ({{ $rawMaterial->sku }})</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('raw-materials.index') }}" class="btn">Back</a>
    </div>
</div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('raw-materials.update', $rawMaterial) }}">
        @csrf @method('PUT')
        @include('raw-materials._form')
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a class="btn" href="{{ route('raw-materials.index') }}">Cancel</a>
    </form>
</div>
@endsection

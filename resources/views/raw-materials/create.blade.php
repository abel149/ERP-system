@extends('layouts.app')

@section('title', 'New Raw Material')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1>New Raw Material</h1>
        <p>Add a material to the raw-materials inventory</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('raw-materials.index') }}" class="btn">Cancel</a>
    </div>
</div>

<div class="card" style="max-width:680px">
    <form method="POST" action="{{ route('raw-materials.store') }}">
        @csrf
        @include('raw-materials._form')
        <button type="submit" class="btn btn-primary">Create Material</button>
        <a class="btn" href="{{ route('raw-materials.index') }}">Cancel</a>
    </form>
</div>
@endsection

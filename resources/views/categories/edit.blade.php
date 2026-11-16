@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('categories.update', $category) }}">
        @csrf @method('PUT')
        <div class="field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required>
            @error('name')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="description">Description</label>
            <input type="text" id="description" name="description" value="{{ old('description', $category->description) }}">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a class="btn" href="{{ route('categories.index') }}">Cancel</a>
    </form>
</div>
@endsection

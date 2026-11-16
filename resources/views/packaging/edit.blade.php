@extends('layouts.app')

@section('title', 'Edit Packaging Type')

@section('content')
<div class="card" style="max-width:560px">
    <form method="POST" action="{{ route('packaging-types.update', $packagingType) }}">
        @csrf @method('PUT')
        <div class="field">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $packagingType->name) }}" required>
            @error('name')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="capacity_kg">Capacity (kg)</label>
            <input type="number" id="capacity_kg" name="capacity_kg" value="{{ old('capacity_kg', $packagingType->capacity_kg) }}" min="0.01" step="0.01" required>
            @error('capacity_kg')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="unit_cost">Cost / package (ETB)</label>
            <input type="number" id="unit_cost" name="unit_cost" value="{{ old('unit_cost', $packagingType->unit_cost) }}" min="0" step="0.01" required>
            @error('unit_cost')<div class="error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label style="display:flex;align-items:center;gap:8px;font-weight:500">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $packagingType->is_active))> Active
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a class="btn" href="{{ route('packaging-types.index') }}">Cancel</a>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Packaging Types')

@section('content')
<div class="card">
    <h2>New Packaging Type</h2>
    <form method="POST" action="{{ route('packaging-types.store') }}" class="inline-form">
        @csrf
        <div class="field" style="min-width:200px">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. 5 kg PP bag">
        </div>
        <div class="field">
            <label for="capacity_kg">Capacity (kg)</label>
            <input type="number" id="capacity_kg" name="capacity_kg" value="{{ old('capacity_kg') }}" min="0.01" step="0.01" required placeholder="5">
        </div>
        <div class="field">
            <label for="unit_cost">Cost / package (ETB)</label>
            <input type="number" id="unit_cost" name="unit_cost" value="{{ old('unit_cost') }}" min="0" step="0.01" required placeholder="250">
        </div>
        <button type="submit" class="btn btn-primary">Add Type</button>
    </form>
    <p class="muted" style="margin-top:10px;font-size:12.5px">Used on production batches: packing cost = (output kg &divide; capacity) &times; cost per package.</p>
</div>

<div class="card">
    <h2>All Packaging Types</h2>
    <table class="data">
        <thead>
            <tr><th>Name</th><th class="text-right">Capacity (kg)</th><th class="text-right">Cost / package</th><th class="text-right">Cost / kg</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($types as $type)
                <tr>
                    <td><strong>{{ $type->name }}</strong></td>
                    <td class="text-right">{{ rtrim(rtrim(number_format((float) $type->capacity_kg, 2), '0'), '.') }}</td>
                    <td class="text-right">{{ number_format((float) $type->unit_cost, 2) }}</td>
                    <td class="text-right muted">{{ number_format((float) $type->unit_cost / max((float) $type->capacity_kg, 0.01), 2) }}</td>
                    <td><span class="badge {{ $type->is_active ? 'badge-green' : 'badge-gray' }}">{{ $type->is_active ? 'Active' : 'Off' }}</span></td>
                    <td class="text-right">
                        <a class="btn btn-sm" href="{{ route('packaging-types.edit', $type) }}">Edit</a>
                        @if($type->is_active)
                        <form method="POST" action="{{ route('packaging-types.destroy', $type) }}" style="display:inline"
                              onsubmit="return confirm('Deactivate this packaging type? Past batches keep their recorded cost.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No packaging types yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination mt">{{ $types->links() }}</div>
</div>
@endsection

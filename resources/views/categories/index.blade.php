@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="card">
    <h2>New Category</h2>
    <form method="POST" action="{{ route('categories.store') }}" class="inline-form">
        @csrf
        <div class="field" style="min-width:220px">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="field" style="flex:1;min-width:260px">
            <label for="description">Description (optional)</label>
            <input type="text" id="description" name="description" value="{{ old('description') }}">
        </div>
        <button type="submit" class="btn btn-primary">Add Category</button>
    </form>
</div>

<div class="card">
    <h2>All Categories</h2>
    <table class="data">
        <thead>
            <tr><th>Name</th><th>Description</th><th>Products</th><th></th></tr>
        </thead>
        <tbody>
            @forelse ($categories as $category)
                <tr>
                    <td><strong>{{ $category->name }}</strong></td>
                    <td class="muted">{{ $category->description ?: '—' }}</td>
                    <td><span class="badge badge-gray">{{ $category->products_count }}</span></td>
                    <td class="text-right">
                        <a class="btn btn-sm" href="{{ route('categories.edit', $category) }}">Edit</a>
                        <form method="POST" action="{{ route('categories.destroy', $category) }}" style="display:inline"
                              onsubmit="return confirm('Delete this category? Products inside will be kept uncategorized.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No categories yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination mt">{{ $categories->links() }}</div>
</div>
@endsection

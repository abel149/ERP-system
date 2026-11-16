@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="card" style="max-width: 640px;">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        @include('users._form')
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update User</button>
            <a class="btn" href="{{ route('users.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection

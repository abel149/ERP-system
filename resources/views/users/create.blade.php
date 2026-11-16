@extends('layouts.app')

@section('title', 'New User')

@section('content')
<div class="card" style="max-width: 640px;">
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        @include('users._form')
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a class="btn" href="{{ route('users.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')
@section('title','Users')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>System Users</h1>
        <p>Staff accounts and role management</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('users.create') }}" class="btn btn-primary">+ New User</a>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Sales</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($users as $user)
            @php
                $roleBadge = match($user->role) {
                    'admin'     => 'badge-red',
                    'cashier'   => 'badge-blue',
                    'inventory' => 'badge-amber',
                    'hr'        => 'badge-purple',
                    default     => 'badge-gray',
                };
            @endphp
            <tr>
                <td>
                    <div class="strong">{{ $user->name }} @if($user->id === auth()->id())<span class="badge badge-gray" style="font-size:10px;">you</span>@endif</div>
                </td>
                <td class="muted">{{ $user->email }}</td>
                <td><span class="badge {{ $roleBadge }}">{{ ucfirst($user->role) }}</span></td>
                <td><span class="badge badge-gray">{{ $user->sales_count }}</span></td>
                <td class="text-right">
                    <a href="{{ route('users.edit',$user) }}" class="btn btn-sm">Edit</a>
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy',$user) }}" style="display:inline"
                              onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="muted" style="padding:24px;text-align:center;">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $users->links() }}</div>
</div>
@endsection

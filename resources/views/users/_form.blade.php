<div class="form-row">
    <div class="field">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
        @error('name')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>
<div class="field">
    <label for="role">Role *</label>
    <select id="role" name="role" required>
        @foreach ($roles as $role)
            <option value="{{ $role }}" @selected(old('role', $user->role ?? 'cashier') === $role)>
                {{ ucfirst($role) }}
            </option>
        @endforeach
    </select>
    <div class="muted">Admin: everything · Cashier: sales + customers · Inventory: products + stock · HR: employees + payroll</div>
    @error('role')<div class="error">{{ $message }}</div>@enderror
</div>
<div class="form-row">
    <div class="field">
        <label for="password">Password {{ isset($user) ? '(leave blank to keep)' : '*' }}</label>
        <input type="password" id="password" name="password" autocomplete="new-password" {{ isset($user) ? '' : 'required' }}>
        @error('password')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
    </div>
</div>

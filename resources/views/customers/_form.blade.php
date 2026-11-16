<div class="form-row">
    <div class="field">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name', $customer->name ?? '') }}" required>
        @error('name')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $customer->phone ?? '') }}">
        @error('phone')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>
<div class="field">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="{{ old('email', $customer->email ?? '') }}">
    @error('email')<div class="error">{{ $message }}</div>@enderror
</div>
<div class="field">
    <label for="address">Address</label>
    <input type="text" id="address" name="address" value="{{ old('address', $customer->address ?? '') }}">
    @error('address')<div class="error">{{ $message }}</div>@enderror
</div>

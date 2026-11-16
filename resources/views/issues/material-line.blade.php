<div class="material-row queue-card" data-index="{{ $index }}">
    <div class="form-group">
        <label class="form-label" for="material-{{ $index }}">Material *</label>
        <select class="form-control" id="material-{{ $index }}" name="items[{{ $index }}][raw_material_id]" required>
            <option value="">Choose a raw material</option>
            @foreach($materials as $material)
            <option value="{{ $material->id }}" data-unit="{{ $material->unit }}" data-stock="{{ $material->quantity }}" data-cost="{{ $material->cost_price }}" @selected(($line['raw_material_id'] ?? '') == $material->id)>{{ $material->name }} · {{ $material->sku }}</option>
            @endforeach
        </select>
        <p class="form-hint" data-stock></p>
        @error("items.$index.raw_material_id")<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div class="fields">
        <div><label class="form-label" for="quantity-{{ $index }}">Quantity <span data-unit></span> *</label><input class="form-control" id="quantity-{{ $index }}" name="items[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? '' }}" data-quantity type="number" min="0.01" step="0.01" placeholder="0.00" required>@error("items.$index.quantity")<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><span class="form-label">Line value</span><div class="queue-top"><strong data-value>ETB 0.00</strong><button type="button" class="btn btn-secondary btn-sm" data-remove aria-label="Remove ingredient line">Remove</button></div></div>
    </div>
</div>

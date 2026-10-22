{{-- Input form biasa (non-Livewire) lengkap dengan label dan pesan error validasi --}}
@props(['name', 'label', 'type' => 'text'])

<div>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
        {{ $attributes->class(['input', 'input-invalid' => $errors->has($name)]) }}>
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>

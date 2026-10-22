{{-- Pesan session('message') dari komponen Livewire --}}
@props(['type' => 'success'])

@if (session()->has('message'))
    <x-alert :type="$type" {{ $attributes }}>{{ session('message') }}</x-alert>
@endif

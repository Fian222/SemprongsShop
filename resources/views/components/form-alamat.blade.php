{{-- Isian alamat untuk AlamatForm ($form) di komponen Livewire; tombol simpan diisi lewat slot --}}
@props(['form'])

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="alamat-label" class="label">Label</label>
        <input id="alamat-label" type="text" wire:model="form.label" required placeholder="mis. Rumah, Kantor"
            class="input @error('form.label') input-invalid @enderror">
        @error('form.label') <p class="field-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="alamat-penerima" class="label">Nama penerima</label>
        <input id="alamat-penerima" type="text" wire:model="form.penerima" required autocomplete="name"
            class="input @error('form.penerima') input-invalid @enderror">
        @error('form.penerima') <p class="field-error">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="alamat-no-hp" class="label">No. HP</label>
        <input id="alamat-no-hp" type="tel" wire:model="form.no_hp" required autocomplete="tel" placeholder="0812-3456-7890"
            aria-describedby="alamat-no-hp-petunjuk" class="input @error('form.no_hp') input-invalid @enderror">
        @error('form.no_hp')
            <p class="field-error">{{ $message }}</p>
        @else
            <p id="alamat-no-hp-petunjuk" class="hint">Dihubungi kurir saat mengantar.</p>
        @enderror
    </div>
    <div class="sm:col-span-2">
        <p id="label-area" class="label">Area tujuan</p>
        <livewire:pilih-area wire:model="form.area" :key="'area-'.($form->alamat?->id ?? 'baru')" />
        @error('form.area') <p class="field-error">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-2">
        <label for="alamat-lengkap" class="label">Alamat lengkap</label>
        <textarea id="alamat-lengkap" rows="3" wire:model="form.alamat_lengkap" required autocomplete="street-address"
            placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota"
            class="input h-auto py-3 @error('form.alamat_lengkap') input-invalid @enderror"></textarea>
        @error('form.alamat_lengkap') <p class="field-error">{{ $message }}</p> @enderror
    </div>
    <label class="flex items-center gap-2.5 text-[15px] sm:col-span-2">
        <input type="checkbox" wire:model="form.utama" class="size-4 accent-brand"> Jadikan alamat utama
    </label>
</div>

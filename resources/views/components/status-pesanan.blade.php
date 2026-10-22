{{-- Status pesanan: 1 = sudah checkout tapi belum dikonfirmasi admin, 2 = dikonfirmasi lunas --}}
@props(['status'])

@if ($status == 1)
    <span class="badge badge-warn">Belum Lunas</span>
@else
    <span class="badge badge-ok"><x-icon name="check" class="size-3" /> Sudah Lunas</span>
@endif

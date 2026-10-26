{{-- Keadaan kosong: ikon, pesan, dan slot $action opsional untuk langkah berikutnya --}}
@props(['icon' => 'inbox', 'action' => null])

<div class="flex flex-col items-center gap-3 px-6 py-14 text-center">
    <span class="grid size-14 place-items-center rounded-full bg-surface-muted text-ink-muted">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <p class="max-w-sm text-[15px] text-ink-muted">{{ $slot }}</p>
    @if ($action)
        <div class="mt-2">{{ $action }}</div>
    @endif
</div>

@props(['icon' => 'inbox'])

<div class="flex flex-col items-center gap-3 px-6 py-14 text-center text-ink-muted">
    <span class="grid size-14 place-items-center rounded-full bg-surface-muted">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <p class="text-sm">{{ $slot }}</p>
</div>

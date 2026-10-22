<footer class="border-t border-line bg-surface">
    <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 py-8 text-center text-sm text-ink-muted sm:flex-row sm:px-6 sm:text-left">
        <a wire:navigate href="{{ route('home') }}" class="font-display text-lg font-semibold text-ink">
            <x-logo />
        </a>
        <p>&copy; 2022–{{ date('Y') }} Semprong. Dibuat dengan cinta dan mentega.</p>
    </div>
</footer>

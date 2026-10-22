{{-- Override tampilan pagination bawaan Livewire (livewire::tailwind) agar memakai token tema toko --}}
@php
    $scrollTo = $scrollTo ?? 'body';
    $scrollIntoViewJsSnippet = $scrollTo !== false
        ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()"
        : '';
    $pageName = $paginator->getPageName();
    $tombol = 'grid size-10 place-items-center rounded-full border border-line bg-surface text-sm font-semibold text-ink transition hover:border-brand hover:text-brand disabled:opacity-40 disabled:hover:border-line disabled:hover:text-ink';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center gap-3">
            <div class="flex flex-wrap items-center justify-center gap-1.5">
                <button type="button" class="{{ $tombol }}" aria-label="{{ __('pagination.previous') }}"
                    wire:click="previousPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled" @disabled($paginator->onFirstPage())>
                    <x-icon name="arrow-left" />
                </button>

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="grid size-10 place-items-center text-sm text-ink-muted">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $pageName }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page"
                                        class="grid size-10 place-items-center rounded-full bg-brand text-sm font-semibold text-on-brand">{{ $page }}</span>
                                @else
                                    <button type="button" class="{{ $tombol }}" aria-label="Halaman {{ $page }}"
                                        wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}">{{ $page }}</button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                <button type="button" class="{{ $tombol }}" aria-label="{{ __('pagination.next') }}"
                    wire:click="nextPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled" @disabled(! $paginator->hasMorePages())>
                    <x-icon name="arrow-right" />
                </button>
            </div>
            <p class="text-xs text-ink-muted">
                Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }}
            </p>
        </nav>
    @endif
</div>

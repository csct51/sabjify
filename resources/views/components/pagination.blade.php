@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex justify-center">
        <ul class="inline-flex items-center gap-1.5 text-sm">
            @if ($paginator->onFirstPage())
                <li>
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-stone-200 bg-stone-100 text-stone-300 cursor-default" aria-disabled="true">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </span>
                </li>
            @else
                <li>
                    <button type="button" wire:click="previousPage('{{ $pageName }}')" rel="prev" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-stone-200 bg-white text-stone-600 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="inline-flex items-center justify-center px-2 h-9 text-stone-400">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li>
                                <span aria-current="page" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-brand-600 text-white font-semibold shadow-sm">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-stone-200 bg-white text-stone-600 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 transition">{{ $page }}</button>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <button type="button" wire:click="nextPage('{{ $pageName }}')" rel="next" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-stone-200 bg-white text-stone-600 hover:bg-brand-50 hover:text-brand-700 hover:border-brand-300 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </li>
            @else
                <li>
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-stone-200 bg-stone-100 text-stone-300 cursor-default" aria-disabled="true">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif

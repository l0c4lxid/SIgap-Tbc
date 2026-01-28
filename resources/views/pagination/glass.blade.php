@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center gap-2">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 cursor-default shadow-sm text-lg">
                <i class="ri-arrow-left-s-line"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/50 border border-gray-200 text-gray-600 hover:bg-[var(--color-glass-primary)] hover:text-white hover:border-[var(--color-glass-primary)] transition-all shadow-sm hover:shadow text-lg no-underline" aria-label="{{ __('pagination.previous') }}">
                <i class="ri-arrow-left-s-line"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        <div class="hidden md:flex gap-2">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg border border-transparent text-gray-500 cursor-default">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page">
                                <span class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-[var(--color-glass-primary)] border border-[var(--color-glass-primary)] text-white shadow-md cursor-default text-sm font-bold">
                                    {{ $page }}
                                </span>
                            </span>
                        @else
                            <a href="{{ $url }}" class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/50 border border-gray-200 text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-all shadow-sm text-sm font-medium no-underline" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white/50 border border-gray-200 text-gray-600 hover:bg-[var(--color-glass-primary)] hover:text-white hover:border-[var(--color-glass-primary)] transition-all shadow-sm hover:shadow text-lg no-underline" aria-label="{{ __('pagination.next') }}">
                <i class="ri-arrow-right-s-line"></i>
            </a>
        @else
            <span class="relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 border border-gray-200 text-gray-400 cursor-default shadow-sm text-lg">
                <i class="ri-arrow-right-s-line"></i>
            </span>
        @endif
    </nav>
@endif

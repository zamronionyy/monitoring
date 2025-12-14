@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col items-center justify-center sm:flex-row sm:justify-between mt-6">
        
        {{-- =============================================== --}}
        {{-- 1. TAMPILAN MOBILE (Angka Lengkap & Bulat)      --}}
        {{-- =============================================== --}}
        <div class="flex flex-wrap justify-center items-center gap-1 sm:hidden">
            
            {{-- Tombol Previous --}}
            @if ($paginator->onFirstPage())
                <span class="flex items-center justify-center w-8 h-8 text-gray-300 bg-white border border-gray-200 rounded-full cursor-not-allowed">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="flex items-center justify-center w-8 h-8 text-gray-500 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:text-blue-600 transition duration-150">
                    <i class="fas fa-chevron-left text-[10px]"></i>
                </a>
            @endif

            {{-- Loop Angka Halaman --}}
            @foreach ($elements as $element)
                {{-- Separator "..." --}}
                @if (is_string($element))
                    <span class="flex items-center justify-center w-8 h-8 text-gray-400 text-xs font-medium">{{ $element }}</span>
                @endif

                {{-- Array Link Halaman --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            {{-- Aktif --}}
                            <span class="flex items-center justify-center w-8 h-8 text-xs font-bold text-white bg-blue-600 border border-blue-600 rounded-full shadow-md">
                                {{ $page }}
                            </span>
                        @else
                            {{-- Link Biasa --}}
                            <a href="{{ $url }}" class="flex items-center justify-center w-8 h-8 text-xs text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:text-blue-600 transition duration-150">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="flex items-center justify-center w-8 h-8 text-gray-500 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:text-blue-600 transition duration-150">
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            @else
                <span class="flex items-center justify-center w-8 h-8 text-gray-300 bg-white border border-gray-200 rounded-full cursor-not-allowed">
                    <i class="fas fa-chevron-right text-[10px]"></i>
                </span>
            @endif
        </div>

        {{-- =============================================== --}}
        {{-- 2. TAMPILAN DESKTOP (Info Total + Angka)        --}}
        {{-- =============================================== --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between w-full">
            
            {{-- Info Total Data (Kiri) --}}
            <div>
                <p class="text-sm text-gray-500">
                    Menampilkan <span class="font-bold text-gray-800">{{ $paginator->firstItem() }}</span>
                    sampai <span class="font-bold text-gray-800">{{ $paginator->lastItem() }}</span>
                    dari <span class="font-bold text-gray-800">{{ $paginator->total() }}</span> data
                </p>
            </div>

            {{-- Tombol Angka (Kanan) --}}
            <div>
                <ul class="flex items-center gap-1">
                    
                    {{-- Previous --}}
                    @if ($paginator->onFirstPage())
                        <li>
                            <span class="flex items-center justify-center w-9 h-9 text-gray-300 bg-white border border-gray-200 rounded-full cursor-not-allowed">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $paginator->previousPageUrl() }}" class="flex items-center justify-center w-9 h-9 text-gray-500 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 transition duration-150">
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <li>
                                <span class="flex items-center justify-center w-9 h-9 text-gray-400 bg-white border border-gray-100 rounded-full cursor-default">{{ $element }}</span>
                            </li>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li>
                                        <span class="flex items-center justify-center w-9 h-9 text-sm font-bold text-white bg-blue-600 border border-blue-600 rounded-full shadow-md transform scale-105">
                                            {{ $page }}
                                        </span>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ $url }}" class="flex items-center justify-center w-9 h-9 text-sm text-gray-600 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 transition duration-150">
                                            {{ $page }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($paginator->hasMorePages())
                        <li>
                            <a href="{{ $paginator->nextPageUrl() }}" class="flex items-center justify-center w-9 h-9 text-gray-500 bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:text-blue-600 hover:border-blue-300 transition duration-150">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </li>
                    @else
                        <li>
                            <span class="flex items-center justify-center w-9 h-9 text-gray-300 bg-white border border-gray-200 rounded-full cursor-not-allowed">
                                <i class="fas fa-chevron-right text-xs"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
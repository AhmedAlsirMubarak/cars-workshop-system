@if($paginator->hasPages())
<nav class="flex items-center gap-1 justify-center mt-6">
    @foreach($paginator->links()->offsetGet('elements') ?? [] as $element)
        @if(is_string($element))
            <span class="px-3 py-1.5 text-sm text-gray-300">{{ $element }}</span>
        @endif
        @if(is_array($element))
            @foreach($element as $page => $url)
                @if($page == $paginator->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-sm bg-[#FEE103] text-black font-medium">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach
</nav>
@endif

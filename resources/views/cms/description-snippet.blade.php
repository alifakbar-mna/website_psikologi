@if($doc->description)
    <div class="description-snippet">
        <p class="description-text text-xs text-slate-500 line-clamp-3 whitespace-pre-wrap break-words">{{ $doc->description }}</p>

        <button
            type="button"
            data-description="{{ $doc->description }}"
            class="description-more hidden mt-2 text-xs text-sage-700 font-bold hover:underline"
        >
            Detail deskripsi
        </button>
    </div>
@endif
@php($photoStyle = $style ?? '')

@if($motor->photo_url)
    <div class="motor-photo" style="{{ $photoStyle }}">
        <img src="{{ asset(ltrim($motor->photo_url, '/')) }}" alt="{{ $motor->label() }}" loading="lazy">
    </div>
    @if($motor->photo_credit)
        <p class="photo-credit">
            @if($motor->photo_source_url)
                <a href="{{ $motor->photo_source_url }}" rel="nofollow noopener" target="_blank">{{ $motor->photo_credit }}</a>
            @else
                {{ $motor->photo_credit }}
            @endif
        </p>
    @endif
@else
    <div class="photo-placeholder" style="{{ $photoStyle }}">Foto {{ $motor->brand }} {{ $motor->model }}</div>
@endif

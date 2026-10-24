{{-- Fotostrook boven de paginatitel: eigen beeld uit de beeldbank, loopt onderaan over in de pagina.
     $photo = bestandsnaam in images/pagina (zonder breedte), $widths = beschikbare breedtes, $caption = bijschrift. --}}
@php($widths = $widths ?? [800, 1400, 2000])
<figure class="page-photo">
    <img src="{{ asset('images/pagina/'.$photo.'-'.$widths[min(1, count($widths) - 1)].'.webp') }}"
         srcset="@foreach($widths as $w){{ asset('images/pagina/'.$photo.'-'.$w.'.webp') }} {{ $w }}w{{ $loop->last ? '' : ', ' }}@endforeach"
         sizes="100vw" alt="" width="2000" height="667" fetchpriority="high">
    @isset($caption)<figcaption class="wrap">{{ $caption }}</figcaption>@endisset
</figure>

{{-- Pengganti message.blade.php bawaan Laravel: wordmark toko di kepala dan footer berbahasa Indonesia --}}
<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">
Semprong
</x-mail::header>
</x-slot:header>

{!! $slot !!}

@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. Semprong &amp; hampers untuk setiap perayaan.
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>

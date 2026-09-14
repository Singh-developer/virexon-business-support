{{-- Standalone DataTables-style "Show X entries" form (use where the page has no GET filter form).
     Preserves current query string (except page) and reloads with the chosen per_page.
     Props: $perPage (int, current value), $options (array, default [20, 40, 100]). --}}
<form method="GET" action="{{ url()->current() }}" style="display:flex;justify-content:flex-end;margin:0 0 10px;">
    @foreach(request()->except(['per_page', 'page']) as $key => $value)
        @if(is_string($value) || is_numeric($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    @include('partials.per-page-fields', ['perPage' => $perPage ?? 20, 'options' => $options ?? [20, 40, 100]])
</form>

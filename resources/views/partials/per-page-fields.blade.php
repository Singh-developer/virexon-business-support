{{-- DataTables-style "Show X entries" control (fields only — place inside an existing GET form).
     Props: $perPage (int, current value), $options (array, default [20, 40, 100]). --}}
@php
    $perPageCurrent = $perPage ?? 20;
    $perPageOptions = $options ?? [20, 40, 100];
@endphp
<label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:#64748b;white-space:nowrap;margin:0;">
    Show
    <select name="per_page" onchange="this.form.submit()" style="border:1px solid #d9e0e8;border-radius:8px;padding:8px 10px;font-size:12px;font-weight:700;color:#1e293b;background:#fff;cursor:pointer;">
        @foreach($perPageOptions as $option)
            <option value="{{ $option }}" @selected((int) $perPageCurrent === (int) $option)>{{ $option }}</option>
        @endforeach
    </select>
    entries
</label>

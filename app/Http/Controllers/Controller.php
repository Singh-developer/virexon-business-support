<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve the "entries per page" value for listing tables.
     * Accepts only the standard options (20 / 40 / 100) à la DataTables.
     */
    protected function perPage(Request $request, int $default = 20): int
    {
        $allowed = [20, 40, 100];
        $value = (int) $request->query('per_page', $default);

        return in_array($value, $allowed, true) ? $value : $default;
    }
}

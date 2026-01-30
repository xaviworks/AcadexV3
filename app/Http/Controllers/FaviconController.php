<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class FaviconController extends BaseController
{
    /**
     * Serve the favicon from the public directory with proper headers.
     */
    public function show(): Response
    {
        $path = public_path('favicon.ico');

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/x-icon',
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }
}

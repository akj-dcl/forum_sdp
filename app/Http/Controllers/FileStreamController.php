<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileStreamController extends Controller
{
    public function show(Request $request)
    {
        $path = $request->query('path');
        
        if (!Storage::disk('google')->exists($path)) {
            abort(404, 'File tidak ditemukan di Google Drive');
        }

        // Cara paling aman membaca file dari Cloud
        $file = Storage::disk('google')->get($path);
        $mimeType = Storage::disk('google')->mimeType($path);

        return response($file, 200)->header('Content-Type', $mimeType);
    }
}
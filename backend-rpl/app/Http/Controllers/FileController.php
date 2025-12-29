<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf|max:2048'
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('uploads', 'public');

        $file = File::create([
            'nama_file' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'tipe' => $uploadedFile->getClientMimeType()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File berhasil diupload',
            'data' => $file
        ]);
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => File::all()
        ]);
    }
}

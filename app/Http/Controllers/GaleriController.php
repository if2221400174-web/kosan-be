<?php

namespace App\Http\Controllers;

use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GaleriController extends Controller
{
    public function index(Request $request)
    {
        $query = Galeri::with('kamar')->orderBy('urutan', 'asc');

        if ($request->has('kamar_id')) {
            $query->where('kamar_id', $request->kamar_id);
        }

        $galeri = $query->get();

        if ($galeri->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found !'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $galeri
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kamar_id'         => 'nullable|exists:kamar,id',
            'judul'            => 'required|string|max:150',
            'foto'             => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'kategori'         => 'required|string|max:50',
            'urutan'           => 'nullable|integer|min:0',
            'status_publikasi' => 'nullable|in:draft,terbit',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $fotoPath = $request->file('foto')->store('galeri', 'public');

        $galeri = Galeri::create([
            'kamar_id'         => $request->kamar_id,
            'judul'            => $request->judul,
            'foto'             => $fotoPath,
            'kategori'         => $request->kategori,
            'urutan'           => $request->urutan ?? 0,
            'status_publikasi' => $request->status_publikasi ?? 'terbit',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource created',
            'data'    => $galeri->load('kamar')
        ], 201);
    }

    public function show(string $id)
    {
        $galeri = Galeri::with('kamar')->find($id);

        if (!$galeri) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $galeri
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $galeri = Galeri::find($id);
        if (!$galeri) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kamar_id'         => 'nullable|exists:kamar,id',
            'judul'            => 'required|string|max:150',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori'         => 'required|string|max:50',
            'urutan'           => 'nullable|integer|min:0',
            'status_publikasi' => 'required|in:draft,terbit',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $data = [
            'kamar_id'         => $request->kamar_id,
            'judul'            => $request->judul,
            'kategori'         => $request->kategori,
            'urutan'           => $request->urutan ?? $galeri->urutan,
            'status_publikasi' => $request->status_publikasi,
        ];

        if ($request->hasFile('foto')) {
            if ($galeri->foto) {
                $oldPath = public_path('storage/' . $galeri->foto);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $data['foto'] = $request->file('foto')->store('galeri', 'public');
        }

        $galeri->update($data);

        return response()->json([
            'success' => true,
            'message' => 'resource updated successfully',
            'data'    => $galeri->load('kamar')
        ], 200);
    }

    public function destroy(string $id)
    {
        $galeri = Galeri::find($id);
        if (!$galeri) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        if ($galeri->foto) {
            $oldPath = public_path('storage/' . $galeri->foto);
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $galeri->delete();

        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully'
        ], 200);
    }
}

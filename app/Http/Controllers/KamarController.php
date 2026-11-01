<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KamarController extends Controller
{
    public function index()
    {
        $kamar = Kamar::with(['fasilitas', 'galeri'])->get();

        if ($kamar->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found !'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $kamar
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_kamar'   => 'required|string|max:20|unique:kamar,nomor_kamar',
            'tipe_kamar'    => 'required|string|max:50',
            'lantai'        => 'nullable|integer|min:1',
            'harga_bulanan' => 'required|numeric|min:0',
            'harga_tahunan' => 'nullable|numeric|min:0',
            'luas_kamar'    => 'nullable|string|max:30',
            'deskripsi'     => 'nullable|string',
            'foto_utama'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status_kamar'  => 'nullable|in:tersedia,terisi,perbaikan,nonaktif',
            'fasilitas_ids' => 'nullable|array',
            'fasilitas_ids.*' => 'exists:fasilitas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_utama')) {
            $fotoPath = $request->file('foto_utama')->store('kamar', 'public');
        }

        $kamar = Kamar::create([
            'nomor_kamar'   => $request->nomor_kamar,
            'tipe_kamar'    => $request->tipe_kamar,
            'lantai'        => $request->lantai ?? 1,
            'harga_bulanan' => $request->harga_bulanan,
            'harga_tahunan' => $request->harga_tahunan,
            'luas_kamar'    => $request->luas_kamar,
            'deskripsi'     => $request->deskripsi,
            'foto_utama'    => $fotoPath,
            'status_kamar'  => $request->status_kamar ?? 'tersedia',
        ]);

        if ($request->has('fasilitas_ids') && is_array($request->fasilitas_ids)) {
            $kamar->fasilitas()->sync($request->fasilitas_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'resource created',
            'data'    => $kamar->load(['fasilitas', 'galeri'])
        ], 201);
    }

    public function show(string $id)
    {
        $kamar = Kamar::with(['fasilitas', 'galeri', 'kontrakSewa.penyewa.user'])->find($id);

        if (!$kamar) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $kamar
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $kamar = Kamar::find($id);
        if (!$kamar) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nomor_kamar'   => 'required|string|max:20|unique:kamar,nomor_kamar,' . $kamar->id,
            'tipe_kamar'    => 'required|string|max:50',
            'lantai'        => 'nullable|integer|min:1',
            'harga_bulanan' => 'required|numeric|min:0',
            'harga_tahunan' => 'nullable|numeric|min:0',
            'luas_kamar'    => 'nullable|string|max:30',
            'deskripsi'     => 'nullable|string',
            'foto_utama'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status_kamar'  => 'required|in:tersedia,terisi,perbaikan,nonaktif',
            'fasilitas_ids' => 'nullable|array',
            'fasilitas_ids.*' => 'exists:fasilitas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $data = [
            'nomor_kamar'   => $request->nomor_kamar,
            'tipe_kamar'    => $request->tipe_kamar,
            'lantai'        => $request->lantai ?? $kamar->lantai,
            'harga_bulanan' => $request->harga_bulanan,
            'harga_tahunan' => $request->harga_tahunan,
            'luas_kamar'    => $request->luas_kamar,
            'deskripsi'     => $request->deskripsi,
            'status_kamar'  => $request->status_kamar,
        ];

        if ($request->hasFile('foto_utama')) {
            if ($kamar->foto_utama) {
                $oldPath = public_path('storage/' . $kamar->foto_utama);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $data['foto_utama'] = $request->file('foto_utama')->store('kamar', 'public');
        }

        $kamar->update($data);

        if ($request->has('fasilitas_ids') && is_array($request->fasilitas_ids)) {
            $kamar->fasilitas()->sync($request->fasilitas_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'resource updated successfully',
            'data'    => $kamar->load(['fasilitas', 'galeri'])
        ], 200);
    }

    public function destroy(string $id)
    {
        $kamar = Kamar::find($id);
        if (!$kamar) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found',
            ], 404);
        }

        if ($kamar->foto_utama) {
            $oldPath = public_path('storage/' . $kamar->foto_utama);
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $kamar->delete();
        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully',
        ], 200);
    }
}

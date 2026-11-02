<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $query = Laporan::with(['penyewa.user', 'kamar', 'petugas']);

        if ($user && $user->role === 'penyewa') {
            $query->where('penyewa_id', $user->penyewa?->id ?? 0);
        }

        $laporan = $query->get();

        if ($laporan->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $laporan,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'penyewa_id' => 'required|exists:penyewa,id',
            'kamar_id'   => 'nullable|exists:kamar,id',
            'judul'      => 'required|string|max:150',
            'deskripsi'  => 'required|string',
            'foto'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        do {
            $kodeLaporan = 'LAP-' . date('Ym') . '-' . rand(100, 999);
            $exists = Laporan::where('kode_laporan', $kodeLaporan)->exists();
        } while ($exists);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('laporan', 'public');
        }

        $laporan = Laporan::create([
            'kode_laporan' => $kodeLaporan,
            'penyewa_id'   => $request->penyewa_id,
            'kamar_id'     => $request->kamar_id,
            'judul'        => $request->judul,
            'deskripsi'    => $request->deskripsi,
            'foto'         => $fotoPath,
            'status'       => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource add successfully!',
            'data'    => $laporan->load(['penyewa.user', 'kamar'])
        ], 201);
    }

    public function show(string $id)
    {
        $laporan = Laporan::with(['penyewa.user', 'kamar', 'petugas'])->find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $laporan,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul'         => 'nullable|string|max:150',
            'deskripsi'     => 'nullable|string',
            'status'        => 'required|in:menunggu,diproses,selesai,ditolak',
            'catatan_admin' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $data = [
            'judul'          => $request->judul ?? $laporan->judul,
            'deskripsi'      => $request->deskripsi ?? $laporan->deskripsi,
            'status'         => $request->status,
            'ditangani_oleh' => auth('api')->id(),
            'catatan_admin'  => $request->catatan_admin,
        ];

        if ($request->status === 'selesai' && !$laporan->tanggal_selesai) {
            $data['tanggal_selesai'] = now();
        }

        $laporan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'resource updated successfully',
            'data'    => $laporan->load(['penyewa.user', 'kamar', 'petugas'])
        ], 200);
    }

    public function destroy(string $id)
    {
        $laporan = Laporan::find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        if ($laporan->foto) {
            $oldPath = public_path('storage/' . $laporan->foto);
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $laporan->delete();

        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully'
        ], 200);
    }
}

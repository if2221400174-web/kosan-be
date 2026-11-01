<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::with('admin')->orderBy('tanggal_publikasi', 'desc')->get();

        if ($pengumuman->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $pengumuman,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul'             => 'required|string|max:150',
            'isi'               => 'required|string',
            'tanggal_publikasi' => 'required|date',
            'tanggal_berakhir'  => 'nullable|date|after_or_equal:tanggal_publikasi',
            'status'            => 'nullable|in:draft,terbit,arsip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $pengumuman = Pengumuman::create([
            'admin_id'          => auth('api')->id(),
            'judul'             => $request->judul,
            'isi'               => $request->isi,
            'tanggal_publikasi' => $request->tanggal_publikasi,
            'tanggal_berakhir'  => $request->tanggal_berakhir,
            'status'            => $request->status ?? 'terbit',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource add successfully!',
            'data'    => $pengumuman->load('admin')
        ], 201);
    }

    public function show(string $id)
    {
        $pengumuman = Pengumuman::with('admin')->find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $pengumuman,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul'             => 'required|string|max:150',
            'isi'               => 'required|string',
            'tanggal_publikasi' => 'required|date',
            'tanggal_berakhir'  => 'nullable|date|after_or_equal:tanggal_publikasi',
            'status'            => 'required|in:draft,terbit,arsip',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $pengumuman->update([
            'judul'             => $request->judul,
            'isi'               => $request->isi,
            'tanggal_publikasi' => $request->tanggal_publikasi,
            'tanggal_berakhir'  => $request->tanggal_berakhir,
            'status'            => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource updated successfully',
            'data'    => $pengumuman->load('admin')
        ], 200);
    }

    public function destroy(string $id)
    {
        $pengumuman = Pengumuman::find($id);

        if (!$pengumuman) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $pengumuman->delete();

        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Penyewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PenyewaController extends Controller
{
    public function index()
    {
        $penyewa = Penyewa::with('user')->get();

        if ($penyewa->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found !'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $penyewa
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'              => 'required|exists:users,id|unique:penyewa,user_id',
            'nik'                  => 'required|string|size:16|unique:penyewa,nik',
            'jenis_kelamin'        => 'required|in:laki-laki,perempuan',
            'alamat_asal'          => 'required|string',
            'pekerjaan'            => 'nullable|string|max:100',
            'nama_kontak_darurat'  => 'nullable|string|max:100',
            'nomor_kontak_darurat' => 'nullable|string|max:20',
            'foto_ktp'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $fotoKtpPath = null;
        if ($request->hasFile('foto_ktp')) {
            $fotoKtpPath = $request->file('foto_ktp')->store('ktp', 'public');
        }

        $penyewa = Penyewa::create([
            'user_id'              => $request->user_id,
            'nik'                  => $request->nik,
            'jenis_kelamin'        => $request->jenis_kelamin,
            'alamat_asal'          => $request->alamat_asal,
            'pekerjaan'            => $request->pekerjaan,
            'nama_kontak_darurat'  => $request->nama_kontak_darurat,
            'nomor_kontak_darurat' => $request->nomor_kontak_darurat,
            'foto_ktp'             => $fotoKtpPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource created',
            'data'    => $penyewa->load('user')
        ], 201);
    }

    public function show(string $id)
    {
        $penyewa = Penyewa::with(['user', 'kontrakSewa.kamar', 'laporan'])->find($id);

        if (!$penyewa) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'get detail resource',
            'data'    => $penyewa
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $penyewa = Penyewa::find($id);
        if (!$penyewa) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nik'                  => 'required|string|size:16|unique:penyewa,nik,' . $penyewa->id,
            'jenis_kelamin'        => 'required|in:laki-laki,perempuan',
            'alamat_asal'          => 'required|string',
            'pekerjaan'            => 'nullable|string|max:100',
            'nama_kontak_darurat'  => 'nullable|string|max:100',
            'nomor_kontak_darurat' => 'nullable|string|max:20',
            'foto_ktp'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $data = [
            'nik'                  => $request->nik,
            'jenis_kelamin'        => $request->jenis_kelamin,
            'alamat_asal'          => $request->alamat_asal,
            'pekerjaan'            => $request->pekerjaan,
            'nama_kontak_darurat'  => $request->nama_kontak_darurat,
            'nomor_kontak_darurat' => $request->nomor_kontak_darurat,
        ];

        if ($request->hasFile('foto_ktp')) {
            if ($penyewa->foto_ktp) {
                $oldPath = public_path('storage/' . $penyewa->foto_ktp);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $data['foto_ktp'] = $request->file('foto_ktp')->store('ktp', 'public');
        }

        $penyewa->update($data);

        return response()->json([
            'success' => true,
            'message' => 'resource updated successfully',
            'data'    => $penyewa->load('user')
        ], 200);
    }

    public function destroy(string $id)
    {
        $penyewa = Penyewa::find($id);
        if (!$penyewa) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found',
            ], 404);
        }

        if ($penyewa->foto_ktp) {
            $oldPath = public_path('storage/' . $penyewa->foto_ktp);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $penyewa->delete();
        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully',
        ], 200);
    }
}

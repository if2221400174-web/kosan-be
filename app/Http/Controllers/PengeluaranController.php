<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PengeluaranController extends Controller
{
    public function index()
    {
        $pengeluaran = Pengeluaran::with('admin')->orderBy('tanggal_pengeluaran', 'desc')->get();

        if ($pengeluaran->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $pengeluaran,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori'            => 'required|string|max:50',
            'nama_pengeluaran'    => 'required|string|max:150',
            'tanggal_pengeluaran' => 'required|date',
            'jumlah'              => 'required|numeric|min:0',
            'keterangan'          => 'nullable|string',
            'bukti_pengeluaran'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        do {
            $kodePengeluaran = 'OUT-' . date('Ym') . '-' . rand(100, 999);
            $exists = Pengeluaran::where('kode_pengeluaran', $kodePengeluaran)->exists();
        } while ($exists);

        $buktiPath = null;
        if ($request->hasFile('bukti_pengeluaran')) {
            $buktiPath = $request->file('bukti_pengeluaran')->store('pengeluaran', 'public');
        }

        $pengeluaran = Pengeluaran::create([
            'admin_id'            => auth('api')->id(),
            'kode_pengeluaran'    => $kodePengeluaran,
            'kategori'            => $request->kategori,
            'nama_pengeluaran'    => $request->nama_pengeluaran,
            'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
            'jumlah'              => $request->jumlah,
            'keterangan'          => $request->keterangan,
            'bukti_pengeluaran'   => $buktiPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource add successfully!',
            'data'    => $pengeluaran->load('admin')
        ], 201);
    }

    public function show(string $id)
    {
        $pengeluaran = Pengeluaran::with('admin')->find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $pengeluaran,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kategori'            => 'required|string|max:50',
            'nama_pengeluaran'    => 'required|string|max:150',
            'tanggal_pengeluaran' => 'required|date',
            'jumlah'              => 'required|numeric|min:0',
            'keterangan'          => 'nullable|string',
            'bukti_pengeluaran'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $data = [
            'kategori'            => $request->kategori,
            'nama_pengeluaran'    => $request->nama_pengeluaran,
            'tanggal_pengeluaran' => $request->tanggal_pengeluaran,
            'jumlah'              => $request->jumlah,
            'keterangan'          => $request->keterangan,
        ];

        if ($request->hasFile('bukti_pengeluaran')) {
            if ($pengeluaran->bukti_pengeluaran) {
                $oldPath = public_path('storage/' . $pengeluaran->bukti_pengeluaran);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $data['bukti_pengeluaran'] = $request->file('bukti_pengeluaran')->store('pengeluaran', 'public');
        }

        $pengeluaran->update($data);

        return response()->json([
            'success' => true,
            'message' => 'resource updated successfully',
            'data'    => $pengeluaran->load('admin')
        ], 200);
    }

    public function destroy(string $id)
    {
        $pengeluaran = Pengeluaran::find($id);

        if (!$pengeluaran) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        if ($pengeluaran->bukti_pengeluaran) {
            $oldPath = public_path('storage/' . $pengeluaran->bukti_pengeluaran);
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $pengeluaran->delete();

        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully'
        ], 200);
    }
}

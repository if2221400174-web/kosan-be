<?php

namespace App\Http\Controllers;

use App\Models\KontrakSewa;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagihanController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $query = Tagihan::with(['kontrakSewa.penyewa.user', 'kontrakSewa.kamar', 'pembayaran']);

        if ($user && $user->role === 'penyewa') {
            $penyewaId = $user->penyewa?->id ?? 0;
            $query->whereHas('kontrakSewa', function ($q) use ($penyewaId) {
                $q->where('penyewa_id', $penyewaId);
            });
        }

        $tagihan = $query->get();

        if ($tagihan->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $tagihan,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kontrak_sewa_id'     => 'required|exists:kontrak_sewa,id',
            'periode_mulai'       => 'required|date',
            'periode_selesai'     => 'required|date|after:periode_mulai',
            'tanggal_terbit'      => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_terbit',
            'nominal_pokok'       => 'nullable|numeric|min:0',
            'denda'               => 'nullable|numeric|min:0',
            'status_tagihan'      => 'nullable|in:belum_bayar,sebagian,lunas,dibatalkan',
            'keterangan'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // Cek anti duplikasi tagihan pada periode yang sama
        $existing = Tagihan::where('kontrak_sewa_id', $request->kontrak_sewa_id)
            ->where('periode_mulai', $request->periode_mulai)
            ->where('periode_selesai', $request->periode_selesai)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan untuk kontrak dan periode ini sudah ada'
            ], 409);
        }

        $kontrak = KontrakSewa::find($request->kontrak_sewa_id);

        do {
            $kodeTagihan = 'INV-' . date('Ym') . '-' . rand(100, 999);
            $exists = Tagihan::where('kode_tagihan', $kodeTagihan)->exists();
        } while ($exists);

        $nominalPokok = $request->nominal_pokok ?? $kontrak->harga_sewa;
        $denda = $request->denda ?? 0;
        $jumlahTagihan = $nominalPokok + $denda;

        $tagihan = Tagihan::create([
            'kode_tagihan'        => $kodeTagihan,
            'kontrak_sewa_id'     => $request->kontrak_sewa_id,
            'periode_mulai'       => $request->periode_mulai,
            'periode_selesai'     => $request->periode_selesai,
            'tanggal_terbit'      => $request->tanggal_terbit,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'nominal_pokok'       => $nominalPokok,
            'denda'               => $denda,
            'jumlah_tagihan'      => $jumlahTagihan,
            'status_tagihan'      => $request->status_tagihan ?? 'belum_bayar',
            'keterangan'          => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource add successfully!',
            'data'    => $tagihan->load(['kontrakSewa.penyewa.user', 'kontrakSewa.kamar'])
        ], 201);
    }

    public function show(string $id)
    {
        $tagihan = Tagihan::with(['kontrakSewa.penyewa.user', 'kontrakSewa.kamar', 'pembayaran.verifikator'])->find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $tagihan,
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $tagihan = Tagihan::find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'periode_mulai'       => 'required|date',
            'periode_selesai'     => 'required|date|after:periode_mulai',
            'tanggal_terbit'      => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date',
            'nominal_pokok'       => 'required|numeric|min:0',
            'denda'               => 'nullable|numeric|min:0',
            'status_tagihan'      => 'required|in:belum_bayar,sebagian,lunas,dibatalkan',
            'keterangan'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $denda = $request->denda ?? 0;
        $jumlahTagihan = $request->nominal_pokok + $denda;

        $tagihan->update([
            'periode_mulai'       => $request->periode_mulai,
            'periode_selesai'     => $request->periode_selesai,
            'tanggal_terbit'      => $request->tanggal_terbit,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'nominal_pokok'       => $request->nominal_pokok,
            'denda'               => $denda,
            'jumlah_tagihan'      => $jumlahTagihan,
            'status_tagihan'      => $request->status_tagihan,
            'keterangan'          => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource update successfully!',
            'data'    => $tagihan
        ], 200);
    }

    public function destroy(string $id)
    {
        $tagihan = Tagihan::find($id);

        if (!$tagihan) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $tagihan->delete();

        return response()->json([
            'success' => true,
            'message' => 'resource delete successfully!'
        ], 200);
    }
}

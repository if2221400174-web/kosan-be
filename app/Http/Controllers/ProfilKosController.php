<?php

namespace App\Http\Controllers;

use App\Models\ProfilKos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfilKosController extends Controller
{
    public function index()
    {
        $profil = ProfilKos::first();

        if (!$profil) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found !'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $profil
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kos'        => 'required|string|max:150',
            'tagline'         => 'nullable|string|max:255',
            'deskripsi'       => 'required|string',
            'logo'            => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'foto_utama'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'alamat'          => 'required|string',
            'google_maps_url' => 'nullable|string',
            'telepon'         => 'required|string|max:20',
            'email'           => 'nullable|email|max:100',
            'whatsapp'        => 'required|string|max:20',
            'info_rekening'   => 'nullable|string',
            'aturan_kos'      => 'nullable|string',
            'jam_malam'       => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $profil = ProfilKos::first();

        $data = [
            'nama_kos'        => $request->nama_kos,
            'slug'            => Str::slug($request->nama_kos),
            'tagline'         => $request->tagline,
            'deskripsi'       => $request->deskripsi,
            'alamat'          => $request->alamat,
            'google_maps_url' => $request->google_maps_url,
            'telepon'         => $request->telepon,
            'email'           => $request->email,
            'whatsapp'        => $request->whatsapp,
            'info_rekening'   => $request->info_rekening,
            'aturan_kos'      => $request->aturan_kos,
            'jam_malam'       => $request->jam_malam,
        ];

        if ($request->hasFile('logo')) {
            if ($profil && $profil->logo) {
                $oldPath = public_path('storage/' . $profil->logo);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $data['logo'] = $request->file('logo')->store('profil_kos', 'public');
        }

        if ($request->hasFile('foto_utama')) {
            if ($profil && $profil->foto_utama) {
                $oldPath = public_path('storage/' . $profil->foto_utama);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $data['foto_utama'] = $request->file('foto_utama')->store('profil_kos', 'public');
        }

        if ($profil) {
            $profil->update($data);
        } else {
            $profil = ProfilKos::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'profil kos saved successfully',
            'data'    => $profil
        ], 200);
    }

    public function show(string $id)
    {
        $profil = ProfilKos::find($id);

        if (!$profil) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $profil
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        return $this->store($request);
    }

    public function destroy(string $id)
    {
        $profil = ProfilKos::find($id);
        if (!$profil) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found',
            ], 404);
        }
        $profil->delete();
        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully',
        ], 200);
    }
}

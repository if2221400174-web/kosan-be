<?php

namespace App\Http\Controllers;

use App\Models\Penyewa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenyewaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $query = Penyewa::with('user');

        // penyewa hanya boleh melihat profil miliknya sendiri
        if ($user && $user->role === 'penyewa') {
            $query->where('user_id', $user->id);
        }

        $penyewa = $query->get();

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
        /** @var \App\Models\User $authUser */
        $authUser = auth('api')->user();
        $isPengelola = in_array($authUser->role, ['administrator', 'admin']);

        // penyewa hanya boleh membuat profil untuk dirinya sendiri
        if (!$isPengelola) {
            $request->merge(['user_id' => $authUser->id]);
        }

        // pengelola boleh membuat akun login penyewa sekaligus (jika user_id tidak dikirim)
        $buatAkunBaru = $isPengelola && !$request->filled('user_id');

        $rules = [
            'nik'                  => 'required|string|size:16|unique:penyewa,nik',
            'jenis_kelamin'        => 'required|in:laki-laki,perempuan',
            'alamat_asal'          => 'required|string',
            'pekerjaan'            => 'nullable|string|max:100',
            'nama_kontak_darurat'  => 'nullable|string|max:100',
            'nomor_kontak_darurat' => 'nullable|string|max:20',
            'foto_ktp'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($buatAkunBaru) {
            $rules['nama']          = 'required|string|max:100';
            $rules['email']         = 'required|email|unique:users,email';
            $rules['password']      = 'required|min:8';
            $rules['nomor_telepon'] = 'required|string|max:20';
        } else {
            $rules['user_id'] = 'required|exists:users,id|unique:penyewa,user_id';
        }

        $validator = Validator::make($request->all(), $rules);

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

        $penyewa = DB::transaction(function () use ($request, $buatAkunBaru, $fotoKtpPath, $authUser) {
            $userId = $request->user_id;

            if ($buatAkunBaru) {
                $user = User::create([
                    'nama'          => $request->nama,
                    'email'         => $request->email,
                    'password'      => bcrypt($request->password),
                    'nomor_telepon' => $request->nomor_telepon,
                    'role'          => 'penyewa',
                    'status_akun'   => 'aktif',
                ]);
                $userId = $user->id;
            } elseif ($authUser) {
                $userUpdates = array_filter([
                    'nama'          => $request->nama,
                    'nomor_telepon' => $request->nomor_telepon,
                ]);
                if (!empty($userUpdates)) {
                    $authUser->update($userUpdates);
                }
            }

            return Penyewa::create([
                'user_id'              => $userId,
                'nik'                  => $request->nik,
                'jenis_kelamin'        => $request->jenis_kelamin,
                'alamat_asal'          => $request->alamat_asal,
                'pekerjaan'            => $request->pekerjaan,
                'nama_kontak_darurat'  => $request->nama_kontak_darurat,
                'nomor_kontak_darurat' => $request->nomor_kontak_darurat,
                'foto_ktp'             => $fotoKtpPath,
            ]);
        });

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

        /** @var \App\Models\User $authUser */
        $authUser = auth('api')->user();
        if ($authUser->role === 'penyewa' && $penyewa->user_id !== $authUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nik'                  => 'required|string|size:16|unique:penyewa,nik,' . $penyewa->id,
            'jenis_kelamin'        => 'required|in:laki-laki,perempuan',
            'alamat_asal'          => 'required|string',
            'pekerjaan'            => 'nullable|string|max:100',
            'nama_kontak_darurat'  => 'nullable|string|max:100',
            'nomor_kontak_darurat' => 'nullable|string|max:20',
            'foto_ktp'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // data akun (opsional)
            'nama'                 => 'nullable|string|max:100',
            'email'                => 'nullable|email|unique:users,email,' . $penyewa->user_id,
            'nomor_telepon'        => 'nullable|string|max:20',
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

        DB::transaction(function () use ($request, $penyewa, $data) {
            $penyewa->update($data);

            $dataUser = array_filter([
                'nama'          => $request->nama,
                'email'         => $request->email,
                'nomor_telepon' => $request->nomor_telepon,
            ]);

            if (!empty($dataUser) && $penyewa->user) {
                $penyewa->user->update($dataUser);
            }
        });

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

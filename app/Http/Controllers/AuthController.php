<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        // 1. setup validator
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:8',
            'nomor_telepon' => 'required|string|max:20',
            'role'          => 'nullable|in:administrator,admin,penyewa',
            'status_akun'   => 'nullable|in:aktif,nonaktif',
        ]);

        // 2. cek validator
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // 3. create user
        $user = User::create([
            'nama'          => $request->nama,
            'email'         => $request->email,
            'password'      => bcrypt($request->password),
            'nomor_telepon' => $request->nomor_telepon,
            'role'          => $request->role ?? 'penyewa',
            'status_akun'   => $request->status_akun ?? 'aktif',
        ]);

        // 4. cek keberhasilan
        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'user created successfully',
                'data'    => $user
            ], 201);
        }

        // 5. cek gagal
        return response()->json([
            'success' => false,
            'message' => 'user creation failed'
        ], 500);
    }

    public function login(Request $request)
    {
        // 1. setup validator
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // 2. cek validator
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // 3. get kredensial dari request
        $credentials = $request->only('email', 'password');

        // 4. cek isFailed
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password anda salah !'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = auth()->guard('api')->user();

        if ($user->status_akun === 'nonaktif') {
            JWTAuth::invalidate($token);
            return response()->json([
                'success' => false,
                'message' => 'Akun anda sedang dinonaktifkan !'
            ], 403);
        }

        // 5. cek is Success
        return response()->json([
            'success' => true,
            'message' => 'Login successfully',
            'user'    => $user->load('penyewa'),
            'token'   => $token,
        ], 200);
    }

    // lupa password
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => $token,
                'created_at' => now(),
                'expired_at' => now()->addMinutes(60)
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token reset dibuat',
            'token'   => $token
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required',
            'password' => 'required|min:8'
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid'
            ], 400);
        }

        if (now()->gt($record->expired_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Token kadaluarsa'
            ], 400);
        }

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset'
        ]);
    }

    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama salah'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah'
        ]);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout successfully'
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed'
            ], 500);
        }
    }

    // CRUD
    public function index()
    {
        $user = User::with('penyewa')->get();

        if ($user->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $user
        ], 200);
    }

    // show
    public function show(string $id)
    {
        $user = User::with('penyewa')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $user
        ], 200);
    }

    // update
    public function update(Request $request, string $id)
    {
        // 1. Cari data
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        // 2. Validator
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'nomor_telepon' => 'required|string|max:20',
            'role'          => 'required|in:administrator,admin,penyewa',
            'status_akun'   => 'required|in:aktif,nonaktif',
            'password'      => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // 3. Siapkan data
        $data = [
            'nama'          => $request->nama,
            'email'         => $request->email,
            'nomor_telepon' => $request->nomor_telepon,
            'role'          => $request->role,
            'status_akun'   => $request->status_akun,
        ];

        // 4. Update password jika dikirim
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        // 5. Update & return
        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'resource updated',
            'data'    => $user
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // VALIDASI
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'nomor_telepon' => 'required|string|max:20',
            'password'      => 'nullable|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        // DATA DASAR
        $user->nama = $request->nama;
        $user->email = $request->email;
        $user->nomor_telepon = $request->nomor_telepon;

        // PASSWORD (optional)
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diupdate',
            'data'    => $user->load('penyewa')
        ]);
    }

    // delete
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'resource deleted',
            'data'    => $user
        ], 200);
    }
}

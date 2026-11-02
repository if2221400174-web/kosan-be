<?php

namespace App\Http\Controllers;

use App\Models\PesanChat;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('pesan_chat')) {
            Schema::create('pesan_chat', function (Blueprint $table) {
                $table->id();
                $table->foreignId('penyewa_user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
                $table->text('isi_pesan');
                $table->boolean('dibaca')->default(false);
                $table->timestamps();
            });
        }
    }

    public function index(Request $request)
    {
        $this->ensureTableExists();

        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        if ($user->role === 'penyewa') {
            // Tandai pesan dari admin sebagai sudah dibaca oleh penyewa
            PesanChat::where('penyewa_user_id', $user->id)
                ->where('pengirim_id', '!=', $user->id)
                ->where('dibaca', false)
                ->update(['dibaca' => true]);

            $messages = PesanChat::with(['pengirim:id,nama,role'])
                ->where('penyewa_user_id', $user->id)
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success'  => true,
                'messages' => $messages,
            ]);
        }

        // Untuk Admin & Administrator: daftar seluruh calon penyewa / penyewa beserta ringkasan chat
        $penyewaUsers = User::with('penyewa')
            ->where('role', 'penyewa')
            ->orderBy('nama')
            ->get();

        $threads = $penyewaUsers->map(function ($u) {
            $lastMsg = PesanChat::where('penyewa_user_id', $u->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $unreadCount = PesanChat::where('penyewa_user_id', $u->id)
                ->where('pengirim_id', $u->id)
                ->where('dibaca', false)
                ->count();

            return [
                'user_id'       => $u->id,
                'nama'          => $u->nama,
                'email'         => $u->email,
                'nomor_telepon' => $u->nomor_telepon,
                'last_message'  => $lastMsg ? $lastMsg->isi_pesan : null,
                'last_time'     => $lastMsg ? $lastMsg->created_at : $u->created_at,
                'unread_count'  => $unreadCount,
            ];
        })->sortByDesc('last_time')->values();

        $selectedUserId = $request->query('penyewa_user_id') ?: ($threads->first()['user_id'] ?? null);
        $messages = [];

        if ($selectedUserId) {
            // Tandai pesan dari penyewa tersebut sebagai sudah dibaca oleh admin
            PesanChat::where('penyewa_user_id', $selectedUserId)
                ->where('pengirim_id', $selectedUserId)
                ->where('dibaca', false)
                ->update(['dibaca' => true]);

            $messages = PesanChat::with(['pengirim:id,nama,role'])
                ->where('penyewa_user_id', $selectedUserId)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return response()->json([
            'success'          => true,
            'threads'          => $threads,
            'selected_user_id' => $selectedUserId ? (int) $selectedUserId : null,
            'messages'         => $messages,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureTableExists();

        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        $penyewaUserId = $user->role === 'penyewa'
            ? $user->id
            : $request->input('penyewa_user_id');

        $validator = Validator::make([
            'penyewa_user_id' => $penyewaUserId,
            'isi_pesan'       => $request->input('isi_pesan'),
        ], [
            'penyewa_user_id' => 'required|exists:users,id',
            'isi_pesan'       => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $chat = PesanChat::create([
            'penyewa_user_id' => $penyewaUserId,
            'pengirim_id'     => $user->id,
            'isi_pesan'       => trim($request->input('isi_pesan')),
            'dibaca'          => false,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $chat->load(['pengirim:id,nama,role']),
        ], 201);
    }
}

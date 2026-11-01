<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FasilitasController extends Controller
{
    public function index()
    {
        $fasilitas = Fasilitas::all();

        if ($fasilitas->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found !'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $fasilitas
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_fasilitas'  => 'required|string|max:100',
            'jenis_fasilitas' => 'required|in:kamar,umum',
            'ikon'            => 'nullable|string|max:100',
            'deskripsi'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $fasilitas = Fasilitas::create([
            'nama_fasilitas'  => $request->nama_fasilitas,
            'jenis_fasilitas' => $request->jenis_fasilitas,
            'ikon'            => $request->ikon,
            'deskripsi'       => $request->deskripsi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource created',
            'data'    => $fasilitas
        ], 201);
    }

    public function show(string $id)
    {
        $fasilitas = Fasilitas::with('kamar')->find($id);

        if (!$fasilitas) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $fasilitas
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $fasilitas = Fasilitas::find($id);
        if (!$fasilitas) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_fasilitas'  => 'required|string|max:100',
            'jenis_fasilitas' => 'required|in:kamar,umum',
            'ikon'            => 'nullable|string|max:100',
            'deskripsi'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $fasilitas->update([
            'nama_fasilitas'  => $request->nama_fasilitas,
            'jenis_fasilitas' => $request->jenis_fasilitas,
            'ikon'            => $request->ikon,
            'deskripsi'       => $request->deskripsi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource updated successfully',
            'data'    => $fasilitas
        ], 200);
    }

    public function destroy(string $id)
    {
        $fasilitas = Fasilitas::find($id);
        if (!$fasilitas) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found',
            ], 404);
        }

        $fasilitas->delete();
        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully',
        ], 200);
    }
}

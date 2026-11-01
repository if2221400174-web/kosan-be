<?php

namespace App\Http\Controllers;

use App\Models\KamarFasilitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KamarFasilitasController extends Controller
{
    public function index()
    {
        $data = KamarFasilitas::with(['kamar', 'fasilitas'])->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'resource data not found !'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get all resource',
            'data'    => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kamar_id'     => 'required|exists:kamar,id',
            'fasilitas_id' => 'required|exists:fasilitas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 422);
        }

        $exists = KamarFasilitas::where('kamar_id', $request->kamar_id)
            ->where('fasilitas_id', $request->fasilitas_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Fasilitas ini sudah terdaftar pada kamar tersebut'
            ], 409);
        }

        $item = KamarFasilitas::create([
            'kamar_id'     => $request->kamar_id,
            'fasilitas_id' => $request->fasilitas_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'resource created',
            'data'    => $item->load(['kamar', 'fasilitas'])
        ], 201);
    }

    public function show(string $id)
    {
        $item = KamarFasilitas::with(['kamar', 'fasilitas'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Get resource',
            'data'    => $item
        ], 200);
    }

    public function destroy(string $id)
    {
        $item = KamarFasilitas::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'resource not found'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'resource deleted successfully'
        ], 200);
    }
}

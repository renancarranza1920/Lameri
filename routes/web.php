<?php



use App\Models\DetalleOrden;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::post('/ordenes/ordenar', function (Request $request) {
    foreach ($request->ids as $index => $id) {
        DetalleOrden::where('id', $id)
            ->where('orden_id', $request->orden_id)
            ->update(['orden_en_recipiente' => $index]);
    }

    return response()->json(['status' => 'ok']);
});
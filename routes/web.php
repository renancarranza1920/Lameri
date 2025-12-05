<?php



use App\Http\Controllers\ZplController;
use App\Models\DetalleOrden;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
Route::get('/', function () {
    return view('welcome');
});
*/
Route::get('/detalles/zpl/{id}', [ZplController::class, 'single'])->name('detalles.zpl');

Route::get('/grupo/{status}/zpl/{ordenId}', [ZplController::class, 'group'])
    ->name('grupo.zpl');


Route::post('/ordenes/ordenar', function (Request $request) {
    foreach ($request->ids as $index => $id) {
        DetalleOrden::where('id', $id)
            ->where('orden_id', $request->orden_id)
            ->update(['orden_en_recipiente' => $index]);
    }

    return response()->json(['status' => 'ok']);
})->middleware('auth');

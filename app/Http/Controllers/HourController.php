<?php

namespace App\Http\Controllers;

use App\Models\Hour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HourController extends Controller
{
    /**
     * Devuelve las franjas horarias del doctor autenticado
     */
    public function index()
    {
        $doctorId = Auth::id() ?: 2; // Cambiar por Auth::id()
        return Hour::where('user_id', $doctorId)
                   ->orderBy('day')
                   ->get();
    }

    /**
     * Crear, actualiza o elimina (soft-delete) las franjas horarias enviadas desde el front
     */
    public function storeOrUpdate(Request $request)
    {
        $doctorId = Auth::id() ?: 2;

        // 1) Obtenemos el array de franjas horarias del request
        $slots = $request->input('availabilities', []);

        // 2) Reglas dinamicas de validación
        $validDays = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
        $rules = ['availabilities' => 'required|array'];
        foreach ($slots as $i => $slot) {
            $rules["availabilities.$i.day"]        = ['required','string',"in:".implode(',', $validDays)];
            $rules["availabilities.$i.start_time"] = 'required|date_format:H:i';
            $rules["availabilities.$i.end_time"]   = "required|date_format:H:i|after:availabilities.$i.start_time";
            $rules["availabilities.$i.value"]      = 'required|integer|min:0|max:1000000';
            $rules["availabilities.$i.eliminate"]  = 'sometimes|boolean';
        }

        // 3) Validar
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Errores de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $slots = $validator->validated()['availabilities'];

        // 4) Procesar cada franja
        foreach ($slots as $slot) {
            // Buscar registro existente
            $hour = Hour::where('user_id', $doctorId)
                        ->where('day', $slot['day'])
                        ->where('start_time', $slot['start_time'])
                        ->where('end_time', $slot['end_time'])
                        ->first();

            if (!empty($slot['eliminate'])) {
                // Soft delete si existe
                if ($hour) {
                    $hour->delete();
                }
                continue;
            }

            // Crear o actualizar
            if ($hour) {
                $hour->value = $slot['value'];
                $hour->save();
            } else {
                Hour::create([
                    'user_id'    => $doctorId,
                    'day'        => $slot['day'],
                    'start_time' => $slot['start_time'],
                    'end_time'   => $slot['end_time'],
                    'value'      => $slot['value'],
                ]);
            }
        }

        // 5) Respuesta de éxito
        return response()->json([
            'status'  => 'success',
            'message' => 'Disponibilidad actualizada con éxito.',
        ], 200);
    }
}

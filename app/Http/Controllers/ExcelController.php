<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Hour;

use Illuminate\Http\Request;

class ExcelController extends Controller
{
    public function import(Request $request)
    {
        $data = $request->all();

        if (!is_array($data) || empty($data)) {
            return response()->json([
                'message' => 'No se recibieron datos válidos',
                'status' => 'error'
            ], 422);
        }
        foreach ($data as $row) {
            if (!isset($row['Nombre']) || !isset($row['Especialidad']) || !isset($row['RUT']) || !isset($row['Valor Consulta']) || !isset($row['Horario de Atención'])) {
                return response()->json([
                    'message' => 'Faltan columnas obligatorias en alguna fila',
                    'status' => 'error'
                ], 422);
            }
            if (isset($row['Horario de Atención']) && is_string($row['Horario de Atención'])) {
                $horarios_raw = preg_split('/\r\n|\r|\n|,/', $row['Horario de Atención']);
                $horarios = [];

                foreach ($horarios_raw as $linea) {
                    $linea = trim($linea);
                    if (!$linea) continue;

                    if (preg_match('/^(\w+):\s*(\d{1,2}:\d{2})\s*a\s*(\d{1,2}:\d{2})/', $linea, $matches)) {
                        $horarios[] = [
                            'day'         => $matches[1],
                            'start_time' => $matches[2],
                            'end_time'    => $matches[3],
                        ];
                    }

                }

            }
            // registro de usuarios y por cada uno el registro de horas
            if (!User::where('rut', $row['RUT'])->exists()) {
                $user = User::create([
                    'name' => $row['Nombre'],
                    'area' => $row['Especialidad'],
                    'rut'=> $row['RUT'],
                    'password' => $row['RUT']
                ]);

                foreach ($horarios as $horario) {
                    Hour::create([
                        'user_id' => $user->id,
                        'day' => $horario['day'],
                        'start_time' => $horario['start_time'],
                        'end_time' => $horario['end_time'],
                        'value' => $row['Valor Consulta'],

                    ]);
                }

            }else {
                $user = User::where('rut', $row['RUT'])->first();
                // Si el usuario ya existe, añademos los horarios, sin embargo tenemos que verificar si ya existe el horario
                foreach ($horarios as $horario) {
                    Hour::create([
                        'user_id' => $user->id,
                        'day' => $horario['day'],
                        'start_time' => $horario['start_time'],
                        'end_time' => $horario['end_time'],
                        'value' => $row['Valor Consulta'],

                    ]);
                }
            }

        }

        return response()->json([
            'message' => 'Datos recibidos correctamente',
            'total' => count($data),
            'status' => 'success',
            'pd'=> 'ok',
        ], 200);
    }
}

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

        // Validar que se reciban datos
        if (!is_array($data) || empty($data)) {
            return response()->json([
                'message' => 'No se recibieron datos válidos',
                'status'  => 'error',
            ], 422);
        }

        foreach ($data as $row) {
            // Validar columnas obligatorias
            if (!isset(
                $row['nombre'],
                $row['especialidad'],
                $row['rut'],
                $row['valorConsulta'],
                $row['horarioDeAtencion']
            )) {
                return response()->json([
                    'message' => 'Faltan columnas obligatorias en alguna fila',
                    'status'  => 'error',
                ], 422);
            }

            // Limpiar y convertir valorConsulta: eliminar símbolos y convertir a entero
            $rawValue   = (string) $row['valorConsulta'];
            $cleanValue = preg_replace('/[^0-9]/', '', $rawValue);

            if ($cleanValue === '' || !ctype_digit($cleanValue)) {
                return response()->json([
                    'message' => "Valor de consulta inválido: {$row['valorConsulta']}",
                    'status'  => 'error',
                ], 422);
            }

            $valorConsulta = (int) $cleanValue;

            // Procesar horarioDeAtencion
            $horarios = [];
            if (is_string($row['horarioDeAtencion'])) {
                $horarios_raw = preg_split('/\r\n|\r|\n|,/', $row['horarioDeAtencion']);

                foreach ($horarios_raw as $linea) {
                    $linea = trim($linea);
                    if ($linea === '') {
                        continue;
                    }

                    // Formato esperado: Día: H(:)MM a H(:)MM
                    if (preg_match('/^(\w+):\s*(\d{1,2}:\d{2})\s*a\s*(\d{1,2}:\d{2})/i', $linea, $matches)) {
                        // Normalizar formato 00:00
                        $start = date('H:i', strtotime($matches[2]));
                        $end   = date('H:i', strtotime($matches[3]));

                        $horarios[] = [
                            'day'        => $matches[1],
                            'start_time' => $start,
                            'end_time'   => $end,
                        ];
                    }
                }
            }

            // Crear o actualizar usuario
            $user = User::firstOrCreate(
                ['rut' => $row['rut']],
                [
                    'name'     => $row['nombre'],
                    'area'     => $row['especialidad'],
                    'password' => bcrypt($row['rut']),
                ]
            );

            // Registrar franjas horarias
            foreach ($horarios as $horario) {
                Hour::create([
                    'user_id'    => $user->id,
                    'day'        => $horario['day'],
                    'start_time' => $horario['start_time'],
                    'end_time'   => $horario['end_time'],
                    'value'      => $valorConsulta,
                ]);
            }
        }

        // Respuesta final
        return response()->json([
            'message' => 'Datos recibidos correctamente',
            'total'   => count($data),
            'status'  => 'success',
        ], 200);
    }
}

<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsuariosImport;

class ExcelController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        dd($request->file('file'));
        Excel::import(new UsuariosImport, $request->file('file'));

        return response()->json(['message' => 'Archivo importado con éxito']);
    }
}

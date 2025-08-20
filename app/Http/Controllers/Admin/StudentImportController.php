<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentImportController extends Controller
{
    /**
     * Import CSV d’étudiants
     * CSV attendu : student_number,first_name,last_name,birth_date,email,phone,class_id
     */
    public function import(Request $request)
    {
        // Vérifier que l’utilisateur est admin pédagogique
        $admin = $this->checkPedagogicalPermissions();
        if (!$admin) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Vérifier qu’un fichier a bien été envoyé
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle, 0, ','); // lire la 1ère ligne (en-têtes)

        $expected = ['student_number','first_name','last_name','birth_date','email','phone','class_id'];

        // Vérif structure CSV
        if ($header !== $expected) {
            return response()->json([
                'message' => 'Colonnes CSV invalides',
                'expected' => $expected,
                'received' => $header
            ], 422);
        }

        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $data = array_combine($header, $row);

            // Validation rapide
            $validator = validator($data, [
                'student_number' => 'required|unique:students,student_number',
                'first_name'     => 'required|string|max:255',
                'last_name'      => 'required|string|max:255',
                'birth_date'     => 'required|date',
                'email'          => 'required|email|unique:students,email',
                'phone'          => 'nullable|string|max:20',
                'class_id'       => 'required|exists:classes,id',
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $data,
                    'errors' => $validator->errors()->all()
                ];
                continue;
            }

            // Vérifier que la classe appartient à l’institution
            $classe = Classes::find($data['class_id']);
            if ($classe->formation->institution_id !== $admin->institution_id) {
                $errors[] = [
                    'row' => $data,
                    'errors' => ['Classe non autorisée pour cette institution.']
                ];
                continue;
            }

            $data['institution_id'] = $admin->institution_id;

            try {
                Student::create($data);
                $imported++;
            } catch (\Exception $e) {
                Log::error('CSV import error', ['row' => $data, 'error' => $e->getMessage()]);
                $errors[] = [
                    'row' => $data,
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        fclose($handle);

        return response()->json([
            'message' => "Import terminé",
            'imported' => $imported,
            'errors'   => $errors
        ]);
    }

    private function checkPedagogicalPermissions()
    {
        $user = auth()->user();
        return Administrator::where('user_id', $user->id)
            ->where('type','pedagogique')
            ->first();
    }
}

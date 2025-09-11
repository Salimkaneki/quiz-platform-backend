<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StudentImportController extends Controller
{
    /**
     * Import CSV d'étudiants
     */
    public function import(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est admin pédagogique
            $admin = $this->checkPedagogicalPermissions();
            if (!$admin) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }

            // Vérifier qu'un fichier a bien été envoyé
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:2048'
            ]);

            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), 'r');

            // Détecter automatiquement le délimiteur
            $firstLine = fgets($handle);
            $delimiter = $this->detectDelimiter($firstLine);
            
            Log::info('Délimiteur détecté: ' . $delimiter);
            Log::info('Première ligne: ' . $firstLine);
            
            // Retourner au début du fichier
            fseek($handle, 0);
            
            // Lire les en-têtes avec le bon délimiteur
            $header = fgetcsv($handle, 0, $delimiter);
            
            if ($header === false) {
                fclose($handle);
                return response()->json(['message' => 'Erreur de lecture du fichier CSV'], 422);
            }
            
            // Nettoyer les en-têtes
            $header = array_map('trim', $header);
            $header = array_map('strtolower', $header);

            $expected = ['student_number', 'first_name', 'last_name', 'birth_date', 'email', 'phone', 'class_id'];

            Log::info('En-têtes détectés: ', $header);
            Log::info('En-têtes attendus: ', $expected);

            // Vérifier la structure CSV
            if (count($header) !== count($expected)) {
                fclose($handle);
                return response()->json([
                    'message' => 'Nombre de colonnes incorrect',
                    'expected' => count($expected),
                    'received' => count($header),
                    'headers_received' => $header
                ], 422);
            }

            if (array_diff($expected, $header)) {
                fclose($handle);
                return response()->json([
                    'message' => 'Colonnes CSV invalides',
                    'expected' => $expected,
                    'received' => $header,
                    'missing' => array_diff($expected, $header),
                    'extra' => array_diff($header, $expected)
                ], 422);
            }

            $imported = 0;
            $errors = [];
            $lineNumber = 1;

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $lineNumber++;
                
                // Ignorer les lignes vides
                if ($row === null || count($row) === 1 && empty(trim($row[0]))) {
                    continue;
                }

                // Vérifier que le nombre de colonnes correspond
                if (count($row) !== count($header)) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'errors' => ["Nombre de colonnes incorrect. Attendu: " . count($header) . ", Reçu: " . count($row)],
                        'data' => $row
                    ];
                    continue;
                }

                $data = array_combine($header, $row);
                
                // Nettoyer les données
                foreach ($data as $key => $value) {
                    $data[$key] = trim($value);
                }

                // Validation des données
                $validator = Validator::make($data, [
                    'student_number' => 'required|unique:students,student_number',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'birth_date' => 'required|date|date_format:Y-m-d',
                    'email' => 'required|email|unique:students,email',
                    'phone' => 'required|string|max:20',
                    'class_id' => 'required|exists:classes,id',
                ]);

                if ($validator->fails()) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'errors' => $validator->errors()->all(),
                        'data' => $data
                    ];
                    continue;
                }

                // Vérifier que la classe appartient à l'institution
                $classe = Classes::find($data['class_id']);
                if (!$classe) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'errors' => ['Classe non trouvée.'],
                        'data' => $data
                    ];
                    continue;
                }

                if ($classe->formation->institution_id !== $admin->institution_id) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'errors' => ['Classe non autorisée pour cette institution.'],
                        'data' => $data
                    ];
                    continue;
                }

                try {
                    Student::create([
                        'student_number' => $data['student_number'],
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'birth_date' => $data['birth_date'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                        'class_id' => $data['class_id'],
                        'institution_id' => $admin->institution_id,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    Log::error('Erreur création étudiant', ['data' => $data, 'error' => $e->getMessage()]);
                    $errors[] = [
                        'line' => $lineNumber,
                        'errors' => [$e->getMessage()],
                        'data' => $data
                    ];
                }
            }

            fclose($handle);

            return response()->json([
                'message' => "Import terminé",
                'imported' => $imported,
                'errors' => $errors,
                'total_lines' => $lineNumber - 1
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur import étudiants', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'message' => 'Erreur interne du serveur',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Une erreur est survenue lors de l\'importation'
            ], 500);
        }
    }

    /**
     * Détecter le délimiteur du CSV - VERSION CORRIGÉE
     */
    private function detectDelimiter($line)
    {
        $delimiters = [',', ';', "\t", '|'];
        $counts = [];
        
        foreach ($delimiters as $delimiter) {
            $count = count(str_getcsv($line, $delimiter));
            $counts[$delimiter] = $count;
        }
        
        // Trouver le délimiteur avec le plus grand nombre de colonnes
        $maxCount = max($counts);
        $possibleDelimiters = array_keys($counts, $maxCount);
        
        // Retourner le premier délimiteur avec le compte maximum
        // Préférer le point-virgule s'il y a égalité
        if (in_array(';', $possibleDelimiters)) {
            return ';';
        }
        
        return $possibleDelimiters[0] ?? ',';
    }

    private function checkPedagogicalPermissions()
    {
        $user = auth()->user();
        return Administrator::where('user_id', $user->id)
            ->where('type', 'pedagogique')
            ->first();
    }
}
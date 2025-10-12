<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Teacher;
use App\Models\Subject;
use Carbon\Carbon;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $quizzes = [
            // Quiz Informatique - Salim Pereira
            [
                'title' => 'Algorithmique et Programmation - QCM',
                'description' => 'Quiz de révision sur les bases de l\'algorithmique et de la programmation structurée',
                'teacher_email' => 'salimpereira01@gmail.com',
                'subject_code' => 'ALGO-INFO-UL-101',
                'duration_minutes' => 45,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Quel est le rôle principal d\'un algorithme ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Stocker des données', 'is_correct' => false],
                            ['text' => 'Décrire une séquence d\'opérations pour résoudre un problème', 'is_correct' => true],
                            ['text' => 'Créer des interfaces graphiques', 'is_correct' => false],
                            ['text' => 'Gérer les erreurs système', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 1,
                        'explanation' => 'Un algorithme est une séquence finie et non ambiguë d\'opérations permettant de résoudre un problème.',
                    ],
                    [
                        'question_text' => 'Quelle structure de contrôle permet de répéter une séquence d\'instructions tant qu\'une condition est vraie ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'if-else', 'is_correct' => false],
                            ['text' => 'switch-case', 'is_correct' => false],
                            ['text' => 'while', 'is_correct' => true],
                            ['text' => 'for', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 2,
                        'explanation' => 'La boucle while répète les instructions tant que la condition est vraie.',
                    ],
                    [
                        'question_text' => 'En programmation, une variable est un espace mémoire qui peut contenir une valeur.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 3,
                        'explanation' => 'Une variable est effectivement un espace mémoire nommé qui peut stocker une valeur.',
                    ],
                ],
            ],
            [
                'title' => 'Bases de Données - Exercices Pratiques',
                'description' => 'Quiz sur les concepts fondamentaux des bases de données relationnelles',
                'teacher_email' => 'salimpereira01@gmail.com',
                'subject_code' => 'BDD-INFO-UL-202',
                'duration_minutes' => 60,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Quelle commande SQL permet de récupérer des données d\'une table ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'INSERT', 'is_correct' => false],
                            ['text' => 'UPDATE', 'is_correct' => false],
                            ['text' => 'SELECT', 'is_correct' => true],
                            ['text' => 'DELETE', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 1,
                        'explanation' => 'SELECT est la commande SQL utilisée pour interroger et récupérer des données.',
                    ],
                    [
                        'question_text' => 'Une clé primaire peut être composée de plusieurs colonnes.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 2,
                        'explanation' => 'Une clé primaire composite est possible et fréquente dans les bases de données.',
                    ],
                    [
                        'question_text' => 'Complétez : La ______ assure l\'intégrité et la cohérence des données dans une base de données.',
                        'type' => 'fill_blank',
                        'correct_answer' => 'normalisation',
                        'points' => 3,
                        'order' => 3,
                        'explanation' => 'La normalisation est le processus qui organise les données pour éviter la redondance.',
                    ],
                ],
            ],

            // Quiz Mathématiques - Marie Kouassi
            [
                'title' => 'Mathématiques Discrètes - Graphes',
                'description' => 'Évaluation sur les concepts fondamentaux des graphes en mathématiques discrètes',
                'teacher_email' => 'marie.kouassi@ul.edu.tg',
                'subject_code' => 'MATH-DISC-INFO-UL-102',
                'duration_minutes' => 50,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Dans un graphe non orienté, le degré d\'un sommet est le nombre d\'arêtes incidentes à ce sommet.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 1,
                        'explanation' => 'Le degré d\'un sommet est effectivement défini comme le nombre d\'arêtes qui lui sont connectées.',
                    ],
                    [
                        'question_text' => 'Quel algorithme permet de trouver le plus court chemin dans un graphe pondéré ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Algorithme de Prim', 'is_correct' => false],
                            ['text' => 'Algorithme de Dijkstra', 'is_correct' => true],
                            ['text' => 'Algorithme de Kruskal', 'is_correct' => false],
                            ['text' => 'Tri topologique', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 2,
                        'explanation' => 'L\'algorithme de Dijkstra trouve le plus court chemin entre deux nœuds dans un graphe pondéré.',
                    ],
                ],
            ],

            // Quiz Médecine - Aminata Sow
            [
                'title' => 'Anatomie Humaine - Système Circulatoire',
                'description' => 'Quiz sur l\'anatomie du système circulatoire humain',
                'teacher_email' => 'aminata.sow@ul.edu.tg',
                'subject_code' => 'ANAT-MED-UL-101',
                'duration_minutes' => 40,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Quel organe pompe le sang dans le corps humain ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Le foie', 'is_correct' => false],
                            ['text' => 'Les poumons', 'is_correct' => false],
                            ['text' => 'Le cœur', 'is_correct' => true],
                            ['text' => 'Les reins', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 1,
                        'explanation' => 'Le cœur est l\'organe musculaire qui pompe le sang dans tout le corps.',
                    ],
                    [
                        'question_text' => 'Le sang veineux contient plus d\'oxygène que le sang artériel.',
                        'type' => 'true_false',
                        'correct_answer' => 'false',
                        'points' => 1,
                        'order' => 2,
                        'explanation' => 'C\'est l\'inverse : le sang artériel est riche en oxygène, le sang veineux en est pauvre.',
                    ],
                ],
            ],

            // Quiz Développement Web - Paul Konate
            [
                'title' => 'HTML/CSS - Bases du Web',
                'description' => 'Évaluation des connaissances en HTML et CSS pour le développement web',
                'teacher_email' => 'paul.konate@ism.tg',
                'subject_code' => 'HTML-WEB-ISM-101',
                'duration_minutes' => 35,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Quelle balise HTML est utilisée pour créer un lien hypertexte ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => '<link>', 'is_correct' => false],
                            ['text' => '<a>', 'is_correct' => true],
                            ['text' => '<href>', 'is_correct' => false],
                            ['text' => '<url>', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 1,
                        'explanation' => 'La balise <a> (anchor) est utilisée pour créer des liens hypertextes.',
                    ],
                    [
                        'question_text' => 'En CSS, la propriété ______ permet de changer la couleur d\'arrière-plan d\'un élément.',
                        'type' => 'fill_blank',
                        'correct_answer' => 'background-color',
                        'points' => 2,
                        'order' => 2,
                        'explanation' => 'background-color est la propriété CSS pour définir la couleur d\'arrière-plan.',
                    ],
                ],
            ],

            // Quiz Comptabilité - Michel Camara
            [
                'title' => 'Comptabilité Générale - Bilan',
                'description' => 'Quiz sur les principes comptables et l\'établissement du bilan',
                'teacher_email' => 'michel.camara@ism.tg',
                'subject_code' => 'COMPTA-GEN-ISM-101',
                'duration_minutes' => 55,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Le bilan comptable présente la situation financière de l\'entreprise à un moment donné.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 1,
                        'explanation' => 'Le bilan est un document comptable qui présente l\'actif et le passif à une date donnée.',
                    ],
                    [
                        'question_text' => 'Quelle équation fondamentale lie l\'actif, le passif et les capitaux propres ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Actif = Passif + Capitaux propres', 'is_correct' => false],
                            ['text' => 'Actif = Passif - Capitaux propres', 'is_correct' => false],
                            ['text' => 'Actif = Passif + Capitaux propres', 'is_correct' => true],
                            ['text' => 'Passif = Actif + Capitaux propres', 'is_correct' => false],
                        ],
                        'points' => 3,
                        'order' => 2,
                        'explanation' => 'L\'équation comptable fondamentale est : Actif = Passif + Capitaux propres.',
                    ],
                ],
            ],

            // Quiz Génie Civil - Ahmed Bamba
            [
                'title' => 'Résistance des Matériaux - Contraintes',
                'description' => 'Évaluation sur les concepts de contraintes et déformations dans les matériaux',
                'teacher_email' => 'ahmed.bamba@ul.edu.tg',
                'subject_code' => 'RESIST-GC-UL-101',
                'duration_minutes' => 45,
                'status' => 'draft',
                'questions' => [
                    [
                        'question_text' => 'La contrainte normale est définie comme le rapport de la force normale sur la section.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 1,
                        'explanation' => 'La contrainte normale σ = F/A où F est la force normale et A la section.',
                    ],
                ],
            ],
        ];

        $createdQuizzes = 0;
        $createdQuestions = 0;

        foreach ($quizzes as $quizData) {
            $teacher = Teacher::whereHas('user', function($query) use ($quizData) {
                $query->where('email', $quizData['teacher_email']);
            })->first();

            if (!$teacher) {
                $this->command->error("Professeur {$quizData['teacher_email']} non trouvé");
                continue;
            }

            $subject = Subject::where('code', $quizData['subject_code'])->first();

            if (!$subject) {
                $this->command->error("Matière {$quizData['subject_code']} non trouvée");
                continue;
            }

            $quiz = Quiz::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'title' => $quizData['title'],
                    'subject_id' => $subject->id,
                ],
                [
                    'description' => $quizData['description'],
                    'duration_minutes' => $quizData['duration_minutes'],
                    'total_points' => array_sum(array_column($quizData['questions'], 'points')),
                    'shuffle_questions' => true,
                    'show_results_immediately' => false,
                    'allow_review' => true,
                    'status' => $quizData['status'],
                    'settings' => [
                        'max_attempts' => 1,
                        'shuffle_answers' => true,
                        'show_explanation' => true,
                    ],
                ]
            );

            // Supprimer les questions existantes et en créer de nouvelles
            $quiz->questions()->delete();

            foreach ($quizData['questions'] as $questionData) {
                $question = new Question([
                    'question_text' => $questionData['question_text'],
                    'type' => $questionData['type'],
                    'points' => $questionData['points'],
                    'order' => $questionData['order'],
                    'explanation' => $questionData['explanation'] ?? null,
                ]);

                if ($questionData['type'] === 'multiple_choice') {
                    $question->options = $questionData['options'];
                } elseif ($questionData['type'] === 'true_false') {
                    $question->correct_answer = $questionData['correct_answer'];
                } elseif ($questionData['type'] === 'fill_blank') {
                    $question->correct_answer = $questionData['correct_answer'];
                }

                $quiz->questions()->save($question);
                $createdQuestions++;
            }

            $createdQuizzes++;
        }

        $this->command->info('Quiz créés avec succès!');
        $this->command->info('Total: ' . $createdQuizzes . ' quiz');
        $this->command->info('Total questions: ' . $createdQuestions);
    }
}
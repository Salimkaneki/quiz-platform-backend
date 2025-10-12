<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Teacher;
use App\Models\Subject;
use Carbon\Carbon;

class EsgisQuizSeeder extends Seeder
{
    public function run(): void
    {
        $quizzes = [
            // Quiz de Salim Pereira - Algorithmique et Programmation
            [
                'title' => 'Algorithmique et Programmation - Concepts Fondamentaux',
                'description' => 'Évaluation des connaissances de base en algorithmique et programmation structurée',
                'teacher_email' => 'salim.pereira@esgis.tg',
                'subject_code' => 'ALGO-INFO-ESGIS-101',
                'duration_minutes' => 45,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Quel est le rôle principal d\'un algorithme ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Stocker des données en mémoire', 'is_correct' => false],
                            ['text' => 'Décrire une séquence d\'opérations pour résoudre un problème', 'is_correct' => true],
                            ['text' => 'Créer des interfaces graphiques utilisateur', 'is_correct' => false],
                            ['text' => 'Gérer les erreurs système d\'un ordinateur', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 1,
                        'explanation' => 'Un algorithme est une séquence finie et non ambiguë d\'opérations permettant de résoudre un problème donné.',
                    ],
                    [
                        'question_text' => 'Quelle structure de contrôle permet de répéter une séquence d\'instructions tant qu\'une condition est vraie ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'if-else (conditionnel)', 'is_correct' => false],
                            ['text' => 'switch-case (sélection multiple)', 'is_correct' => false],
                            ['text' => 'while (boucle conditionnelle)', 'is_correct' => true],
                            ['text' => 'for (boucle itérative)', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 2,
                        'explanation' => 'La boucle while répète les instructions contenues dans son corps tant que la condition spécifiée reste vraie.',
                    ],
                    [
                        'question_text' => 'En programmation, une variable est un espace mémoire qui peut contenir une valeur modifiable.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 3,
                        'explanation' => 'Une variable est effectivement un espace mémoire nommé qui peut stocker une valeur qui peut être modifiée au cours de l\'exécution du programme.',
                    ],
                    [
                        'question_text' => 'Quel est l\'ordre correct des phases de développement d\'un programme ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Codage → Analyse → Test → Déploiement', 'is_correct' => false],
                            ['text' => 'Analyse → Codage → Test → Déploiement', 'is_correct' => true],
                            ['text' => 'Test → Analyse → Codage → Déploiement', 'is_correct' => false],
                            ['text' => 'Déploiement → Test → Codage → Analyse', 'is_correct' => false],
                        ],
                        'points' => 3,
                        'order' => 4,
                        'explanation' => 'Le développement logiciel suit généralement l\'ordre : analyse des besoins, codage, tests, puis déploiement.',
                    ],
                    [
                        'question_text' => 'Complétez : Un ______ est un diagramme qui représente graphiquement un algorithme.',
                        'type' => 'fill_blank',
                        'correct_answer' => 'organigramme',
                        'points' => 2,
                        'order' => 5,
                        'explanation' => 'Un organigramme (ou flowchart) est une représentation graphique des étapes d\'un algorithme.',
                    ],
                ],
            ],

            // Quiz de Salim Pereira - Bases de Données
            [
                'title' => 'Bases de Données - Modèle Relationnel',
                'description' => 'Évaluation sur les concepts fondamentaux du modèle relationnel et SQL',
                'teacher_email' => 'salim.pereira@esgis.tg',
                'subject_code' => 'BDD-INFO-ESGIS-202',
                'duration_minutes' => 60,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Quelle commande SQL permet de récupérer des données d\'une table ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'INSERT INTO', 'is_correct' => false],
                            ['text' => 'UPDATE', 'is_correct' => false],
                            ['text' => 'SELECT', 'is_correct' => true],
                            ['text' => 'DELETE FROM', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 1,
                        'explanation' => 'SELECT est la commande SQL utilisée pour interroger et récupérer des données depuis une ou plusieurs tables.',
                    ],
                    [
                        'question_text' => 'Une clé primaire peut être composée de plusieurs colonnes dans une table.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 2,
                        'explanation' => 'Une clé primaire composite est possible et fréquente dans les bases de données relationnelles.',
                    ],
                    [
                        'question_text' => 'Quel type de relation existe entre deux tables lorsqu\'une ligne de la première table peut être liée à plusieurs lignes de la deuxième table ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Relation un-à-un (1:1)', 'is_correct' => false],
                            ['text' => 'Relation un-à-plusieurs (1:N)', 'is_correct' => true],
                            ['text' => 'Relation plusieurs-à-plusieurs (N:N)', 'is_correct' => false],
                            ['text' => 'Relation zéro-à-un (0:1)', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 3,
                        'explanation' => 'Dans une relation un-à-plusieurs, un enregistrement d\'une table peut être lié à plusieurs enregistrements d\'une autre table.',
                    ],
                    [
                        'question_text' => 'Complétez : La ______ est le processus qui organise les données pour éviter la redondance et assurer la cohérence.',
                        'type' => 'fill_blank',
                        'correct_answer' => 'normalisation',
                        'points' => 3,
                        'order' => 4,
                        'explanation' => 'La normalisation est le processus qui organise les données en tables liées pour éviter la redondance.',
                    ],
                    [
                        'question_text' => 'Quelle commande SQL permet de modifier des données existantes dans une table ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'INSERT INTO', 'is_correct' => false],
                            ['text' => 'SELECT', 'is_correct' => false],
                            ['text' => 'UPDATE', 'is_correct' => true],
                            ['text' => 'CREATE TABLE', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 5,
                        'explanation' => 'La commande UPDATE permet de modifier les valeurs des colonnes dans les lignes existantes d\'une table.',
                    ],
                ],
            ],

            // Quiz de Sophie Ouattara - Mathématiques Discrètes
            [
                'title' => 'Mathématiques Discrètes - Théorie des Graphes',
                'description' => 'Évaluation sur les concepts fondamentaux des graphes en mathématiques discrètes',
                'teacher_email' => 'sophie.ouattara@esgis.tg',
                'subject_code' => 'MATH-DISC-INFO-ESGIS-102',
                'duration_minutes' => 50,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Dans un graphe non orienté, le degré d\'un sommet est le nombre d\'arêtes qui lui sont incidentes.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 1,
                        'explanation' => 'Le degré d\'un sommet est effectivement défini comme le nombre d\'arêtes connectées à ce sommet.',
                    ],
                    [
                        'question_text' => 'Quel algorithme permet de trouver le plus court chemin dans un graphe pondéré avec des poids positifs ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Algorithme de Prim (arbre couvrant minimum)', 'is_correct' => false],
                            ['text' => 'Algorithme de Dijkstra', 'is_correct' => true],
                            ['text' => 'Algorithme de Kruskal (arbre couvrant minimum)', 'is_correct' => false],
                            ['text' => 'Tri topologique', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 2,
                        'explanation' => 'L\'algorithme de Dijkstra trouve le plus court chemin entre deux nœuds dans un graphe pondéré avec des poids positifs.',
                    ],
                    [
                        'question_text' => 'Un graphe connexe est un graphe dans lequel il existe un chemin entre chaque paire de sommets.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 3,
                        'explanation' => 'Un graphe connexe est défini comme un graphe où il existe au moins un chemin entre chaque paire de sommets.',
                    ],
                    [
                        'question_text' => 'Complétez : Un ______ est un graphe dans lequel les arêtes ont une direction définie.',
                        'type' => 'fill_blank',
                        'correct_answer' => 'graphe orienté',
                        'points' => 2,
                        'order' => 4,
                        'explanation' => 'Un graphe orienté (ou digraphe) est un graphe où chaque arête a une direction spécifique.',
                    ],
                    [
                        'question_text' => 'Quelle est la somme des degrés de tous les sommets dans un graphe non orienté ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'égale au nombre de sommets', 'is_correct' => false],
                            ['text' => 'égale au nombre d\'arêtes multiplié par 2', 'is_correct' => true],
                            ['text' => 'égale au nombre d\'arêtes', 'is_correct' => false],
                            ['text' => 'variable selon le graphe', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 5,
                        'explanation' => 'Dans tout graphe non orienté, la somme des degrés de tous les sommets est toujours égale à deux fois le nombre d\'arêtes.',
                    ],
                ],
            ],

            // Quiz de Sophie Ouattara - Développement Web
            [
                'title' => 'Développement Web - HTML/CSS/JavaScript',
                'description' => 'Évaluation des connaissances en développement web frontend',
                'teacher_email' => 'sophie.ouattara@esgis.tg',
                'subject_code' => 'WEB-INFO-ESGIS-401',
                'duration_minutes' => 55,
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
                        'explanation' => 'La balise <a> (anchor) est utilisée pour créer des liens hypertextes vers d\'autres pages ou ressources.',
                    ],
                    [
                        'question_text' => 'En CSS, quelle propriété permet de changer la couleur d\'arrière-plan d\'un élément ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'color', 'is_correct' => false],
                            ['text' => 'background-color', 'is_correct' => true],
                            ['text' => 'border-color', 'is_correct' => false],
                            ['text' => 'text-color', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 2,
                        'explanation' => 'La propriété background-color définit la couleur d\'arrière-plan d\'un élément HTML.',
                    ],
                    [
                        'question_text' => 'En JavaScript, quelle méthode permet d\'accéder à un élément HTML par son ID ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'getElementByClass()', 'is_correct' => false],
                            ['text' => 'getElementById()', 'is_correct' => true],
                            ['text' => 'querySelector()', 'is_correct' => false],
                            ['text' => 'getElementsByTagName()', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 3,
                        'explanation' => 'La méthode getElementById() permet d\'accéder à un élément HTML unique par son attribut id.',
                    ],
                    [
                        'question_text' => 'Complétez : Le ______ est un modèle de boîte qui décrit l\'espace occupé par un élément HTML.',
                        'type' => 'fill_blank',
                        'correct_answer' => 'box model',
                        'points' => 2,
                        'order' => 4,
                        'explanation' => 'Le box model CSS décrit les propriétés de dimensionnement et d\'espacement d\'un élément HTML.',
                    ],
                    [
                        'question_text' => 'Quelle pseudo-classe CSS permet de sélectionner un élément au survol de la souris ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => ':active', 'is_correct' => false],
                            ['text' => ':hover', 'is_correct' => true],
                            ['text' => ':focus', 'is_correct' => false],
                            ['text' => ':visited', 'is_correct' => false],
                        ],
                        'points' => 1,
                        'order' => 5,
                        'explanation' => 'La pseudo-classe :hover s\'applique lorsqu\'un élément est survolé par le pointeur de la souris.',
                    ],
                ],
            ],

            // Quiz de Salim Pereira - Programmation Orientée Objet
            [
                'title' => 'Programmation Orientée Objet - Concepts Avancés',
                'description' => 'Évaluation sur les principes avancés de la POO',
                'teacher_email' => 'salim.pereira@esgis.tg',
                'subject_code' => 'POO-INFO-ESGIS-301',
                'duration_minutes' => 50,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Quel principe de la POO permet à une classe d\'hériter des propriétés et méthodes d\'une autre classe ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'L\'encapsulation', 'is_correct' => false],
                            ['text' => 'Le polymorphisme', 'is_correct' => false],
                            ['text' => 'L\'héritage', 'is_correct' => true],
                            ['text' => 'L\'abstraction', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 1,
                        'explanation' => 'L\'héritage permet à une classe (sous-classe) d\'hériter des propriétés et méthodes d\'une autre classe (super-classe).',
                    ],
                    [
                        'question_text' => 'L\'encapsulation permet de cacher les détails d\'implémentation d\'une classe.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 2,
                        'explanation' => 'L\'encapsulation permet effectivement de masquer les détails d\'implémentation et de protéger les données.',
                    ],
                    [
                        'question_text' => 'Complétez : Un ______ est une classe qui ne peut pas être instanciée directement.',
                        'type' => 'fill_blank',
                        'correct_answer' => 'classe abstraite',
                        'points' => 2,
                        'order' => 3,
                        'explanation' => 'Une classe abstraite ne peut pas être instanciée et sert de modèle pour d\'autres classes.',
                    ],
                    [
                        'question_text' => 'Quel mécanisme permet à un objet d\'avoir plusieurs formes selon le contexte ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'L\'encapsulation', 'is_correct' => false],
                            ['text' => 'L\'héritage', 'is_correct' => false],
                            ['text' => 'Le polymorphisme', 'is_correct' => true],
                            ['text' => 'L\'abstraction', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 4,
                        'explanation' => 'Le polymorphisme permet à un objet de prendre différentes formes selon le contexte d\'utilisation.',
                    ],
                ],
            ],

            // Quiz de Sophie Ouattara - Intelligence Artificielle
            [
                'title' => 'Intelligence Artificielle - Apprentissage Automatique',
                'description' => 'Évaluation sur les concepts fondamentaux de l\'IA et du machine learning',
                'teacher_email' => 'sophie.ouattara@esgis.tg',
                'subject_code' => 'IA-INFO-ESGIS-402',
                'duration_minutes' => 60,
                'status' => 'published',
                'questions' => [
                    [
                        'question_text' => 'Quel type d\'apprentissage automatique utilise des données étiquetées pour entraîner un modèle ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'Apprentissage supervisé', 'is_correct' => true],
                            ['text' => 'Apprentissage non supervisé', 'is_correct' => false],
                            ['text' => 'Apprentissage par renforcement', 'is_correct' => false],
                            ['text' => 'Apprentissage profond', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 1,
                        'explanation' => 'L\'apprentissage supervisé utilise des données d\'entraînement étiquetées pour apprendre à prédire des sorties.',
                    ],
                    [
                        'question_text' => 'Un réseau de neurones artificiels est inspiré du fonctionnement du cerveau humain.',
                        'type' => 'true_false',
                        'correct_answer' => 'true',
                        'points' => 1,
                        'order' => 2,
                        'explanation' => 'Les réseaux de neurones artificiels sont effectivement inspirés de la structure et du fonctionnement des neurones biologiques.',
                    ],
                    [
                        'question_text' => 'Complétez : Le ______ est une technique qui permet à un modèle d\'apprendre à partir de ses propres erreurs.',
                        'type' => 'fill_blank',
                        'correct_answer' => 'réseau de neurones',
                        'points' => 2,
                        'order' => 3,
                        'explanation' => 'Les réseaux de neurones apprennent en ajustant leurs poids en fonction des erreurs commises.',
                    ],
                    [
                        'question_text' => 'Quel algorithme est couramment utilisé pour la classification binaire ?',
                        'type' => 'multiple_choice',
                        'options' => [
                            ['text' => 'K-means', 'is_correct' => false],
                            ['text' => 'Régression linéaire', 'is_correct' => false],
                            ['text' => 'Régression logistique', 'is_correct' => true],
                            ['text' => 'PCA (Analyse en Composantes Principales)', 'is_correct' => false],
                        ],
                        'points' => 2,
                        'order' => 4,
                        'explanation' => 'La régression logistique est largement utilisée pour les problèmes de classification binaire.',
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
                        'time_limit_enforced' => true,
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

        $this->command->info('Quiz ESGIS créés avec succès!');
        $this->command->info('Total: ' . $createdQuizzes . ' quiz');
        $this->command->info('Total questions: ' . $createdQuestions);
    }
}
<?php

return [
    /*
    | Mot de passe attribué aux comptes étudiants créés par un administrateur
    | (création unitaire ou import CSV). À surcharger via STUDENT_DEFAULT_PASSWORD
    | plutôt que de le laisser en dur dans le code.
    */
    'default_password' => env('STUDENT_DEFAULT_PASSWORD', 'Motdepasse123'),
];

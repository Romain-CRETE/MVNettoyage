<?php
// send_form.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Accès direct interdit.');
}

// Répertoire de stockage
$uploads_dir = __DIR__ . '/uploads/';
if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

// Configuration
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_file_size = 2 * 1024 * 1024; // 2 Mo
$max_files = 5;

// Résultats
$uploaded_files = [];
$errors = [];

// Vérification du nombre de fichiers
if (!empty($_FILES['photos']['name'][0])) {
    $names = array_filter($_FILES['photos']['name']);
    if (count($names) > $max_files) {
        $errors[] = "Vous ne pouvez uploader que $max_files fichiers maximum.";
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        foreach ($_FILES['photos']['name'] as $key => $original_name) {
            if (empty($original_name)) continue;

            $tmp_name = $_FILES['photos']['tmp_name'][$key];
            $filesize = $_FILES['photos']['size'][$key];

            // Vérifier erreur d'upload
            if ($_FILES['photos']['error'][$key] !== UPLOAD_ERR_OK) {
                $errors[] = "$original_name : erreur d'upload.";
                continue;
            }

            // Vérifier taille
            if ($filesize > $max_file_size) {
                $errors[] = "$original_name : dépasse la taille maximale autorisée (2 Mo).";
                continue;
            }

            // Vérifier extension
            $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowed_extensions, true)) {
                $errors[] = "$original_name : extension non autorisée.";
                continue;
            }

            // Vérifier type MIME réel
            $mime_type = finfo_file($finfo, $tmp_name);
            if (!in_array($mime_type, $allowed_mime_types, true)) {
                $errors[] = "$original_name : type de fichier invalide.";
                continue;
            }

            // Vérification getimagesize
            if (@getimagesize($tmp_name) === false) {
                $errors[] = "$original_name : n'est pas une image valide.";
                continue;
            }

            // Nom unique sécurisé
            $safe_name = uniqid('img_', true) . '.' . $extension;
            $destination = $uploads_dir . $safe_name;

            // Déplacer le fichier
            if (!move_uploaded_file($tmp_name, $destination)) {
                $errors[] = "$original_name : échec de l'upload.";
                continue;
            }

            @chmod($destination, 0644);
            $uploaded_files[] = $safe_name;
        }

        finfo_close($finfo);
    }
}

// Fonction sécurisée pour afficher
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Affichage des résultats
echo "<h2>Formulaire reçu !</h2>";

if (!empty($_POST)) {
    echo "<h3>Données :</h3><pre>" . h(print_r($_POST, true)) . "</pre>";
}

if (!empty($errors)) {
    echo "<h3>Erreurs :</h3><ul>";
    foreach ($errors as $err) {
        echo "<li>" . h($err) . "</li>";
    }
    echo "</ul>";
}

if (!empty($uploaded_files)) {
    echo "<h3>Fichiers uploadés :</h3><ul>";
    foreach ($uploaded_files as $file) {
        echo "<li>" . h($file) . "</li>";
    }
    echo "</ul>";
    echo "<p>Simulation de l’envoi d’email réussie !</p>";
} else {
    if (empty($errors)) {
        echo "<p>Aucun fichier n'a été uploadé.</p>";
    }
}
?>


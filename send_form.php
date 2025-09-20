<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Créer un dossier uploads si il n'existe pas
    $uploads_dir = 'uploads/';
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }

    // Traiter les fichiers envoyés
    if (!empty($_FILES['photos']['name'][0])) {
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            $name = basename($_FILES['photos']['name'][$key]);
            move_uploaded_file($tmp_name, $uploads_dir . $name);
        }
    }

    // Message de confirmation
    echo "Formulaire reçu avec succès ! Les fichiers sont dans le dossier uploads.";
}
?>

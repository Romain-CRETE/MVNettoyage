<?php
// send_form.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Accès direct interdit.');
}

// ---------------------------
// Répertoire de stockage des fichiers
// ---------------------------
$uploads_dir = __DIR__ . '/uploads/';
if (!is_dir($uploads_dir)) mkdir($uploads_dir, 0755, true);

// ---------------------------
// Configuration sécurité upload
// ---------------------------
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_file_size = 2 * 1024 * 1024; // 2 Mo
$max_files = 5;

// ---------------------------
// Initialisation
// ---------------------------
$uploaded_files = [];
$errors = [];

// ---------------------------
// Vérification et upload des fichiers
// ---------------------------
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

            if ($_FILES['photos']['error'][$key] !== UPLOAD_ERR_OK) {
                $errors[] = "$original_name : erreur d'upload.";
                continue;
            }

            if ($filesize > $max_file_size) {
                $errors[] = "$original_name : dépasse la taille maximale autorisée (2 Mo).";
                continue;
            }

            $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowed_extensions, true)) {
                $errors[] = "$original_name : extension non autorisée.";
                continue;
            }

            $mime_type = finfo_file($finfo, $tmp_name);
            if (!in_array($mime_type, $allowed_mime_types, true)) {
                $errors[] = "$original_name : type de fichier invalide.";
                continue;
            }

            if (@getimagesize($tmp_name) === false) {
                $errors[] = "$original_name : n'est pas une image valide.";
                continue;
            }

            $safe_name = uniqid('img_', true) . '.' . $extension;
            $destination = $uploads_dir . $safe_name;

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

// ---------------------------
// Fonction sécurisée pour affichage
// ---------------------------
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ---------------------------
// Préparer l'email
// ---------------------------
$to = "mvnettoyage2site@outlook.fr, mv.nettoyage2@gmail.com";
$subject = "Formulaire de contact MVNettoyage";

$message = "Nom : " . ($_POST['nom'] ?? '') . "\n";
$message .= "Prénom : " . ($_POST['prenom'] ?? '') . "\n";
$message .= "Type de client : " . ($_POST['type-client'] ?? '') . "\n";
$message .= "Email : " . ($_POST['email'] ?? '') . "\n";
$message .= "Téléphone : " . ($_POST['tel'] ?? '') . "\n";
$message .= "Message : " . ($_POST['message'] ?? '') . "\n";

if (!empty($uploaded_files)) {
    $message .= "Fichiers uploadés (cliquer sur le lien pour télécharger) :\n";
    foreach ($uploaded_files as $file) {
        $url = "https://mvnettoyage.com/uploads/" . $file;
        $message .= " - $url\n";
    }
}

// ---------------------------
// Headers pour réduire le spam
// ---------------------------
$headers = "From: contact@mvnettoyage.com\r\n";
$headers .= "Reply-To: " . ($_POST['email'] ?? 'contact@mvnettoyage.com') . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// ---------------------------
// Envoi de l'email
// ---------------------------
$mail_success = mail($to, $subject, $message, $headers);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Formulaire</title>
<script>
function showPopup(message) {
    alert(message);
    window.location.href = "/"; // redirige vers l'accueil après fermeture
}
</script>
</head>
<body>
<?php
if (!empty($errors)) {
    echo "<script>showPopup('Erreur : ".h(implode(", ", $errors))."');</script>";
} elseif ($mail_success) {
    echo "<script>showPopup('Envoi du formulaire confirmé !');</script>";
} else {
    echo "<script>showPopup('Une erreur inconnue est survenue.');</script>";
}
?>
</body>
</html>

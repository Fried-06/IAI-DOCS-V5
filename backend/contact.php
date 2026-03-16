<?php
// backend/contact.php - Skeleton for Contact form

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $message = htmlspecialchars($_POST['message'] ?? '');

    if (!$email) {
        die("Erreur: Adresse email invalide.");
    }

    // TODO: Insert into database or send email via mail() or PHPMailer
    
    // Simulate success
    echo "<script>alert('Votre message a été envoyé avec succès !'); window.location.href='../contact.html';</script>";
}
?>

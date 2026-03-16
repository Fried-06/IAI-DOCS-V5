<?php
// backend/upload.php - Skeleton for document upload

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $level = $_POST['level'] ?? '';
    $category = $_POST['category'] ?? '';
    
    // Check if file was uploaded
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $fileName = $_FILES['document']['name'];
        $fileTmpName  = $_FILES['document']['tmp_name'];
        
        // TODO: Validate file type (pdf, docx, zip) and size
        // TODO: Move file to uploads directory: move_uploaded_file(...)
        // TODO: Insert record into database with status = pending
        
        // Simulate success
        echo "<script>alert('Document uploadé avec succès. En attente de validation.'); window.location.href='../profile.html';</script>";
    } else {
        die("Erreur lors de l'upload du fichier.");
    }
}
?>

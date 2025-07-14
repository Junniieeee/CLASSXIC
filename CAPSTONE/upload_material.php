<?php
include "myconnector.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $uploaded_by = $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; // or however you store the name
    $uploaded_by_id = $_SESSION['user_id'];
    $upload_date = date('Y-m-d H:i:s');
    $is_approved = 1; // Default to approved
    $approved_by = 'admin'; // Default for now
    $approved_at = $upload_date;

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_name = $_FILES['file']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        // Ensure the file is a PDF
        if ($file_ext === 'pdf') {
            $file_url = 'uploads/' . uniqid() . '_' . $file_name;
            move_uploaded_file($file_tmp, $file_url);

            // Insert into database
            $stmt = $conn->prepare("INSERT INTO learning_materials (title, description, uploaded_by, uploaded_by_id, file_url, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssisi", $title, $description, $uploaded_by, $uploaded_by_id, $file_url, $is_approved);

            if ($stmt->execute()) {
                header("Location: tutormodule.php?upload=success");
                exit();
            } else {
                header("Location: tutormodule.php?upload=fail");
                exit();
            }
        } else {
            echo "Only PDF files are allowed.";
        }
    } else {
        echo "Error uploading file.";
    }
}
?>
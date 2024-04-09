<?php
// Database connection details
$servername = "fdb6.awardspace.net"; // Change this if your database is hosted elsewhere
$username = "1544017_users";
$password = "townHall1";
$dbname = "1544017_users";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize user inputs
function sanitize_input($data) {
    $data = trim($data); // Remove leading/trailing whitespace
    $data = stripslashes($data); // Remove backslashes
    $data = htmlspecialchars($data); // Convert special characters to HTML entities
    return $data;
}

// Validate and sanitize user inputs
$candidate = $for_against = $statement = $reference = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $candidate = sanitize_input($_POST['candidate']);
    $for_against = sanitize_input($_POST['for_against']);
    $statement = sanitize_input($_POST['statement']);
    $reference = sanitize_input($_POST['reference']);

    // Additional validation can be added here if needed
}

// Prepare and bind the SQL statement
$stmt = $conn->prepare("INSERT INTO election2024 (candidate, for_against, statement, reference) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $candidate, $for_against, $statement, $reference);

// Set parameters and execute
$candidate = $_POST['candidate'];
$for_against = $_POST['for_against'];
$statement = $_POST['statement'];
$reference = $_POST['reference'];
$stmt->execute();

echo "Form submitted successfully";

$stmt->close();
$conn->close();
?>

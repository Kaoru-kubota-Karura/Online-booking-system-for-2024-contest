<!-- <?php
// $servername = "localhost";
// $username = "root";
// $password = "root";
// $dbname = "contest2024";

// $conn = new mysqli($servername, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
?> -->

<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "contest2024";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>




<!-- themagasticmaharaja07@tmm.in -->


<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tmm";
$charset = 'utf8mb4';
$dsn = "mysql:host=$servername;dbname=$dbname;charset=$charset";
$dsn = "mysql:host=$servername;dbname=$dbname";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
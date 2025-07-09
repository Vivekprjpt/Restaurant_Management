<?php
//Include database connection
include('db.php');

session_start();

//Check if user is logged in
if (!isset($_SESSION['email'])) {
    //Redirect to reservation page if no email is found in the session
    header(header: 'Location: show_reservation.php');
    exit();
}

$email = $_SESSION['email'];

//Fetch the user`s last reservation ID(just and example)
$stmt = $conn->prepare(query: "SELECT id FROM book_table WHERE email = ? ORDER BY id DESC LIMIT 1");

?>

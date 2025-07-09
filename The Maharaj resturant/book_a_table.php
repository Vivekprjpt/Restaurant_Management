<?php
//Include database connection
include('db.php');

session_start();

//Debugging session values

//Error massages array
$errors = [];

//Tabel formats array
$valid_formats = ['2-seater', '4-seater', '6-seater', '8-seater'];

//Default form values
$fields = [
    'name' => $_SESSION['name'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'phone' => $_SESSION['phone'] ?? '',
    'date' => date(format: 'Y-m-d'), //Default to today's date
    'check-in' => date(format: 'H:i'), //Default to current time
    'check-out' => date(format: 'H:i', timestamp: strtotime(datetime: '+1 hour')),  //Default to 1 hour later
    'table_format' => '',
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Collect form data
    $fields['name'] = $_POST['name'] ?? '';
    $fields['email'] = $_POST['email'] ?? '';
    $fields['phone'] = $_POST['phone'] ?? '';
    $fields['date'] = $_POST['date'] ?? date(format: 'Y-m-d');
    $fields['check-in'] = $_POST['check-in'] ?? date(format: 'H:i');
    $fields['check-out'] = $_POST['check-out'] ?? date(format: 'H:i', timestamp: strtotime(datetime: '+1 hour'));
    $fields['table_format'] = $_POST['table_format'] ?? '';
    $fields['message'] = $_POST['message'] ?? '';

    //Validate form fields
    foreach ($fields as $key => $value) {
        if (empty($value)) {
            $errors[$key] = ucfirst(string: str_replace(search: '_', replace: ' ', subject: $key)) . " is required.";
        }
    }

    // Validate reservation date
    $reservation_date = strtotime(datetime: $fields['date']);
    $today = strtotime(datetime: 'today');
    if ($reservation_date < $today || $reservation_date > strtotime(datetime: '+5 days', baseTimestamp: $today)) {
        $errors['date'] = "Reservations can only be made for today or up to 5 days in advance.";
    }

    //Validate check-in and check-out times
    $check_in_datetime = strtotime(datetime: $fields['date'] . ' ' . $fields['check-in']);
    $opening_time = strtotime(datetime: $fields['date'] . '11:00:00');
    $closing_time = strtotime(datetime: $fields['date'] . '23:00:00');

    $current_datetime = time(); // Current time

    if ($fields['date'] == date('Y-m-d') && $check_in_datetime < $current_datetime) {
        $errors['check-in'] = "You cannot book a table for a time that has already passed.";
    } elseif ($check_in_datetime < $opening_time || $check_in_datetime > $closing_time) {
        $errors['check-in'] = "Check-in time must be between 11:00 AM and 11:00 PM.";
    }

    // Validate table format
    if (!in_array($fields['table_format'], $valid_formats)) {
        $errors['table_format'] = "Please select a valid table format (2, 4, 6, or 8 seats).";
    } else {
        // Check for existing bookings with the same table format and time
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM book_table WHERE table_format = ? AND date = ? AND check_in = ?");
        $stmt->bind_param("sss", $fields['table_format'], $fields['date'], $fields['check-in']);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_bookings = $result->fetch_assoc()['count'];

        if ($existing_bookings >= 2) {
            $errors['table_format'] = "Bookings are full for this hour. Please choose a different time or table format.";
        }
    }


    // Check for errors before inserting data
    if (empty($errors)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO book_table (name, email, phone, date, check_in, check_out, table_format, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $fields['name'], $fields['email'], $fields['phone'], $fields['date'], $fields['check-in'], $fields['check-out'], $fields['table_format'], $fields['message']);

        if ($stmt->execute()) {
            header('Location: confirmation.php?id=' . $conn->insert_id);
            exit();
        } else {
            $errors['general'] = "There was an error with your reservation. Please try again.";
            echo "Error: " . $conn->error; // Display the error message
        }

        $stmt->close();
    }
}
$conn->close();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>Table Reservation</title>
    <!-- Favicons -->
    <link href="assets/img/favicon_io/android-chrome-512x512.png" rel="icon">
    <link href="assets/img/favicon_io/apple-touch-icon.png" rel="apple-touch-icon">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/global.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <!-- Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            width: 100%;
            overflow-y: auto;
        }

        body::-webkit-scrollbar {
            display: none;
        }

        body {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        .error {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-control.error {
            border: 1px solid red;
            border-radius: 0.25rem;
        }

        .table-options {
            margin-top: 1rem;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .table-option {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .table-option input[type="radio"] {
            display: none;
        }

        .table-option .table-image {
            width: 100px;
            height: 100px;
            overflow: hidden;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
        }

        .table-option .table-image img {
            width: 100%;
            height: auto;
        }

        .table-option input[type="radio"]:checked+.table-image {
            border: 2px solid #007bff;
        }

        .table-option span {
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
        }
    </style>
</head>

<body>
    <main id="main">
        <section id="book-a-table" class="section book-a-table">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2>Reservation</h2>
                    <p class="fs-1">Book a <span class="text-primary">Table</span></p>
                </div>

                <form id="reservation-form" class="reservation-form" method="post" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'error' : ''; ?>"
                                name="name" placeholder="Your Name"
                                value="<?php echo htmlspecialchars($fields['name']); ?>">
                            <?php if (isset($errors['name'])): ?>
                                <div class="error"><?php echo $errors['name']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <input type="email"
                                class="form-control <?php echo isset($errors['email']) ? 'error' : ''; ?>" name="email"
                                placeholder="Your Email" value="<?php echo htmlspecialchars($fields['email']); ?>">
                            <?php if (isset($errors['email'])): ?>
                                <div class="error"><?php echo $errors['email']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'error' : ''; ?>"
                                name="phone" placeholder="Your Phone"
                                value="<?php echo htmlspecialchars($fields['phone']); ?>">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="error"><?php echo $errors['phone']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <input type="date" class="form-control <?php echo isset($errors['date']) ? 'error' : ''; ?>"
                                name="date" value="<?php echo htmlspecialchars($fields['date']); ?>">
                            <?php if (isset($errors['date'])): ?>
                                <div class="error"><?php echo $errors['date']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <input type="time"
                                class="form-control <?php echo isset($errors['check-in']) ? 'error' : ''; ?>"
                                name="check-in" value="<?php echo htmlspecialchars($fields['check-in']); ?>">
                            <?php if (isset($errors['check-in'])): ?>
                                <div class="error"><?php echo $errors['check-in']; ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-12">
                            <textarea class="form-control <?php echo isset($errors['message']) ? 'error' : ''; ?>"
                                name="message" rows="5"
                                placeholder="Your Message"><?php echo htmlspecialchars($fields['message']); ?></textarea>
                            <?php if (isset($errors['message'])): ?>
                                <div class="error"><?php echo $errors['message']; ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="table-options">
                        <?php
                        $table_format = [
                            '2-seater' => 'assets/img/table/2 seater.jfif',
                            '4-seater' => 'assets/img/table/4 seater.jfif',
                            '6-seater' => 'assets/img/table/6 seater.jfif',
                            '8-seater' => 'assets/img/table/8 seater.jfif'
                        ];

                        foreach ($table_format as $format => $image) {
                            echo '<label class="table-option">';
                            echo '<input type="radio" name="table_format" value="' . $format . '" ' . ($fields['table_format'] == $format ? 'checked' : '') . '>';
                            echo '<div class="table-image"><img src="' . $image . '" alt="' . $format . '"></div>';
                            echo '<span>' . ucfirst($format) . '</span>';
                            echo '</label>';
                        }
                        ?>
                    </div>
                    <?php if (isset($errors['table_format'])): ?>
                        <div class="error"><?php echo $errors['table_format']; ?></div><?php endif; ?>

                    <?php if (isset($errors['general'])): ?>
                        <div class="error"><?php echo $errors['general']; ?></div><?php endif; ?>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary mt-3">Make a Reservation</button>
                        <a href="javascript:history.back()" class="btn btn-primary mt-3 ml-2">Go Back</a>
                        <a href="show_reservation.php" class="btn btn-secondary mt-3 ml-2">Show Reservation</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.j	s"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/js/main.js"></script>
</body>

</html>
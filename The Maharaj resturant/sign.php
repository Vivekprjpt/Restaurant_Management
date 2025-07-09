<?php
session_start();
include('db.php');
include('');

$error_massages = [
    'email' => '',
    'name' => '',
    'mobile' => '',
    'password' => '',
    'confirmpassword' => '',
    'general' => ''
];

$email = $name = $mobile = $password = $confirm_password = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmpassword'];

    //Validate email
    if (empty($email)) {
        $error_massages['email'] = "*Email is required.";
    } elseif (!filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
        $error_massages['email'] = "*Invalid email format.";
    } elseif (!preg_match(pattern: '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', subject: $email)) {
        $error_massages['email'] = "*Invalid email format.";
    } elseif (strpos(haystack: $email, needle: '@') == false) {
        $error_massages['email'] = "*Email must contain an '@' symbol.";
    }

    //Validate name
    if (empty($name)) {
        $error_massages['name'] = "*Name is required.";
    }

    //Validate mobile number
    if (empty($mobile)) {
        $error_massages['mobile'] = "*Password is required.";
    } elseif (strlen(string: $password) < 8) {
        $error_massages['password'] = "*Password must be at least 8 characters.";
    } elseif (!preg_match(pattern: '/[A-Za-z]/', subject: $password) || !preg_match(pattern: '/[0-9]/', subject: $password) || !preg_match(pattern: '/[\W_]/', subject: $password)) {
        $error_massages['password'] = "*Password must include at least one latter, one number, and one special character.";
    }


    //Validate confirm password
    if ($password != $confirm_password) {
        $error_massages['confirmpassword'] = "*Passwords do not match.";
    }

    //Check for the existing email
    if (array_filter(array: $error_massages) == false) {
        $stmt = $conn->prepare(query: "SELECT id FROM users WHERE email = ?");
        $stmt->bind_param(types: "s", var: $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_massages['email'] = "*Email already exists.";
            $stmt->close();
        } else {
            $stmt->close();

            //Generated OTP
            $otp = rand(min: 100000, max: 999999);
            $otp_expiry = date(format: 'Y-m-d-H:i:s', timestamp: strtotime(datetime: '+10 minutes'));

            //Insert user data into the database
            $stmt = $conn->prepare(query: "INSERT INTO users (email, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW())");
            $hashed_password = password_hash(password: $password, algo: PASSWORD_DEFAULT);
            $stmt->bind_param(types: "ssssss", var: $email, vars: $email, $mobile, $hashed_password, $otp, $otp_expiry);

            if ($stmt->execute()) {
                // Send OTP email
                $message = '
                    <div style="font-family: Arial, sans-serif; text-align: center; padding: 20px; background-color: #f4f4f4;">
                        <h1 style="color: #ff6f61;">Perch - Restaurant</h1>
                        <p style="font-size: 18px;">Use the OTP below to proceed:</p>
                        <div style="font-size: 28px; font-weight: bold; color: #333; margin: 20px 0;">
                            ' . $otp . '
                        </div>
                        <p style="font-size: 16px; color: #555;">This OTP is valid for the next 10 minutes. Please do not share it with anyone.</p>
                    </div>
                ';

                $result = smtp_mailer($email, 'OTP Verification', $message);

                if ($result == '') {
                    //Redirect to OTP verification page
                    $_SESSION['email'] = $email;
                    header(header: 'Locstion: otp_verification.php');
                    exit();
                } else {
                    $error_massages['general'] = "Failed to send OTP:" . $result;
                }
            } else {
                $error_massages['general'] = "Failed to register user.";
            }
            $stmt->close();
        }
    }
}


function smtp_mailer($to, $subject, $msg): string
{
    $mail = new PHPMailer();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'tls';
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->isHTML(isHtml: true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "info.themagasticmaharaj@gmail.com";
    $mail->Password = "nqowcljgttwmscqd";
    $mail->SetFrom("info.themagasticmaharaj@gmail.com", "The Magastic Maharaja Restaurant");
    $mail->Subject = $subject;
    $mail->Body = $msg;
    $mail->AddAddress($to);
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false
        )
    );
    if (!$mail->Send()) {
        return $mail->ErrorInfo;
    } else {
        return '';
    }
}

?>

!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>Sign Up</title>
    <!-- Favicons -->
    <link href="assets/img/favicon_io/android-chrome-512x512.png" rel="icon">
    <link href="assets/img/favicon_io/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Amatic+SC:wght@400;700&display=swap"
        rel="stylesheet">

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

    <!-- footer -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900" rel="stylesheet">

    <link rel="stylesheet" href="/assets/myimg/css/ionicons.min.css">
    <link rel="stylesheet" href="/assets/myimg/css/style.css">
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
    </style>
</head>

<body class="index-page" style="height: 100%;">
    <section class="py-3 py-md-5 py-xl-8 img-bg">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-12 col-md-6 col-xl-7">
                    <div class="d-flex justify-content-start ">
                        <div class="col-12 col-xl-9">
                            <h1 id="logo" class="d-inline logo-top">The Magastic Maharaja</h1><span
                                style="font-family: var(--the-barista); color: var(--dot-color);font-size: 150px;line-height: 100px;">.</span>
                            <hr class="my-2 opacity-100" style="border-color: var(--border-color);">
                            <h2 class="h1 mb-4 ">The Magastic Maharaja: Where Every Meal Sits Just Right</h2>
                            <p class="lead mb-5 ">
                                Experience the Perfect Blend of Tradition and Innovation
                                <br>
                                Elevate your dining expectations.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-5">
                    <div class="card bg-light bg-opacity-50 shadow-lg border-0 rounded-4">
                        <div class="card-body opacity-100 p-3 p-md-4 p-xl-5">
                            <div class="text-center mb-4">
                                <h3 class="fs-1">Sign Up</h3>
                                <p>Already Have an Account? <a class="link-a" href="login.php">Login</a></p>
                                <?php if ($error_messages['general']): ?>
                                    <div class="alert alert-danger mt-3">
                                        <?php echo $error_messages['general']; ?>
                                    </div>
                                <?php endif; ?>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <div class="form-floating">
                                                <input type="email"
                                                    class="form-control <?php echo $error_messages['email'] ? 'is-invalid' : ''; ?>"
                                                    name="email" id="email" placeholder="Email"
                                                    value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                                <label for="email" class="form-label">Email Address</label>
                                                <?php if ($error_messages['email']): ?>
                                                    <div class="error-message"><?php echo $error_messages['email']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-floating">
                                                <input type="text"
                                                    class="form-control <?php echo $error_messages['name'] ? 'is-invalid' : ''; ?>"
                                                    name="name" id="name" placeholder="Name"
                                                    value="<?php echo htmlspecialchars($name ?? ''); ?>">
                                                <label for="name" class="form-label">User Name</label>
                                                <?php if ($error_messages['name']): ?>
                                                    <div class="error-message"><?php echo $error_messages['name']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-floating">
                                                <input type="text"
                                                    class="form-control <?php echo $error_messages['mobile'] ? 'is-invalid' : ''; ?>"
                                                    name="mobile" id="mobile" placeholder="Mobile Number"
                                                    value="<?php echo htmlspecialchars($mobile ?? ''); ?>">
                                                <label for="mobile" class="form-label">Mobile Number</label>
                                                <?php if ($error_messages['mobile']): ?>
                                                    <div class="error-message"><?php echo $error_messages['mobile']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-floating">
                                                <input type="password"
                                                    class="form-control <?php echo $error_messages['password'] ? 'is-invalid' : ''; ?>"
                                                    name="password" id="password" placeholder="Password">
                                                <label for="password" class="form-label">Password</label>
                                                <?php if ($error_messages['password']): ?>
                                                    <div class="error-message"><?php echo $error_messages['password']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-floating">
                                                <input type="password"
                                                    class="form-control <?php echo $error_messages['confirmpassword'] ? 'is-invalid' : ''; ?>"
                                                    name="confirmpassword" id="confirmpassword"
                                                    placeholder="Confirm Password">
                                                <label for="confirmpassword" class="form-label">Confirm Password</label>
                                                <?php if ($error_messages['confirmpassword']): ?>
                                                    <div class="error-message">
                                                        <?php echo $error_messages['confirmpassword']; ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button class="btn btn-primary btn-lg" type="submit">Sign Up</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section>

    <footer id="footer" class="footer dark-background position-relative">

        <div class="container">
            <div class="row gy-3">
                <div class="col-lg-3 col-md-6 d-flex">
                    <i class="bi bi-geo-alt icon"></i>
                    <div class="address">
                        <h4>Address</h4>
                        <p>A108 Adam Street</p>
                        <p>New York, NY 535022</p>
                        <p></p>
                    </div>

                </div>

                <div class="col-lg-3 col-md-6 d-flex">
                    <i class="bi bi-telephone icon"></i>
                    <div>
                        <h4>Contact</h4>
                        <p>
                            <strong>Phone:</strong> <span>+91 7624 034 835</span><br>
                            <strong>Email:</strong> <span>info.tmm@gmail.com</span><br>
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 d-flex">
                    <i class="bi bi-clock icon"></i>
                    <div>
                        <h4>Opening Hours</h4>
                        <p>
                            <strong>Mon-Sat:</strong> <span>11AM - 23PM</span><br>
                            <strong>Sunday</strong>: <span> 6PM - 23PM;</span>
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h4>Follow Us</h4>
                    <div class="social-links d-flex">
                        <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© <span>Copyright</span> <strong class="px-1 sitename">TMM</strong> <span>All Rights Reserved</span>
            </p>

        </div>

    </footer>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>
</body>

</html>
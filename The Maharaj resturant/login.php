<?php
session_start();
include 'db.php'; // Include the database connection

// Initialize the variable to prevent undefined variable warni
$show_admin_id_field = false;
$error_massages = [
    'email' => '',
    'password' => '',
    'admin_id' => '',
    'general' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        //Check for empty fields
        if (empty($email)) {
            $error_massages['email'] = "*Email is required.";
        }
        if (empty($password)) {
            $error_messages['password'] = "*Password is required.";
        }

        //Proceed only if there are not empty fields
        if (empty($error_massages['email']) && empty($error_massages['password'])) {
            //Check if the credential are for a regular user
            $sql_user = "SELECT * FROM users WHERE email = ?";
            $stmt_user = $conn->prepare(query: $sql_user);
            $stmt_user->bind_param(types: "s", var: $email);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();

            if ($result_user->num_rows > 0) {
                $user = $result_user->fetch_assoc();
                if ($user && password_verify(password: $password, hash: $user['password'])) {
                    if ($user['verified'] == 1) {
                        $_SESSION['role'] = 'user';
                        $_SESSION['email'] = $email;
                        header(header: "Location: user.php");//Redirect to user panel
                        exit();
                    } else {
                        $error_massages['general'] = "*Your account is not verifed. Please verify your email.";
                    }
                } else {
                    $error_massages['password'] = "Incorrect password.";
                }
            } else {
                $error_massages['email'] = "*Email not found.";
            }
        }
    } elseif (isset($_POST['admin_id'])) {
        $admin_id = $_POST['admin_id'];

        //Check for empty admin_id field
        if (empty($admin_id)) {
            $error_massages['admin_id'] = "*Admin ID is required.";
        } else {
            //Verify Admin ID for the vaild admin
            $sql_admin_id = "SELECT * FROM admins WHERE email = ? AND admin_id = ?";
            $sql_admin_id = $conn->prepare(query: $sql_admin_id);
            $stmt_admin_id->bind_param(types: "ss", var: $_SESSION['email'], vars: $admin_id);
            $stmt_admin_id->execute();
            $result_admin_id = $stmt_admin_id->get_result();

            if ($result_admin_id->num_rows > 0) {
                $_SESSION['loggedin'] = true;
                header(header: "Location: /admin/dashboard.php"); //Redirect to admin panel
                exit();
            } else {
                $error_massages['admin_id'] = "*Invalid Admin ID.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body class="index-page" style="height: 100%;">
    <section class="py-3 py-md-5 py-xl-8 img-bg">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-12 col-md-6 col-xl-7">
                    <div class="d-flex justify-content-start">
                        <div class="col-12 col-xl-9">
                            <h1 id="logo" class="d-inline logo-top">The Megistic Maharaja</h1><span
                                style="font-family: var(--the-barista); color: var(--dot-color);font-size: 9.375rem;line-height: 6.25rem;">.</span>
                            <hr class="my-2 opacity-100" style="border-color: var(--border-color);">
                            <h2 class="h1 mb-4">The magastic Maharaja: Where Every Meal Sits Just Right</h2>
                            <p class="lead mb-5">Experience the Perfect Blend of Tradition and Innovation<br>Elevate
                                your dining expectations.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-5">
                    <div class="card bg-light bg-opacity-50 shadow-lg border-0 rounded-4">
                        <div class="card-body opacity-100 p-3 p-md-4 p-xl-5">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-4 text-center">
                                        <h3 class="fs-1">Login</h3>
                                    </div>

                                    <div>
                                        <p>New To The Magastic Maharaja <a href="sign.php" class="link-a">Sign up</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <form action="" method="post">
                                <?php if (!$show_admin_id_field): ?>
                                    <div class="row gy-3 overflow-hidden">
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input type="email"
                                                    class="form-control <?php echo $error_messages['email'] ? 'error' : ''; ?>"
                                                    name="email" id="email" placeholder="name@example.com"
                                                    value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                                <label for="email" class="form-label">Email</label>
                                                <?php if ($error_messages['email']): ?>
                                                    <div class="error-message">
                                                        <?php echo $error_messages['email']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input type="password"
                                                    class="form-control <?php echo $error_messages['password'] ? 'error' : ''; ?>"
                                                    name="password" id="password" placeholder="Password">
                                                <label for="password" class="form-label">Password</label>
                                                <?php if ($error_messages['password']): ?>
                                                    <div class="error-message">
                                                        <?php echo $error_messages['password']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button class="btn btn-primary btn-lg" type="submit">Login</button>
                                            </div>
                                        </div>
                                        <div class="col-12 text-end mt-2 pe-4">
                                            <a href="forgot_password.php">Forgot your password?</a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="row gy-3 overflow-hidden">
                                        <div class="col-12">
                                            <div class="form-floating mb-3">
                                                <input type="text"
                                                    class="form-control <?php echo $error_messages['admin_id'] ? 'error' : ''; ?>"
                                                    name="admin_id" id="admin_id">
                                                <label for="admin_id" class="form-label">Admin ID:</label>
                                                <?php if ($error_messages['admin_id']): ?>
                                                    <div class="error-message">
                                                        <?php echo $error_messages['admin_id']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <button class="btn btn-primary btn-lg" type="submit">Login</button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </form>
                            <?php if ($error_massages['general']): ?>
                                <div class="alert alert-danger mt-3">
                                    <?php echo $error_massages['general']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
    include("")
        ?>
    
    <!-- Vendor js Files -->
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Magistic Maharaja_ The Restaurant of old theme</title>
</head>

<body>
    <header id="header" class="header d-flex align-item-center sticky-top">
        <div class="container position-relative d-flex align-item-center justify-content-between">

            <a href="login.php" class="logo d-flex align-item-center me-auto me-xl-0">
                <h1 id="home_logo" class="d-inline">The Magastic Maharaja</h1><span id="home_logo_span">.</span>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="login.php" class="active">Home <br></a></li>
                    <li><a href="login.php">About</a></li>
                    <li><a href="login.php">Menu</a></li>
                    <li><a href="login.php">Events</a></li>
                    <li><a href="login.php">Chefs</a></li>
                    <li><a href="login.php">Gallery</a></li>
                    <li><a href="login.php">Contact</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <div class="">
                <a href="login.php" class="btn-getstarted">Login</a>
            </div>


        </div>
    </header>

    <main class="main">

        <!-- Hero section -->
        <section id="hero" class="hero section light-backgorund">

            <div class="container">
                <div class="row gy-4 justify-content-center justify-content-lg-between">
                    <div class="col-lg-5 order-2 order-lg-1 d-flex flex-column justify-content-center">
                        <h1 data-aos="fade-up">The Megistic Maharaja:Where Every
                            <br>Meal Sits Just Right
                        </h1>
                        <p data-aos="fade-up" data-aos-delay="100">Elevate Your dining expectations.</p>
                        <div class="d-flex" data-aos="fade-up" data-aos-delay="200">
                            <a href="login.php" class="btn-get-started">Book a Table</a>
                            <a href=" " class="glightbox btn-watch-video d-flex align-item-center"><i
                                    class="bi bi-play-circle"></i><span>Watch
                                    Video</span></a>
                        </div>
                    </div>

                    <div class="col-lg-5 order-1 order-lg-2 hero-img" data-aos="zoom-out">
                        <img src="" alt="" class="img-fluid animation">
                    </div>
                </div>
            </div>


        </section> <!-- /Hero Section -->

        <!-- About Section -->
        <?php
        include("")
            ?>
        <!-- /About Section -->

        <!-- Why us section -->
        <?php
        include("")
            ?>
        <!-- /Why us Section -->

        <!-- Menu Section -->
        <?php
        include("")
            ?>
        <!-- /Menu Section -->

        <!-- Stats Section -->
        <?php
        include("")
            ?>
        <!-- /Stats Section -->
        <!-- /Testimonials Section -->
        <?php
        include("")
            ?>


        <!-- Events Section -->
        <?php
        include("user_components/events.php")
            ?>


        <!-- /Events Section -->

        <!-- Chefs Section -->
        <?php
        include("chefs.php")
            ?>

        <!-- /Chefs Section -->

        <!-- Book A Table Section -->
        <?php
        include("user_components/book_a_table.php")
            ?>


        <!-- /Book A Table Section -->

        <!-- Gallery Section -->
        <?php
        include("user_components/gallary.php")
            ?>

        <!-- /Gallery Section -->

        <!-- Contact Section -->
        <?php
        //  include("user_components/contact.php")
        ?>

        <!-- /Contact Section -->

    </main>

    <?php
    include("")
        ?>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-item-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor Js Files -->
</body>

</html>
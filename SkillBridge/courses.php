<?php
require_once 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['full_name'] : '';
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;
$message = '';

$unread_count = 0;
$notifications = null;

if ($isLoggedIn) {
    $unread_result = $conn->query("SELECT COUNT(id) AS unread FROM notifications WHERE user_id = $user_id AND is_read = 0");
    $unread_count = ($unread_result) ? $unread_result->fetch_assoc()['unread'] : 0;
    $notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
}

if (isset($_GET['enroll']) && $isLoggedIn) {
    $course_id = intval($_GET['enroll']);
    $check = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $check->bind_param("ii", $user_id, $course_id);
    $check->execute();
    if ($check->get_result()->num_rows == 0) {
        $enroll = $conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $enroll->bind_param("ii", $user_id, $course_id);
        if ($enroll->execute()) {
            $message = "<div class='alert alert-success alert-dismissible fade show fixed-top w-75 mx-auto mt-4 shadow' role='alert' style='z-index: 2000;'>
                            Success! Successfully enrolled. Check <a href='my-courses.php'>My Courses</a>.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>";
        }
    } else {
        $message = "<div class='alert alert-info alert-dismissible fade show fixed-top w-75 mx-auto mt-4 shadow' role='alert' style='z-index: 2000;'>
                        You are already enrolled in this course.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    }
} elseif (isset($_GET['enroll']) && !$isLoggedIn) {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$query = "SELECT * FROM courses";
if (!empty($search)) {
    $query .= " WHERE title LIKE '%$search%' OR category LIKE '%$search%'";
}
$query .= " ORDER BY created_at DESC";
$courses_query = $conn->query($query);

// Handle Logout if triggered from this page
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Browse Courses | SkillBridge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { 
            --primary-purple: #6c5ce7; 
            --dark-bg: #1c144d; 
            --lime-green: #d4ff3f; 
        }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #f8f9fa; 
        }
        
        /* Unified Header & Navbar Styles */
        .header { 
            padding: 15px 0; 
            background: white; 
        }
        .navbar-logo {
            height: 65px;
            width: auto;
            object-fit: contain;
            transition: 0.3s;
        }
        .navbar-brand {
            padding-top: 5px;
            padding-bottom: 5px;
        }
        @media (max-width: 991px) {
            .navbar-logo {
                height: 45px;
            }
        }
        
        /* Page Styles */
        .banner-sub { 
            background: linear-gradient(to top, 
                        rgba(28, 20, 77, 0.95) 0%, 
                        rgba(0, 0, 0, 0.4) 50%, 
                        rgba(0, 0, 0, 0.4) 100%), 
                        url('assets/HBG2.jpg'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 100px 0; 
            border-bottom-left-radius: 40px; 
            border-bottom-right-radius: 40px; 
            margin-bottom: 50px; 
            color: white;
        }
        .search-container { max-width: 600px; margin: -30px auto 40px; position: relative; z-index: 10; }
        .search-input { border-radius: 50px; padding: 15px 30px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .search-btn { position: absolute; right: 10px; top: 7px; border-radius: 50px; background: var(--primary-purple); color: white; padding: 8px 25px; border: none; transition: 0.3s; }
        .search-btn:hover { background: #5b4bc4; }
        .course-box { background: white; border-radius: 15px; overflow: hidden; margin-bottom: 30px; transition: 0.3s; border: 1px solid #eee; }
        .course-box:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.1); transform: translateY(-5px); }
        
        /* Unified Footer & Log Out Styles */
        .footer-con { background: #111; color: white; padding: 80px 0 20px; margin-top: 80px; }
        .copyright { border-top: 1px solid #222; padding-top: 20px; margin-top: 50px; text-align: center; font-size: 14px; color: #666; }
        .log_out { 
            background: var(--lime-green); 
            color: black !important; 
            border-radius: 5px; 
            padding: 8px 18px !important; 
            font-weight: 600;
            transition: 0.3s;
        }
        .log_out:hover { background: #3f8617; transform: translateY(-1px); }
    </style>
</head>
<body>
    <?php echo $message; ?>

    <header class="header sticky-top shadow-sm">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light p-0">
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <img src="assets/SBLOGO2.png" alt="SkillBridge Logo" class="navbar-logo">
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="courses.php">Browse Courses</a></li>
                        
                        <?php if($isLoggedIn): ?>
                            <li class="nav-item"><a class="nav-link" href="my-courses.php">My Courses</a></li>
                            <li class="nav-item"><a class="nav-link" href="assessments.php">Assessments</a></li>
                            <li class="nav-item"><a class="nav-link" href="certifications.php">Certifications</a></li>
                            <li class="nav-item"><a class="nav-link" href="profile.php">My Profile</a></li>
                            
                            <!-- Notifications Dropdown -->
                            <li class="nav-item dropdown mx-2">
                                <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-bell fs-5" style="color: var(--dark-bg);"></i>
                                    <?php if($unread_count > 0): ?>
                                        <span class="position-absolute translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; top: 10px; left: 90%;">
                                            <?php echo $unread_count; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0 shadow border-0" style="width: 280px; border-radius: 12px; overflow: hidden;">
                                    <div class="bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
                                        <h6 class="m-0 fw-bold small">Notifications</h6>
                                        <a href="my-courses.php?read_notifs=1" class="x-small text-decoration-none" style="font-size: 11px;">Mark all read</a>
                                    </div>
                                    <div class="notif-scroll" style="max-height: 300px; overflow-y: auto;">
                                        <?php if($notifications && $notifications->num_rows > 0): ?>
                                            <?php while($n = $notifications->fetch_assoc()): ?>
                                                <div class="p-3 border-bottom small <?php echo $n['is_read'] == 0 ? 'bg-white' : 'bg-light opacity-75'; ?>">
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($n['title']); ?></div>
                                                    <div class="text-muted" style="font-size: 12px;"><?php echo htmlspecialchars($n['message']); ?></div>
                                                </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <div class="p-4 text-center text-muted small">No new notifications.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>

                            <li class="nav-item d-flex align-items-center">
                                <a class="nav-link log_out" href="courses.php?action=logout">Log Out</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link log_in log_out" href="login.php">Log in <i class="fa-solid fa-arrow-right ms-1"></i></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <section class="banner-sub text-center" data-aos="fade-down">
        <div class="container">
            <h2 class="font-weight-bold display-5">Expand Your Knowledge</h2>
            <p class="opacity-75">Discover modules designed to sharpen your digital skills.</p>
        </div>
    </section>

    <div class="container search-container" data-aos="fade-up">
        <form action="courses.php" method="GET">
            <div class="position-relative">
                <input type="text" name="search" class="form-control search-input" placeholder="Search for courses..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass me-1"></i> Search</button>
            </div>
        </form>
    </div>

    <div class="container">
        <div class="row">
            <?php if($courses_query->num_rows > 0): ?>
                <?php while($course = $courses_query->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in">
                        <div class="course-box h-100 d-flex flex-column">
                            <div class="p-4 flex-grow-1">
                                <small class="text-primary font-weight-bold"><i class="fa fa-book me-1"></i> <?php echo $course['lessons']; ?> Lessons</small>
                                <h5 class="my-3 font-weight-bold"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <div class="mt-auto pt-3 border-top">
                                    <a href="courses.php?enroll=<?php echo $course['id']; ?>" class="btn btn-primary w-100" style="background-color: var(--primary-purple); border:none; border-radius: 8px;">Enroll Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fa-solid fa-magnifying-glass text-muted fa-3x mb-3"></i>
                    <h5 class="text-muted">No courses found matching your search.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer-con text-white">
    <div class="container text-center">
        <div class="copyright">
            <p class="mb-0">© 2026 SkillBridge Platform. Developed for Academic Purposes.</p>
            <small class="opacity-50">S. Cojetia | T. Muega | S. Alameda</small>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ duration: 1000, once: true });</script>
</body>
</html>
<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$userName = $_SESSION['full_name'];
$isLoggedIn = true;
$message = '';

// --- Handle Mark as Done ---
if (isset($_GET['mark_done'])) {
    $c_id = intval($_GET['mark_done']);
    $update = $conn->prepare("UPDATE enrollments SET progress = 100 WHERE user_id = ? AND course_id = ?");
    $update->bind_param("ii", $user_id, $c_id);
    if ($update->execute()) {
        $message = "<div class='alert alert-success alert-dismissible fade show fixed-top w-75 mx-auto mt-4 shadow' role='alert' style='z-index: 2000;'>
                        <i class='fa fa-check-circle me-1'></i> Course marked as completed!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    }
}

// --- Mark notifications as read ---
if (isset($_GET['read_notifs'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    header("Location: my-courses.php");
    exit();
}

// --- Fetch Notifications ---
$unread_result = $conn->query("SELECT COUNT(id) AS unread FROM notifications WHERE user_id = $user_id AND is_read = 0");
$unread_count = ($unread_result) ? $unread_result->fetch_assoc()['unread'] : 0;
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// --- Fetch Enrolled Courses ---
// Changed to INNER JOIN and added WHERE e.user_id = ? to strictly show only the logged-in user's enrolled courses
$sql = "SELECT c.id, c.title, c.lessons, c.category, IFNULL(e.progress, 0) as progress 
        FROM courses c 
        JOIN enrollments e ON c.id = e.course_id 
        WHERE e.user_id = ? 
        ORDER BY c.title ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

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
    <title>My Courses | SkillBridge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { 
            --primary-purple: #6c5ce7; 
            --dark-bg: #1c144d; 
            --lime-green: #d4ff3f; 
            --light-bg: #f8f9fa;
        }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        
        /* Header & Navbar Styles */
        .header { padding: 15px 0; background: white; }
        .navbar-logo { height: 65px; width: auto; object-fit: contain; transition: 0.3s; }
        .navbar-brand { padding-top: 5px; padding-bottom: 5px; }
        @media (max-width: 991px) { .navbar-logo { height: 45px; } }

        /* Page Styles */
        .banner-sub { 
            background: linear-gradient(to top, 
                        rgba(28, 20, 77, 0.95) 0%, 
                        rgba(0, 0, 0, 0.4) 50%, 
                        rgba(0, 0, 0, 0.4) 100%), 
                        url('assets/IMG3.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 100px 0; 
            border-bottom-left-radius: 40px; 
            border-bottom-right-radius: 40px; 
            margin-bottom: 50px; 
            color: white;
        }
        
        .course-card { background: white; border-radius: 15px; border: 1px solid #eee; overflow: hidden; height: 100%; transition: 0.3s; }
        .course-card:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.1); transform: translateY(-5px); }
        .progress { height: 8px; border-radius: 10px; background-color: #e9ecef; }
        .btn-purple { background-color: var(--primary-purple); color: white !important; font-weight: 600; border: none; border-radius: 8px; transition: 0.3s;}
        .btn-purple:hover { background-color: #5b4bc4; }
        
        .footer-con { background: #111; color: white; padding: 80px 0 20px; margin-top: 80px; }
        .copyright { border-top: 1px solid #222; padding-top: 20px; margin-top: 50px; text-align: center; font-size: 14px; color: #666; }
        
        .log_out { 
            background: #d4ff3f; 
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
                                        <a href="my-courses.php?read_notifs=1" class="small text-decoration-none" style="font-size: 11px;">Mark all read</a>
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
                                <a class="nav-link log_out" href="my-courses.php?action=logout">Log Out</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <section class="banner-sub text-center" data-aos="fade-down">
        <div class="container">
            <h2 class="text-white font-weight-bold display-5">Welcome Back, <?php echo htmlspecialchars($userName); ?>!</h2>
            <p class="opacity-75">Pick up right where you left off and complete your modules.</p>
        </div>
    </section>

    <div class="container pb-5" style="min-height: 40vh;">
        <div class="row">
            <?php if($enrolled_courses->num_rows > 0): ?>
                <?php while($course = $enrolled_courses->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in">
                        <div class="course-card d-flex flex-column">
                            <div class="p-4 flex-grow-1 d-flex flex-column">
                                <h5 class="font-weight-bold mb-3"><?php echo htmlspecialchars($course['title']); ?></h5>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-bold"><?php echo $course['progress']; ?>% Completed</small>
                                        <?php if($course['progress'] == 100): ?>
                                            <small class="text-success fw-bold"><i class="fa fa-check-circle"></i> Done</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="progress mb-4">
                                        <div class="progress-bar <?php echo $course['progress'] == 100 ? 'bg-success' : ''; ?>" 
                                             style="width: <?php echo $course['progress']; ?>%; background-color: var(--primary-purple);">
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="view-resources.php?id=<?php echo $course['id']; ?>" class="btn btn-purple flex-grow-1 py-2">
                                            <?php echo $course['progress'] == 100 ? 'Review Material' : 'Continue Learning'; ?>
                                        </a>
                                        
                                        <!-- NEW: Mark as Done Button -->
                                        <?php if($course['progress'] < 100): ?>
                                            <a href="my-courses.php?mark_done=<?php echo $course['id']; ?>" class="btn btn-outline-success py-2 px-3 shadow-sm" title="Mark as Done">
                                                <i class="fa fa-check"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-success py-2 px-3 shadow-sm" disabled title="Completed">
                                                <i class="fa-solid fa-check-double"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-light text-center py-5 border rounded" role="alert">
                        <i class="fa-solid fa-book-open text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted">You haven't enrolled in any courses yet.</h5>
                        <a href="courses.php" class="btn btn-primary mt-3" style="background-color: var(--primary-purple); border:none;">Browse Courses</a>
                    </div>
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
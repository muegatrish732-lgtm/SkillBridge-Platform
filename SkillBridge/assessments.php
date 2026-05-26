<?php
// Ensure db.php is included to handle sessions and database calls
require_once 'db.php';

// --- Header Logic ---
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['full_name'] : '';
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Initialize variables for logged-in users
$unread_count = 0;
$notifications = null;

if ($isLoggedIn) {
    // Fetch Notifications count and list
    $unread_result = $conn->query("SELECT COUNT(id) AS unread FROM notifications WHERE user_id = $user_id AND is_read = 0");
    $unread_count = ($unread_result) ? $unread_result->fetch_assoc()['unread'] : 0;
    $notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
}

// --- Assessments Logic ---
// Redirect to login if the user is not authenticated
if (!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

$message = '';

// Check for success redirect from take-assessment.php
if (isset($_GET['success']) && isset($_GET['score'])) {
    $score = intval($_GET['score']);
    $total = intval($_GET['total']);
    $message = "<div class='alert alert-success fixed-top w-75 mx-auto mt-4 shadow' style='z-index: 2000;'>Success! Assessment completed. You scored <strong>$score / $total</strong>.</div>";
}

// Fetch Pending Assessments 
$pending_sql = "
    SELECT a.*, c.title AS course_title, c.category 
    FROM assessments a 
    JOIN enrollments e ON a.course_id = e.course_id 
    JOIN courses c ON a.course_id = c.id
    WHERE e.user_id = ? 
    AND a.id NOT IN (SELECT assessment_id FROM assessment_scores WHERE user_id = ?)
    AND (SELECT COUNT(id) FROM assessment_questions WHERE assessment_id = a.id) > 0
";
$stmt_pending = $conn->prepare($pending_sql);
$stmt_pending->bind_param("ii", $user_id, $user_id);
$stmt_pending->execute();
$pending_assessments = $stmt_pending->get_result();

// Fetch Completed Assessments
$completed_sql = "
    SELECT s.score, s.taken_at, a.id AS assessment_id, a.title, a.total_questions, c.title AS course_title, c.category 
    FROM assessment_scores s 
    JOIN assessments a ON s.assessment_id = a.id 
    JOIN courses c ON a.course_id = c.id
    WHERE s.user_id = ?
    ORDER BY s.taken_at DESC
";
$stmt_completed = $conn->prepare($completed_sql);
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$completed_assessments = $stmt_completed->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assessments | SkillBridge</title>
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
        
        /* Header & Navbar Styles */
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
        .search-container { 
            max-width: 600px; 
            margin: -30px auto 40px; 
            position: relative; 
            z-index: 10; 
        }
        .filter-box { 
            border-radius: 50px; 
            padding: 15px 30px; 
            border: none; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            background: white; 
            text-align: center; 
            font-weight: 600; 
            color: var(--primary-purple); 
        }
        .course-box { 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            margin-bottom: 30px; 
            transition: 0.3s; 
            border: 1px solid #eee; 
        }
        .course-box:hover { 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); 
            transform: translateY(-5px); 
        }
        .footer-con { 
            background: #111; 
            color: white; 
            padding: 80px 0 20px; 
            margin-top: 80px; 
        }
        .copyright { 
            border-top: 1px solid #222; 
            padding-top: 20px; 
            margin-top: 50px; 
            text-align: center; 
            font-size: 14px; 
            color: #666; 
        }
        .log_out { 
            background: #d4ff3f; 
            color: black !important; 
            border-radius: 5px; 
            padding: 8px 18px !important; 
            font-weight: 600;
            transition: 0.3s;
        }
        .log_out:hover { 
            background: #3f8617; 
            transform: translateY(-1px); 
        }
    </style>
</head>
<body>
    <?php echo $message; ?>

    <!-- Navigation Header -->
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
                                <a class="nav-link log_out" href="index.php?action=logout">Log Out</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link log_in" href="login.php">Log in <i class="fa-solid fa-arrow-right ms-1"></i></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <section class="banner-sub text-center" data-aos="fade-down">
        <div class="container">
            <h2 class="font-weight-bold display-5">Assessments & Quizzes</h2>
            <p class="opacity-75">Test your knowledge and track your certification progress.</p>
        </div>
    </section>

    <div class="container search-container" data-aos="fade-up">
        <div class="filter-box">
            <i class="fa-solid fa-clipboard-list me-2"></i> Your Academic Performance
        </div>
    </div>

    <div class="container">
        <!-- Available Assessments -->
        <div class="row mb-5">
            <div class="col-12 mb-4">
                <h4 class="fw-bold" style="color: var(--primary-purple);">Available Assessments</h4>
            </div>
            
            <?php if($pending_assessments->num_rows > 0): ?>
                <?php while($assessment = $pending_assessments->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in">
                        <div class="course-box h-100 d-flex flex-column">
                            <div class="p-4 flex-grow-1">
                                <small class="text-primary font-weight-bold"><i class="fa-solid fa-list-check me-1"></i> <?php echo $assessment['total_questions']; ?> Questions</small>
                                <h5 class="my-3 font-weight-bold"><?php echo htmlspecialchars($assessment['title']); ?></h5>
                                <div class="mt-auto pt-3 border-top">
                                    <a href="take-assessment.php?id=<?php echo $assessment['id']; ?>" class="btn btn-primary w-100" style="background-color: var(--primary-purple); border:none; border-radius: 8px;">Start Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-light text-center py-5 border rounded" role="alert">
                        <i class="fa-regular fa-face-smile text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted">You're all caught up!</h5>
                        <p class="mb-0 text-muted">No pending assessments at the moment.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Completed Assessments -->
        <div class="row">
            <div class="col-12 mb-4">
                <h4 class="fw-bold text-success">Completed Quizzes</h4>
            </div>
            
            <?php if($completed_assessments->num_rows > 0): ?>
                <?php while($result = $completed_assessments->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in">
                        <div class="course-box h-100 d-flex flex-column">
                            <div class="p-4 flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($result['taken_at'])); ?></small>
                                    <i class="fa-solid fa-circle-check text-success"></i>
                                </div>
                                <h5 class="font-weight-bold mb-3"><?php echo htmlspecialchars($result['title']); ?></h5>
                                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="h5 m-0 text-primary font-weight-bold"><?php echo $result['score']; ?> / <?php echo $result['total_questions']; ?></span>
                                    <a href="view-quiz-results.php?id=<?php echo $result['assessment_id']; ?>" class="btn btn-outline-success btn-sm px-3 fw-bold" style="border-radius: 8px;">Review</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-muted">You haven't completed any assessments yet.</p>
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

    <!-- Bootstrap Bundle JS is required for Notifications Dropdown and Mobile Menu to function properly -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 1000, once: true });</script>
</body>
</html>
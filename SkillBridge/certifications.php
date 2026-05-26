<?php
// certifications.php
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

// Redirect to login if the user is not authenticated
if (!$isLoggedIn) {
    header("Location: login.php");
    exit();
}

// --- Certifications Logic ---

// Handle Course Review Submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $cid = intval($_POST['course_id']);
    $rating = intval($_POST['rating']);
    $review_text = $conn->real_escape_string($_POST['review_text']);
    
    // Check if review already exists
    $check_review = $conn->query("SELECT id FROM course_reviews WHERE user_id = $user_id AND course_id = $cid");
    if($check_review->num_rows == 0) {
        $conn->query("INSERT INTO course_reviews (user_id, course_id, rating, review_text) VALUES ($user_id, $cid, $rating, '$review_text')");
        $message = "<div class='alert alert-success fixed-top w-75 mx-auto mt-4 shadow' style='z-index: 2000;'>Thank you! Your review has been submitted.</div>";
    } else {
        $message = "<div class='alert alert-info fixed-top w-75 mx-auto mt-4 shadow' style='z-index: 2000;'>You have already reviewed this course.</div>";
    }
}

// Fetch Profile Pic (If needed for future header additions)
$nav_pic = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=6c5ce7&color=fff';
$pic_query = $conn->query("SELECT profile_picture FROM users WHERE id = $user_id");
if ($pic_query && $pic_query->num_rows > 0) {
    $pic_row = $pic_query->fetch_assoc();
    if (!empty($pic_row['profile_picture'])) { $nav_pic = 'uploads/' . $pic_row['profile_picture']; }
}

// Generate missing certificates
$completed_courses = $conn->query("SELECT c.id AS course_id FROM assessment_scores s JOIN assessments a ON s.assessment_id = a.id JOIN courses c ON a.course_id = c.id WHERE s.user_id = $user_id");
while($row = $completed_courses->fetch_assoc()) {
    $cid = $row['course_id'];
    $check_cert = $conn->query("SELECT id FROM certificates WHERE user_id = $user_id AND course_id = $cid");
    if($check_cert->num_rows == 0) {
        $cert_code = 'SB-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $conn->query("INSERT INTO certificates (user_id, course_id, certificate_code) VALUES ($user_id, $cid, '$cert_code')");
    }
}

// Fetch Certificates
$certificates = $conn->query("SELECT cert.*, c.title AS course_title FROM certificates cert JOIN courses c ON cert.course_id = c.id WHERE cert.user_id = $user_id ORDER BY cert.issued_at DESC");

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Certifications | SkillBridge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- html2pdf Library for downloading certificates -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root { 
            --primary-purple: #6c5ce7; 
            --dark-bg: #1c144d; 
            --lime-green: #d4ff3f; 
            --gold: #f1c40f;
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
                        url('assets/IMG3.jpg'); /* You can change this background image if needed */
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

        /* Certificate Specific Styles */
        .cert-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; border: 1px solid #eee; transition: 0.3s;}
        .cert-card:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.1); transform: translateY(-5px); }
        .btn-gold { background-color: var(--gold); color: #111; font-weight: bold; border: none; }
        .btn-outline-gold { border: 2px solid var(--gold); color: #111; font-weight: bold; }
        .certificate-preview { border: 15px solid var(--dark-bg); padding: 40px; background: #fff; text-align: center; box-shadow: inset 0 0 0 5px var(--gold); }
        .cert-title { font-family: 'Times New Roman', serif; font-size: 3.5rem; color: var(--dark-bg); font-weight: bold; text-transform: uppercase;}
        .cert-name { font-family: 'Times New Roman', serif; font-size: 3rem; color: var(--primary-purple); border-bottom: 2px solid #ddd; display: inline-block; padding: 0 40px; font-style: italic;}
        .cert-course { font-size: 1.8rem; font-weight: bold; color: #333; margin: 20px 0; }
        .seal { font-size: 5rem; color: var(--gold); }
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
                                <a class="nav-link log_out" href="certifications.php?action=logout">Log Out</a>
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
            <h2 class="font-weight-bold display-5">My Certifications</h2>
            <p class="opacity-75">View, download, and share your achievements.</p>
        </div>
    </section>

    <div class="container search-container" data-aos="fade-up">
        <div class="filter-box">
            <i class="fa-solid fa-award me-2"></i> Your Earned Credentials
        </div>
    </div>

    <div class="container pb-5" style="min-height: 40vh;">
        <div class="row">
            <?php if($certificates->num_rows > 0): ?>
                <?php while($cert = $certificates->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="zoom-in">
                        <div class="cert-card h-100 d-flex flex-column">
                            <div class="mb-3 mt-2"><i class="fa-solid fa-certificate fa-4x" style="color: var(--gold);"></i></div>
                            <h5 class="font-weight-bold mb-1 mt-2"><?php echo htmlspecialchars($cert['course_title']); ?></h5>
                            <p class="text-muted small mb-4">Issued: <?php echo date('M d, Y', strtotime($cert['issued_at'])); ?></p>
                            
                            <div class="d-flex flex-column gap-2 mt-auto">
                                <button class="btn btn-gold w-100 py-2 rounded-pill shadow-sm" onclick="showCertificate('<?php echo addslashes($cert['course_title']); ?>', '<?php echo date('F j, Y', strtotime($cert['issued_at'])); ?>', '<?php echo $cert['certificate_code']; ?>')" data-bs-toggle="modal" data-bs-target="#certModal">
                                    <i class="fa fa-eye me-1"></i> View Certificate
                                </button>
                                <!-- LEAVE REVIEW BUTTON -->
                                <button class="btn btn-outline-gold w-100 py-2 rounded-pill" onclick="setReviewCourse(<?php echo $cert['course_id']; ?>, '<?php echo addslashes($cert['course_title']); ?>')" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                    <i class="fa fa-star me-1"></i> Rate Course
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-light text-center py-5 border rounded" role="alert">
                        <i class="fa-solid fa-medal text-muted fs-1 mb-3"></i>
                        <h5 class="text-muted">No Certifications Yet</h5>
                        <p class="mb-0 text-muted">Complete course assessments to earn your certificates!</p>
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

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-bold">Rate <span id="reviewCourseName" class="text-primary"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="course_id" id="reviewCourseId">
                        <div class="mb-3 text-center">
                            <label class="form-label d-block font-weight-bold">Overall Rating</label>
                            <div class="fs-3 text-warning">
                                <input type="radio" name="rating" value="1" required> 1
                                <input type="radio" name="rating" value="2"> 2
                                <input type="radio" name="rating" value="3"> 3
                                <input type="radio" name="rating" value="4"> 4
                                <input type="radio" name="rating" value="5" checked> 5 Stars
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label font-weight-bold">Your Review (Optional)</label>
                            <textarea name="review_text" class="form-control" rows="3" placeholder="What did you learn?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="submit_review" class="btn btn-primary w-100" style="background-color: var(--primary-purple); border:none;">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Realistic Certificate Modal -->
    <div class="modal fade" id="certModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0">
                <!-- Modal Header with Download Button -->
                <div class="modal-header border-0 bg-light">
                    <button type="button" class="btn btn-success btn-sm fw-bold" onclick="downloadPDF()"><i class="fa fa-download me-1"></i> Download PDF</button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-0">
                    <!-- ID attached here for html2pdf targeting -->
                    <div class="certificate-preview" id="certificateToDownload">
                        <h1 class="cert-title">Certificate of Completion</h1>
                        <p class="cert-subtitle">This is to proudly certify that</p>
                        <h2 class="cert-name"><?php echo htmlspecialchars($userName); ?></h2>
                        <p class="cert-subtitle">has successfully completed the module requirements for</p>
                        <h3 class="cert-course" id="modalCourseTitle">Course Name</h3>
                        <div class="mt-5 d-flex justify-content-between px-5">
                            <div><p class="mb-1 fw-bold" id="modalDate">Date</p><div style="border-top: 2px solid #333; width: 150px;">Date Issued</div></div>
                            <i class="fa-solid fa-award seal"></i>
                            <div><p class="mb-1" style="font-family:'Brush Script MT', cursive; font-size:1.5rem;">Admin SkillBridge</p><div style="border-top: 2px solid #333; width: 150px;">System Admin</div></div>
                        </div>
                        <p class="mt-4 text-muted small" id="modalCertId">ID: </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS is required for Notifications Dropdown and Mobile Menu to function properly -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        function showCertificate(course, date, id) { 
            document.getElementById('modalCourseTitle').innerText = course; 
            document.getElementById('modalDate').innerText = date; 
            document.getElementById('modalCertId').innerText = "Certificate ID: " + id; 
        }
        
        function setReviewCourse(id, title) { 
            document.getElementById('reviewCourseId').value = id; 
            document.getElementById('reviewCourseName').innerText = title; 
        }

        // Function to download certificate as PDF
        function downloadPDF() {
            const element = document.getElementById('certificateToDownload');
            const opt = {
                margin:       0.5,
                filename:     'SkillBridge_Certificate.pdf',
                image:        { type: 'jpeg', quality: 1 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
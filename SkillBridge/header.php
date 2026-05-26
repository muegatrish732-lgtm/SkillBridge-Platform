<?php
// Ensure db.php is included to handle sessions and database calls
require_once 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['full_name'] : '';
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Initialize variables for logged-in users
$unread_count = 0;
$notifications = null;

if ($isLoggedIn) {
    // Fetch Notifications count and list from Friend's logic
    $unread_result = $conn->query("SELECT COUNT(id) AS unread FROM notifications WHERE user_id = $user_id AND is_read = 0");
    $unread_count = ($unread_result) ? $unread_result->fetch_assoc()['unread'] : 0;
    $notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
}
?>

<style>
    /* Increased size for better visibility */
    .navbar-logo {
        height: 65px; /* Increased from 45px */
        width: auto;
        object-fit: contain;
        transition: 0.3s;
    }
    
    /* Adjust navbar brand padding to give the larger logo breathing room */
    .navbar-brand {
        padding-top: 5px;
        padding-bottom: 5px;
    }

    @media (max-width: 991px) {
        .navbar-logo {
            height: 45px; /* Responsive size for mobile */
        }
    }
</style>

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

                        
                        <li class="nav-item dropdown mx-2">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
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
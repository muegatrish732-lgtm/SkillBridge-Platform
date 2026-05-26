<?php
require_once 'db.php'; // Original functionality restored
$isLoggedIn = isset($_SESSION['user_id']); // Original logic preserved
$userName = $isLoggedIn ? $_SESSION['full_name'] : '';
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Home | SkillBridge Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --primary-purple: #6c5ce7; --lime-green: #d4ff3f; --dark-bg: #1c144d; }
        body { font-family: 'Poppins', sans-serif; color: #333; overflow-x: hidden; }
        
        /* Header & Nav Logic from your original file */
        .header { padding: 15px 0; background: white; z-index: 1000; }
        .x-small { font-size: 11px; }
        .log_out { 
            background: #d4ff3f; 
            color: black !important; 
            border-radius: 5px; 
            padding: 8px 18px !important; 
            font-weight: 600;
            transition: 0.3s;
        }
        .log_out:hover { background: #3f8617; transform: translateY(-1px); }
        .dropdown-item:active { background-color: var(--primary-purple); }

        /* Banner Design with Gradient Overlay */
        .banner3-con { 
            position: relative;
            background-image: url(assets/HBG1.jpg); 
            background-size: cover;
            background-position: center;
            padding: 180px 0 150px; 
            border-bottom-left-radius: 80px; 
            border-bottom-right-radius: 80px;
        }

        .banner3-con::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(28, 20, 77, 0.3) 0%, rgba(28, 20, 77, 0.9) 100%);
            z-index: 1;
            border-bottom-left-radius: 80px; 
            border-bottom-right-radius: 80px;
        }

        .banner3-con .container { position: relative; z-index: 2; }
        .banner_content h1 { font-size: 4rem; font-weight: 800; line-height: 1.1; margin-bottom: 25px; }
        .primary_btn { background-color: var(--lime-green); color: black !important; padding: 15px 35px; border-radius: 8px; font-weight: 700; display: inline-block; transition: 0.3s; text-decoration: none; }
        
        /* Floating Stats Wrapper */
        .stats-wrapper { margin-top: -80px; position: relative; z-index: 10; }
        .stat-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); height: 100%; }
        .stat-card h2 { color: var(--primary-purple); font-weight: 800; }

        /* Feature Section from original UI */
        .choose3-con { padding: 120px 0 60px; }
        .choose-box { padding: 30px; border-radius: 15px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; margin-bottom: 30px; border: 1px solid #eee; height: 100%; }
        .choose-box:hover { border-color: var(--primary-purple); transform: translateY(-10px); }

        /* Testimonial & Footer Styles */
        .testimonial-card { background: white; padding: 30px; border-radius: 15px; border-left: 5px solid var(--primary-purple); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .footer-con { background: #111; color: white; padding: 80px 0 20px; }
        .copyright { border-top: 1px solid #222; padding-top: 20px; margin-top: 50px; text-align: center; color: #666; }
        .loader-mask { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
<div class="loader-mask" id="preloader"><div class="spinner-border text-primary" role="status"></div></div>
    <?php include 'header.php'; ?>

<section class="banner3-con text-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto" data-aos="fade-up"> <h1 class="text-white">Digital Skills Development Platform</h1>
                <p class="text-white opacity-75 mb-5 fs-5">Empowering students through accessible, interactive, and structured learning modules.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="courses.php" class="primary_btn">Explore Modules <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    <a href="#features" class="btn btn-outline-light btn-lg px-4" style="border-radius: 8px; font-weight: 600;">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container stats-wrapper">
    <div class="row g-4 text-center">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card"><h2>1,000+</h2><p class="text-muted mb-0 font-weight-bold">Active Students</p></div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card"><h2>50+</h2><p class="text-muted mb-0 font-weight-bold">Expert-Led Courses</p></div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-card"><h2>100%</h2><p class="text-muted mb-0 font-weight-bold">Free Certifications</p></div>
        </div>
    </div>
</div>

<section id="features" class="choose3-con">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center mb-5" data-aos="fade-up">
                <h6 class="text-uppercase font-weight-bold text-primary">System Features</h6>
                <h2 class="font-weight-bold display-4">Why Choose SkillBridge</h2>
                <p class="text-muted mt-3">We provide a platform that supports self-paced modules, interactive assessments, and real-time progress tracking.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="choose-box">
                    <i class="fa-solid fa-book-open fa-3x text-primary mb-3"></i>
                    <h3>Structured Modules</h3>
                    <p class="text-muted">Access tutorials and multimedia learning materials securely, organized by skill levels.</p>
                </div>
            </div>
            <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="200">
                <div class="choose-box">
                    <i class="fa-solid fa-laptop-code fa-3x text-warning mb-3"></i>
                    <h3>Interactive Quizzes</h3>
                    <p class="text-muted">Test your knowledge with real-time exercises and receive instant feedback on your performance.</p>
                </div>
            </div>
            <div class="col-lg-4" data-aos="zoom-in" data-aos-delay="300">
                <div class="choose-box">
                    <i class="fa-solid fa-user-graduate fa-3x text-success mb-3"></i>
                    <h3>Certification</h3>
                    <p class="text-muted">Earn digital certificates that you can share on your professional profiles or resumes.</p>
                </div>
            </div>
        </div>
        <?php if(!$isLoggedIn): ?>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="register.php" class="btn btn-outline-primary btn-lg px-5">Register Now</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container py-5">
        <h2 class="text-center fw-bold mb-5" data-aos="fade-up">What Our Students Say</h2>
        <div class="row g-4">
            <div class="col-md-6" data-aos="fade-right" data-aos-delay="100">
                <div class="testimonial-card">
                    <p class="fst-italic text-muted">"SkillBridge has made learning Web Development so much easier. The pace is perfect for working students!"</p>
                    <h6 class="fw-bold mb-0">- Trish Muega</h6>
                    <small class="text-primary">BSIT Student</small>
                </div>
            </div>
            <div class="col-md-6" data-aos="fade-left" data-aos-delay="200">
                <div class="testimonial-card">
                    <p class="fst-italic text-muted">"I love the structured modules. Everything is clear and the quizzes actually help me remember what I learned."</p>
                    <h6 class="fw-bold mb-0">- Juan Dela Cruz</h6>
                    <small class="text-primary">Online Learner</small>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer-con text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h4 class="font-weight-bold">SkillBridge</h4>
                <p class="small opacity-75">Enhancing students’ digital skills through accessible, interactive, and structured learning modules since 2026.</p>
                <ul class="list-unstyled social-icons mt-3">
                    <li><a href="#"><i class="fab fa-facebook-f text-white"></i></a></li>
                    <li><a href="#"><i class="fab fa-twitter text-white"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram text-white"></i></a></li>
                    <li><a href="#"><i class="fab fa-linkedin-in text-white"></i></a></li>
                </ul>
            </div>
            <div class="col-lg-4 mb-4">
                <h4>Useful Links</h4>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="index.php" class="text-white">Home</a></li>
                    <li class="mb-2"><a href="courses.php" class="text-white">Our Courses</a></li>
                    <li class="mb-2"><a href="my-courses.php" class="text-white">My Learning</a></li>
                    <li class="mb-2"><a href="assessments.php" class="text-white">Certifications</a></li>
                </ul>
            </div>
            <div class="col-lg-4 mb-4">
                <h4>Contact Info</h4>
                <p class="small mb-1"><i class="fa fa-map-marker-alt me-2 text-primary"></i> Davao Del Norte State College, Panabo City</p>
                <p class="small mb-1"><i class="fa fa-envelope me-2 text-primary"></i> support@skillbridge.edu.ph</p>
                <p class="small"><i class="fa fa-phone me-2 text-primary"></i> +63 912 345 6789</p>
            </div>
        </div>
        <div class="copyright">
            <p class="mb-0">© 2026 SkillBridge. Presented by S. Cojetia, T. Muega, S. Alameda.</p>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 1000, once: true }); // Original AOS logic restored
    window.addEventListener('load', function() { 
        document.getElementById('preloader').style.display = 'none'; // Original preloader logic
    });
</script>
</body>
</html>
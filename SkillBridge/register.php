<?php
require_once 'db.php';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $conn->real_escape_string($_POST['fullName']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $referral = isset($_POST['referral']) ? $conn->real_escape_string($_POST['referral']) : '';

    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    if ($conn->query($check_sql)->num_rows > 0) {
        $error = "An account with this email already exists.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_sql = "INSERT INTO users (full_name, email, password, referral) VALUES ('$full_name', '$email', '$hashed_password', '$referral')";
        if ($conn->query($insert_sql) === TRUE) {
            $success = "Registration successful! You can now log in.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Join Now | SkillBridge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --primary-purple: #6c5ce7; --btn-hover: #5b4bc4; --bg-light: #f4f7fe; }
        body, html { min-height: 100%; font-family: 'Poppins', sans-serif; background-color: var(--bg-light); }
        .sign-up-form { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 60px 0; }
        .login-form-box { max-width: 500px; width: 100%; margin: 0 auto; }
        .login-card { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .login-form-title h2 { font-weight: 700; color: #333; margin-bottom: 30px; }
        .form-group label { font-weight: 600; font-size: 14px; color: #555; margin-bottom: 8px; }
        .input-field { width: 100%; height: 50px; padding: 10px 20px; border-radius: 10px; border: 1px solid #ddd; background: #fdfdfd; transition: 0.3s; outline: none; }
        .input-field:focus { border-color: var(--primary-purple); box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1); }
        .btn-primary { width: 100%; height: 50px; background-color: var(--primary-purple); border: none; border-radius: 10px; font-weight: 700; font-size: 16px; margin-top: 10px; transition: 0.3s; }
        .btn-primary:hover { background-color: var(--btn-hover); transform: translateY(-2px); }
        .join-now-outer { margin-top: 25px; }
        .join-now-outer a { color: #777; font-size: 14px; text-decoration: none; font-weight: 500; }
        .join-now-outer a:hover { color: var(--primary-purple); }
        .loader-mask { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 9999; display: flex; align-items: center; justify-content: center; }
        
        /* Centered Logo Style */
        .auth-logo { height: 80px; width: auto; margin-bottom: 20px; transition: 0.3s; }
        .auth-logo:hover { transform: scale(1.05); }
    </style>
</head>
<body>
<div class="loader-mask" id="preloader"><div class="spinner-border text-primary" role="status"></div></div>

<section class="sign-up-form">
    <div class="container">
        <div class="login-form-box" data-aos="fade-up">
            <div class="login-form-title text-center">
                <a href="index.php">
                    <img src="assets/SBLOGO.png" alt="SkillBridge Logo" class="auth-logo">
                </a>
                <h2>Create Your FREE Account</h2>
            </div>
            
            <div class="login-card">
                <?php if($error): ?><div class="alert alert-danger p-2 small"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success p-2 small"><?php echo $success; ?></div><?php endif; ?>
                <form action="register.php" method="POST">
                    <div class="form-group"><label for="fullName">Your full name</label><input class="input-field" type="text" id="fullName" name="fullName" placeholder="John Doe" required></div>
                    <div class="form-group"><label for="email">Your e-mail</label><input class="input-field" type="email" id="email" name="email" placeholder="name@example.com" required></div>
                    <div class="form-group"><label for="password">Enter your password <small class="text-muted">(min. 6 characters)</small></label><input class="input-field" type="password" id="password" name="password" minlength="6" placeholder="••••••••" required></div>
                    <div class="form-group">
                        <label for="referral">How did you find out about us?</label>
                        <select id="referral" name="referral" class="input-field">
                            <option selected disabled>Please choose an option</option>
                            <option value="school_announcement">School Announcement</option>
                            <option value="search">Search engine</option>
                            <option value="social">Social media</option>
                            <option value="friend">From a friend</option>
                        </select>
                    </div>
                    <label class="checkbox-container small mt-3 d-block"><input type="checkbox" checked required> I agree to the Terms & Privacy Policy</label>
                    <button type="submit" class="btn btn-primary">Register Now</button>
                </form>
            </div>
            <div class="join-now-outer text-center">
                <a href="login.php">Already have an account? <span class="font-weight-bold" style="color:var(--primary-purple)">Log In</span></a>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
    window.addEventListener('load', function() { document.getElementById('preloader').style.display = 'none'; });
</script>
</body>
</html>
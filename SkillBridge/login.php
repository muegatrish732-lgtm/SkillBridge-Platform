<?php
require_once 'db.php';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, full_name, password, is_admin FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['is_admin'] = $row['is_admin'];
            
            if ($row['is_admin'] == 1) {
                header("Location: admin/admin-dashboard.php");
            } else {
                header("Location: index.php"); 
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Login | SkillBridge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --primary-purple: #6c5ce7; --btn-hover: #5b4bc4; --bg-light: #f4f7fe; }
        body, html { height: 100%; font-family: 'Poppins', sans-serif; background-color: var(--bg-light); }
        .login-form { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 0; }
        .login-form-box { max-width: 450px; width: 100%; margin: 0 auto; }
        .login-card { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); }
        .login-form-title h2 { font-weight: 700; color: #333; margin-bottom: 30px; }
        .form-group label { font-weight: 600; font-size: 14px; color: #555; margin-bottom: 8px; }
        .form_style { width: 100%; height: 50px; padding: 10px 20px; border-radius: 10px; border: 1px solid #ddd; background: #fdfdfd; transition: 0.3s; outline: none; margin-bottom: 15px; }
        .form_style:focus { border-color: var(--primary-purple); box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1); }
        .btn-primary { width: 100%; height: 50px; background-color: var(--primary-purple); border: none; border-radius: 10px; font-weight: 700; font-size: 16px; margin: 10px 0 20px 0; transition: 0.3s; }
        .btn-primary:hover { background-color: var(--btn-hover); transform: translateY(-2px); }
        .forgot-password { color: var(--primary-purple); font-size: 14px; font-weight: 500; text-decoration: none; }
        .join-now-outer { margin-top: 25px; }
        .join-now-outer a { color: #777; font-size: 14px; text-decoration: none; transition: 0.3s; }
        .join-now-outer a:hover { color: var(--primary-purple); }
        .loader-mask { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 9999; display: flex; align-items: center; justify-content: center; }
        
        /* Centered Logo Style */
        .auth-logo { height: 80px; width: auto; margin-bottom: 20px; transition: 0.3s; }
        .auth-logo:hover { transform: scale(1.05); }
    </style>
</head>
<body>
<div class="loader-mask" id="preloader"><div class="spinner-border text-primary" role="status"></div></div>

<section class="login-form">
    <div class="container">
        <div class="login-form-box" data-aos="fade-up">
            <div class="login-form-title text-center">
                <a href="index.php">
                    <img src="assets/SBLOGO.png" alt="SkillBridge Logo" class="auth-logo">
                </a>
                <h2>Welcome Back!</h2>
            </div>
            
            <div class="login-card">
                <?php if($error): ?><div class="alert alert-danger p-2 small"><?php echo $error; ?></div><?php endif; ?>
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Enter your e-mail</label>
                        <input class="form_style" type="email" id="email" name="email" placeholder="name@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Enter your password</label>
                        <input class="form_style" type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label small" style="cursor: pointer;" for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Lost Password?</a>
                    </div>
                </form>
            </div>
            <div class="join-now-outer text-center">
                <a href="register.php">Don't have an account? <span class="font-weight-bold">Join now, create your FREE account</span></a>
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
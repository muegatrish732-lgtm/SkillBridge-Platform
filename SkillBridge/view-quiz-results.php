<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) { header("Location: assessments.php"); exit(); }
$assessment_id = intval($_GET['id']);

// SECURITY CHECK: Verify the student has actually completed this quiz
$score_check = $conn->prepare("SELECT score FROM assessment_scores WHERE user_id = ? AND assessment_id = ?");
$score_check->bind_param("ii", $user_id, $assessment_id);
$score_check->execute();
$result = $score_check->get_result();

if ($result->num_rows === 0) {
    // If no score exists, they haven't taken the quiz! Send them back.
    header("Location: assessments.php");
    exit();
}

$user_score = $result->fetch_assoc()['score'];

// Fetch the questions and correct answers
$assessment = $conn->query("SELECT title FROM assessments WHERE id = $assessment_id")->fetch_assoc();
$questions = $conn->query("SELECT * FROM assessment_questions WHERE assessment_id = $assessment_id");
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Quiz Results | SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .quiz-header { 
            background: linear-gradient(to top, 
                        rgba(28, 20, 77, 0.95) 0%,   /* Darker at the bottom curve */
                        rgba(0, 0, 0, 0.4) 50%,     /* Mid-level dimming */
                        rgba(0, 0, 0, 0.4) 100%),   /* Top dimming for text clarity */
                        url('assets/IMG1.jpg'); /* Your chosen image */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 100px 0; 
            border-bottom-left-radius: 40px; 
            border-bottom-right-radius: 40px; 
            margin-bottom: 50px; 
            color: white;
        }
        /*.quiz-header { background: var(--dark-bg); color: white; padding: 40px 0; border-radius: 0 0 40px 40px; margin-bottom: 40px; }*/
        .q-card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px; border: 1px solid #eee; }
        .option-box { padding: 12px 20px; border: 1px solid #eee; border-radius: 10px; margin-bottom: 10px; }
        .correct-option { background-color: rgba(46, 204, 113, 0.1); border-color: #2ecc71; color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>

<section class="quiz-sub text-center">
    <div class="container">
        </div>
</section>

<div class="quiz-header text-center">
    <div class="container">
        <h2><?php echo htmlspecialchars($assessment['title']); ?> - Answer Key</h2>
        <a href="assessments.php" class="btn btn-outline-light mt-3 rounded-pill px-4"><i class="fa fa-arrow-left me-1"></i> Back to Dashboard</a>
    </div>
</div>

<div class="container pb-5" style="max-width: 800px;">
    
    <div class="alert alert-info text-center border-0 shadow-sm mb-4">
        You scored <strong><?php echo $user_score; ?> points</strong> on this assessment. Review the correct answers below.
    </div>

    <?php $count = 1; while($q = $questions->fetch_assoc()): ?>
        <div class="q-card shadow-sm">
            <h6 class="font-weight-bold mb-4"><?php echo $count++; ?>. <?php echo htmlspecialchars($q['question_text']); ?></h6>
            
            <div class="options">
                <div class="option-box <?php echo ($q['correct_option'] == 'A') ? 'correct-option' : ''; ?>">
                    A. <?php echo htmlspecialchars($q['option_a']); ?> <?php if($q['correct_option'] == 'A') echo "<i class='fa fa-check float-end mt-1'></i>"; ?>
                </div>
                <div class="option-box <?php echo ($q['correct_option'] == 'B') ? 'correct-option' : ''; ?>">
                    B. <?php echo htmlspecialchars($q['option_b']); ?> <?php if($q['correct_option'] == 'B') echo "<i class='fa fa-check float-end mt-1'></i>"; ?>
                </div>
                <div class="option-box <?php echo ($q['correct_option'] == 'C') ? 'correct-option' : ''; ?>">
                    C. <?php echo htmlspecialchars($q['option_c']); ?> <?php if($q['correct_option'] == 'C') echo "<i class='fa fa-check float-end mt-1'></i>"; ?>
                </div>
                <div class="option-box <?php echo ($q['correct_option'] == 'D') ? 'correct-option' : ''; ?>">
                    D. <?php echo htmlspecialchars($q['option_d']); ?> <?php if($q['correct_option'] == 'D') echo "<i class='fa fa-check float-end mt-1'></i>"; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
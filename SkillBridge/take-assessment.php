<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) { header("Location: assessments.php"); exit(); }
$assessment_id = intval($_GET['id']);

// Check if already taken
$check = $conn->query("SELECT id FROM assessment_scores WHERE user_id = $user_id AND assessment_id = $assessment_id");
if ($check->num_rows > 0) { header("Location: assessments.php"); exit(); }

$assessment = $conn->query("SELECT a.*, c.title AS course_title FROM assessments a JOIN courses c ON a.course_id = c.id WHERE a.id = $assessment_id")->fetch_assoc();
$questions = $conn->query("SELECT * FROM assessment_questions WHERE assessment_id = $assessment_id");

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $score = 0;
    $questions_list = $conn->query("SELECT id, correct_option FROM assessment_questions WHERE assessment_id = $assessment_id");
    while($q = $questions_list->fetch_assoc()) {
        $q_id = $q['id'];
        if (isset($_POST['question_'.$q_id]) && $_POST['question_'.$q_id] === $q['correct_option']) {
            $score++;
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO assessment_scores (user_id, assessment_id, score) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $assessment_id, $score);
    if ($stmt->execute()) {
        header("Location: assessments.php?finished=1&score=".$score);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Take Quiz | SkillBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; }
        body { font-family: 'Poppins', sans-serif; background: #f4f7fe; }
        .quiz-header { background: var(--dark-bg); color: white; padding: 40px 0; border-radius: 0 0 40px 40px; margin-bottom: 40px; }
        .q-card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px; border: 1px solid #eee; }
        .form-check { padding: 12px 40px; border: 1px solid #eee; border-radius: 10px; margin-bottom: 10px; transition: 0.3s; cursor: pointer; }
        .form-check:hover { background: #f8f9fa; border-color: var(--primary-purple); }
    </style>
</head>
<body>
<div class="quiz-header text-center">
    <div class="container">
        <h2><?php echo htmlspecialchars($assessment['title']); ?></h2>
        <p class="opacity-75">Course: <?php echo htmlspecialchars($assessment['course_title']); ?></p>
    </div>
</div>
<div class="container pb-5" style="max-width: 800px;">
    <form method="POST">
        <?php $count = 1; while($q = $questions->fetch_assoc()): ?>
            <div class="q-card shadow-sm">
                <h6 class="font-weight-bold mb-4"><?php echo $count++; ?>. <?php echo htmlspecialchars($q['question_text']); ?></h6>
                <div class="options">
                    <label class="form-check d-block">
                        <input class="form-check-input" type="radio" name="question_<?php echo $q['id']; ?>" value="A" required>
                        <span><?php echo htmlspecialchars($q['option_a']); ?></span>
                    </label>
                    <label class="form-check d-block">
                        <input class="form-check-input" type="radio" name="question_<?php echo $q['id']; ?>" value="B" required>
                        <span><?php echo htmlspecialchars($q['option_b']); ?></span>
                    </label>
                    <label class="form-check d-block">
                        <input class="form-check-input" type="radio" name="question_<?php echo $q['id']; ?>" value="C" required>
                        <span><?php echo htmlspecialchars($q['option_c']); ?></span>
                    </label>
                    <label class="form-check d-block">
                        <input class="form-check-input" type="radio" name="question_<?php echo $q['id']; ?>" value="D" required>
                        <span><?php echo htmlspecialchars($q['option_d']); ?></span>
                    </label>
                </div>
            </div>
        <?php endwhile; ?>
        <div class="text-center mt-4">
            <button type="submit" name="submit_quiz" class="btn btn-primary px-5 py-3 rounded-pill fw-bold" style="background: var(--primary-purple);">Submit My Answers</button>
        </div>
    </form>
</div>
</body>
</html>
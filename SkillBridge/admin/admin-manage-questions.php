<?php
require_once '../db.php';

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$admin_check = $conn->query("SELECT is_admin FROM users WHERE id = $user_id")->fetch_assoc();
if (!$admin_check || $admin_check['is_admin'] != 1) { header("Location: ../index.php"); exit(); }

if (!isset($_GET['id'])) { header("Location: admin-assessments.php"); exit(); }
$assessment_id = intval($_GET['id']);
$assessment = $conn->query("SELECT * FROM assessments WHERE id = $assessment_id")->fetch_assoc();

$message = '';

// Add Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $q_text = $conn->real_escape_string($_POST['question_text']);
    $opt_a = $conn->real_escape_string($_POST['option_a']);
    $opt_b = $conn->real_escape_string($_POST['option_b']);
    $opt_c = $conn->real_escape_string($_POST['option_c']);
    $opt_d = $conn->real_escape_string($_POST['option_d']);
    $correct = $conn->real_escape_string($_POST['correct_option']);
    
    $sql = "INSERT INTO assessment_questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
            VALUES ($assessment_id, '$q_text', '$opt_a', '$opt_b', '$opt_c', '$opt_d', '$correct')";
    if ($conn->query($sql)) { $message = "<div class='alert alert-success'>Question added!</div>"; }
}

// Edit Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_question'])) {
    $q_id = intval($_POST['question_id']);
    $q_text = $conn->real_escape_string($_POST['question_text']);
    $opt_a = $conn->real_escape_string($_POST['option_a']);
    $opt_b = $conn->real_escape_string($_POST['option_b']);
    $opt_c = $conn->real_escape_string($_POST['option_c']);
    $opt_d = $conn->real_escape_string($_POST['option_d']);
    $correct = $conn->real_escape_string($_POST['correct_option']);
    
    $sql = "UPDATE assessment_questions SET question_text='$q_text', option_a='$opt_a', option_b='$opt_b', option_c='$opt_c', option_d='$opt_d', correct_option='$correct' WHERE id=$q_id";
    if ($conn->query($sql)) { $message = "<div class='alert alert-success'>Question updated!</div>"; }
}

// Delete Question
if (isset($_GET['delete_q'])) {
    $del_id = intval($_GET['delete_q']);
    $conn->query("DELETE FROM assessment_questions WHERE id = $del_id");
    $message = "<div class='alert alert-warning'>Question removed.</div>";
}

$questions = $conn->query("SELECT * FROM assessment_questions WHERE assessment_id = $assessment_id");
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <title>Manage Questions | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --primary-purple: #6c5ce7; --dark-bg: #1c144d; --light-bg: #f4f7fe; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--light-bg); }
        .header { background: white; padding: 15px 0; }
        .banner-sub { background-color: var(--dark-bg); padding: 40px 0; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 40px; }
        .q-card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); margin-bottom: 20px; border: 1px solid #eee; }
        .correct-ans { font-weight: bold; color: #2ecc71; }
    </style>
</head>
<body>

<header class="header sticky-top shadow-sm">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <a class="navbar-brand font-weight-bold" href="admin-dashboard.php" style="color:var(--primary-purple);">SkillBridge Admin</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="admin-assessments.php"><i class="fa fa-arrow-left"></i> Back to Assessments</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>

<section class="banner-sub text-center"><h2 class="text-white font-weight-bold">Questions for: <?php echo htmlspecialchars($assessment['title']); ?></h2></section>

<div class="container pb-5">
    <?php echo $message; ?>
    
    <div class="d-flex justify-content-between mb-4">
        <h5 class="m-0 text-muted">Total Target: <?php echo $assessment['total_questions']; ?> Questions</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuestionModal"><i class="fa fa-plus me-1"></i> Add Question</button>
    </div>

    <?php $i=1; while($q = $questions->fetch_assoc()): ?>
        <div class="q-card">
            <div class="d-flex justify-content-between align-items-start">
                <h6 class="font-weight-bold">Q<?php echo $i++; ?>: <?php echo htmlspecialchars($q['question_text']); ?></h6>
                <div>
                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editQuestionModal" 
                        onclick="fillEditModal(<?php echo $q['id']; ?>, `<?php echo addslashes($q['question_text']); ?>`, `<?php echo addslashes($q['option_a']); ?>`, `<?php echo addslashes($q['option_b']); ?>`, `<?php echo addslashes($q['option_c']); ?>`, `<?php echo addslashes($q['option_d']); ?>`, '<?php echo $q['correct_option']; ?>')">
                        <i class="fa fa-edit"></i> Edit
                    </button>
                    <a href="admin-manage-questions.php?id=<?php echo $assessment_id; ?>&delete_q=<?php echo $q['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete question?');"><i class="fa fa-trash"></i></a>
                </div>
            </div>
            <hr>
            <div class="row small">
                <div class="col-md-3 <?php echo ($q['correct_option'] == 'A') ? 'correct-ans' : 'text-muted'; ?>">A. <?php echo htmlspecialchars($q['option_a']); ?> <?php if($q['correct_option'] == 'A') echo "<i class='fa fa-check-circle'></i>"; ?></div>
                <div class="col-md-3 <?php echo ($q['correct_option'] == 'B') ? 'correct-ans' : 'text-muted'; ?>">B. <?php echo htmlspecialchars($q['option_b']); ?> <?php if($q['correct_option'] == 'B') echo "<i class='fa fa-check-circle'></i>"; ?></div>
                <div class="col-md-3 <?php echo ($q['correct_option'] == 'C') ? 'correct-ans' : 'text-muted'; ?>">C. <?php echo htmlspecialchars($q['option_c']); ?> <?php if($q['correct_option'] == 'C') echo "<i class='fa fa-check-circle'></i>"; ?></div>
                <div class="col-md-3 <?php echo ($q['correct_option'] == 'D') ? 'correct-ans' : 'text-muted'; ?>">D. <?php echo htmlspecialchars($q['option_d']); ?> <?php if($q['correct_option'] == 'D') echo "<i class='fa fa-check-circle'></i>"; ?></div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- Modal: Add Question -->
<div class="modal fade" id="addQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title">New Multiple Choice Question</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Question Prompt</label><textarea name="question_text" class="form-control" rows="3" required></textarea></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label>Option A</label><input type="text" name="option_a" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>Option B</label><input type="text" name="option_b" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>Option C</label><input type="text" name="option_c" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>Option D</label><input type="text" name="option_d" class="form-control" required></div>
                </div>
                <div class="mb-3"><label>Correct Answer Key</label>
                    <select name="correct_option" class="form-select" required>
                        <option value="A">Option A</option><option value="B">Option B</option><option value="C">Option C</option><option value="D">Option D</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" name="add_question" class="btn btn-primary">Save to Quiz</button></div>
        </form>
    </div></div>
</div>

<!-- Modal: Edit Question -->
<div class="modal fade" id="editQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="POST">
            <div class="modal-header"><h5 class="modal-title">Edit Question</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="question_id" id="edit_q_id">
                <div class="mb-3"><label>Question Prompt</label><textarea name="question_text" id="edit_q_text" class="form-control" rows="3" required></textarea></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label>Option A</label><input type="text" name="option_a" id="edit_opt_a" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>Option B</label><input type="text" name="option_b" id="edit_opt_b" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>Option C</label><input type="text" name="option_c" id="edit_opt_c" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label>Option D</label><input type="text" name="option_d" id="edit_opt_d" class="form-control" required></div>
                </div>
                <div class="mb-3"><label>Correct Answer Key</label>
                    <select name="correct_option" id="edit_correct" class="form-select" required>
                        <option value="A">Option A</option><option value="B">Option B</option><option value="C">Option C</option><option value="D">Option D</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" name="edit_question" class="btn btn-warning">Update Question</button></div>
        </form>
    </div></div>
</div>

<script>
    function fillEditModal(id, text, a, b, c, d, correct) {
        document.getElementById('edit_q_id').value = id;
        document.getElementById('edit_q_text').value = text;
        document.getElementById('edit_opt_a').value = a;
        document.getElementById('edit_opt_b').value = b;
        document.getElementById('edit_opt_c').value = c;
        document.getElementById('edit_opt_d').value = d;
        document.getElementById('edit_correct').value = correct;
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
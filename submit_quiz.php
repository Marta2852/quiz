<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "quiz");
$test_id = intval($_POST['test_id']);
$answers = $_POST['answer'] ?? []; // array of question_id => answer_id

$score = 0;

// Fetch all questions for the test
$questions = $conn->query("SELECT * FROM questions WHERE test_id=$test_id ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Result</title>
</head>
<body>
    <h1>Quiz Results</h1>
<?php
while ($q = $questions->fetch_assoc()):
    $qid = $q['id'];
    echo "<h3>" . htmlspecialchars($q['question_text']) . "</h3>";

    // fetch all answers for this question
    $ans_res = $conn->query("SELECT * FROM answers WHERE question_id=$qid");
    $user_answer_id = $answers[$qid] ?? null;

    while ($a = $ans_res->fetch_assoc()):
        $is_user = ($a['id'] == $user_answer_id);
        $is_correct = ($a['is_correct'] == 1);

        if ($is_user && $is_correct) {
            $score++;
            echo "<p style='color:green;'><strong>Your answer: " . htmlspecialchars($a['answer_text']) . " ✔</strong></p>";
        } elseif ($is_user && !$is_correct) {
            echo "<p style='color:red;'><strong>Your answer: " . htmlspecialchars($a['answer_text']) . " ✘</strong></p>";
        } elseif ($is_correct) {
            echo "<p style='color:green;'>Correct answer: " . htmlspecialchars($a['answer_text']) . "</p>";
        }
    endwhile;
endwhile;
?>
    <hr>
<h2>You scored <?php echo $score; ?> out of <?php echo count($answers); ?></h2>

<?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="admin_dashboard.php">Back to Admin Dashboard</a>
<?php else: ?>
    <a href="index.php">Back to Home</a>
<?php endif; ?>

</body>
</html>

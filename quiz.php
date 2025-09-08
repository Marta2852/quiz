<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// prevent caching so shuffle is visible every time
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_GET['test_id'])) {
    header("Location: home.php");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "quiz");
$test_id = intval($_GET['test_id']);

// Fetch test info
$test = $conn->query("SELECT * FROM tests WHERE id=$test_id")->fetch_assoc();

// Fetch questions + answers
$questions = $conn->query("SELECT * FROM questions WHERE test_id=$test_id ORDER BY id ASC");

$all_questions = [];
while ($q = $questions->fetch_assoc()) {
    $q_id = $q['id'];
    $answers = $conn->query("SELECT * FROM answers WHERE question_id=$q_id");
    $q['answers'] = $answers->fetch_all(MYSQLI_ASSOC);
    $all_questions[] = $q;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($test['title']); ?> Quiz</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: auto; padding: 20px; }
        .question { display: none; margin-bottom: 20px; }
        .answers label { display: block; margin: 5px 0; }
        .progress { height: 20px; background: #ddd; border-radius: 10px; margin: 20px 0; }
        .progress-bar { height: 100%; background: #007bff; width: 0%; border-radius: 10px; transition: width 0.3s; }
        .nav-buttons { margin-top: 20px; }
        button { padding: 8px 12px; margin-right: 10px; }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($test['title']); ?> Quiz</h1>

    <form action="submit_quiz.php" method="post">
        <input type="hidden" name="test_id" value="<?php echo $test_id; ?>">

        <?php
// inside your quiz form loop
foreach ($all_questions as $index => $q): 
    $answers = $q['answers'];
    shuffle($answers); // <-- shuffle answers so order is different each time
?>
    <div class="question" id="q<?php echo $index; ?>">
        <h3>Question <?php echo $index+1; ?>:</h3>
        <p><?php echo htmlspecialchars($q['question_text']); ?></p>
        <div class="answers">
            <?php foreach ($answers as $a): ?>
                <label>
                    <input type="radio" name="answer[<?php echo $q['id']; ?>]" value="<?php echo $a['id']; ?>">
                    <?php echo htmlspecialchars($a['answer_text']); ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>


        <div class="progress">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <div class="nav-buttons">
            <button type="button" id="prevBtn">Previous</button>
            <button type="button" id="nextBtn">Next</button>
            <button type="submit" id="submitBtn" style="display:none;">Submit</button>
        </div>
    </form>

    <script>
        let current = 0;
const questions = document.querySelectorAll('.question');
const progressBar = document.getElementById('progressBar');
const nextBtn = document.getElementById('nextBtn');
const prevBtn = document.getElementById('prevBtn');
const submitBtn = document.getElementById('submitBtn');

function showQuestion(index) {
    questions.forEach((q, i) => q.style.display = (i === index ? 'block' : 'none'));
    progressBar.style.width = ((index+1) / questions.length * 100) + '%';

    prevBtn.style.display = (index === 0 ? 'none' : 'inline-block');
    nextBtn.style.display = (index === questions.length-1 ? 'none' : 'inline-block');
    submitBtn.style.display = (index === questions.length-1 ? 'inline-block' : 'none');
}

nextBtn.addEventListener('click', () => {
    if (current < questions.length-1) current++;
    showQuestion(current);
});

prevBtn.addEventListener('click', () => {
    if (current > 0) current--;
    showQuestion(current);
});

// Prevent Enter key from submitting form
document.querySelector('form').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
    }
});

showQuestion(current);

    </script>
</body>
</html>


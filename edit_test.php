<?php
session_start();
if(!isset($_SESSION['username']) || $_SESSION['role']!=='admin'){
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost","root","root","quiz");

if(!isset($_GET['id'])){
    header("Location: admin_dashboard.php");
    exit();
}

$test_id = intval($_GET['id']);

// --- Update test title ---
if(isset($_POST['save_test'])){
    $title = trim($_POST['test_title']);
    if($title){
        $stmt = $conn->prepare("UPDATE tests SET title=? WHERE id=?");
        $stmt->bind_param("si",$title,$test_id);
        $stmt->execute();
    }
}

// --- Add question ---
if(isset($_POST['add_question'])){
    $qtext = trim($_POST['question_text']);
    if($qtext){
        $stmt = $conn->prepare("INSERT INTO questions (test_id, question_text) VALUES (?,?)");
        $stmt->bind_param("is",$test_id,$qtext);
        $stmt->execute();
    }
}

// --- Add answer ---
if(isset($_POST['add_answer'])){
    $question_id = intval($_POST['question_id']);
    $atext = trim($_POST['answer_text']);
    $is_correct = isset($_POST['is_correct']) ? 1 : 0;
    if($atext){
        $stmt = $conn->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?,?,?)");
        $stmt->bind_param("isi",$question_id,$atext,$is_correct);
        $stmt->execute();
    }
}

// --- Edit question ---
if(isset($_POST['edit_question'])){
    $qid = intval($_POST['question_id']);
    $qtext = trim($_POST['question_text']);
    if($qtext){
        $stmt = $conn->prepare("UPDATE questions SET question_text=? WHERE id=?");
        $stmt->bind_param("si",$qtext,$qid);
        $stmt->execute();
    }
}

// --- Edit answer ---
if(isset($_POST['edit_answer'])){
    $aid = intval($_POST['answer_id']);
    $atext = trim($_POST['answer_text']);
    $is_correct = isset($_POST['is_correct']) ? 1 : 0;
    if($atext){
        $stmt = $conn->prepare("UPDATE answers SET answer_text=?, is_correct=? WHERE id=?");
        $stmt->bind_param("sii",$atext,$is_correct,$aid);
        $stmt->execute();
    }
}

// --- Delete question & answers ---
if(isset($_GET['delete_question'])){
    $qid = intval($_GET['delete_question']);
    $conn->query("DELETE FROM answers WHERE question_id=$qid");
    $conn->query("DELETE FROM questions WHERE id=$qid");
}

// --- Delete answer ---
if(isset($_GET['delete_answer'])){
    $aid = intval($_GET['delete_answer']);
    $conn->query("DELETE FROM answers WHERE id=$aid");
}

// --- Fetch test, questions, answers ---
$test = $conn->query("SELECT * FROM tests WHERE id=$test_id")->fetch_assoc();
$questions = $conn->query("SELECT * FROM questions WHERE test_id=$test_id ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Test: <?php echo htmlspecialchars($test['title']); ?></title>
</head>

<body>
<h1>Edit Test: <?php echo htmlspecialchars($test['title']); ?></h1>
<a href="admin_dashboard.php">← Back</a>

<h2>Test Title</h2>
<form method="post">
    <input type="text" name="test_title" value="<?php echo htmlspecialchars($test['title']); ?>" required>
    <button type="submit" name="save_test">Save</button>
</form>

<h2>Questions</h2>

<!-- Add new question -->
<form method="post">
    <input type="text" name="question_text" placeholder="New Question" required>
    <button type="submit" name="add_question">Add Question</button>
</form>

<ul>
<?php while($q=$questions->fetch_assoc()): ?>
    <li>
        <form method="post" style="display:inline;">
            <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
            <input type="text" name="question_text" value="<?php echo htmlspecialchars($q['question_text']); ?>" required>
            <button type="submit" name="edit_question">Save </button>
        </form>
        <a href="edit_test.php?id=<?php echo $test_id; ?>&delete_question=<?php echo $q['id']; ?>" style="color:red;">Delete </a>

        <!-- Answers -->
        <ul>
        <?php 
        $answers = $conn->query("SELECT * FROM answers WHERE question_id=".$q['id']." ORDER BY id ASC");
        while($a=$answers->fetch_assoc()): ?>
            <li>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="answer_id" value="<?php echo $a['id']; ?>">
                    <input type="text" name="answer_text" value="<?php echo htmlspecialchars($a['answer_text']); ?>" required>
                    <label><input type="checkbox" name="is_correct" <?php if($a['is_correct']) echo 'checked'; ?>> Correct</label>
                    <button type="submit" name="edit_answer">Save </button>
                </form>
                <a href="edit_test.php?id=<?php echo $test_id; ?>&delete_answer=<?php echo $a['id']; ?>" style="color:red;">Delete </a>
            </li>
        <?php endwhile; ?>
        </ul>

        <!-- Add new answer -->
        <form method="post">
            <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
            <input type="text" name="answer_text" placeholder="New Answer" required>
            <label><input type="checkbox" name="is_correct"> Correct</label>
            <button type="submit" name="add_answer">Add Answer</button>
        </form>
    </li>
<?php endwhile; ?>
</ul>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost","root","root","quiz");

// Add test
if(isset($_POST['add_test'])){
    $title = trim($_POST['test_title']);
    if($title){
        $stmt = $conn->prepare("INSERT INTO tests (title) VALUES (?)");
        $stmt->bind_param("s",$title);
        $stmt->execute();
    }
}

// Delete test
if(isset($_GET['delete_test'])){
    $id = intval($_GET['delete_test']);
    $conn->query("DELETE FROM tests WHERE id=$id");
}

// Fetch tests
$tests = $conn->query("SELECT * FROM tests ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
</head>
<body>
<h1>Admin Dashboard</h1>
<p>Hi, <b><?php echo $_SESSION['username']; ?></b> | <a href="logout.php">Logout</a></p>

<h2>Tests</h2>
<form method="post">
    <input type="text" name="test_title" placeholder="New Test" required>
    <button type="submit" name="add_test">Add Test</button>
</form>

<ul>
<?php while($row = $tests->fetch_assoc()): ?>
    <li>
        <a href="quiz.php?test_id=<?php echo $row['id']; ?>">
            <?php echo htmlspecialchars($row['title']); ?>
        </a>
        <!-- Keep edit and delete links -->
        <a href="edit_test.php?id=<?php echo $row['id'];  ?>" style="color:green">Edit</a>
        <a href="admin_dashboard.php?delete_test=<?php echo $row['id']; ?>" style="color:red;">Delete</a>
    </li>
<?php endwhile; ?>
</ul>
</body>
</html>

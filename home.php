<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "quiz");
$username = $_SESSION['username'];

// Fetch all tests
$tests = $conn->query("SELECT * FROM tests ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Home</title>
</head>
<body>
    <h1>Hi, <?php echo htmlspecialchars($username); ?> 👋</h1>
    <a href="logout.php">Logout</a>

    <h2>Available Tests</h2>
    <?php if ($tests->num_rows > 0): ?>
        <ul>
        <?php while ($test = $tests->fetch_assoc()): ?>
            <li>
            <a href="quiz.php?test_id=<?php echo $test['id']; ?>">
                <?php echo htmlspecialchars($test['title']); ?>
            </a>
            </li>
        <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No tests available.</p>
    <?php endif; ?>
</body>
</html>

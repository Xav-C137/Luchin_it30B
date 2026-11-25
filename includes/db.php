<?php

// Shared database connection

$host = 'localhost';
$db   = 'it30_lab_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ---------- 1. KPI COUNTS ----------
$studentCount  = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$bookCount     = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalBorrows  = $pdo->query("SELECT COUNT(*) FROM borrow")->fetchColumn();
$activeBorrows = $pdo->query("SELECT COUNT(*) FROM borrow WHERE borrow_return_date IS NULL")->fetchColumn();

// ---------- 2. BOOKS BY CATEGORY ----------
$catStmt = $pdo->query("
    SELECT book_category, COUNT(*) AS cnt
    FROM books
    GROUP BY book_category
    ORDER BY cnt DESC
");

$categories = [];
$categoryCounts = [];

while ($row = $catStmt->fetch()) {
    $categories[]     = $row['book_category'];
    $categoryCounts[] = (int) $row['cnt'];
}

// ---------- 3. BORROW ACTIVITY LAST 7 DAYS ----------

$dailyStmt = $pdo->query("
    SELECT DATE(borrow_date) AS day, COUNT(*) AS cnt
    FROM borrow
    WHERE borrow_date >= CURDATE() - INTERVAL 6 DAY
    GROUP BY DATE(borrow_date)
    ORDER BY day ASC
");

$borrowByDay = [];

while ($row = $dailyStmt->fetch()) {
    $borrowByDay[$row['day']] = (int) $row['cnt'];
}

// Generate labels and counts for the last 7 days

$dayLabels = [];
$dailyBorrows = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));

    $dayLabels[] = date('D', strtotime($date));
    $dailyBorrows[] = $borrowByDay[$date] ?? 0;
}

// ---------- 4. RECENT BORROWS ----------

$recentStmt = $pdo->query("
    SELECT
        s.student_first_name,
        s.student_last_name,
        b.book_title,
        br.borrow_return_date
    FROM borrow br
    INNER JOIN students s ON br.student_id = s.student_id
    INNER JOIN books b ON br.book_id = b.book_id
    ORDER BY br.borrow_id DESC
    LIMIT 5
");
$recentBorrows = $recentStmt->fetchAll();

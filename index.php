<?php

require_once __DIR__ . '/includes/db.php';

// Session
session_start();

// Determine current section
$section = $_GET['section'] ?? 'students';

// Determine CRUD operation
$action = $_GET['action'] ?? '';

// Fetch students
if($section === 'students'){
    $stmt = $pdo->query("   
        SELECT *
        FROM students
        ORDER BY student_id DESC
    ");

    $students = $stmt->fetchAll();
}

// Create Student
if($section==='students' && $action==='create'){

    if($_SERVER['REQUEST_METHOD']==='POST'){

        $firstName = trim($_POST['student_first_name'] ?? '');
        $lastName = trim($_POST['student_last_name'] ?? '');
        $course = trim($_POST['student_course'] ?? '');

        if($firstName !== '' && $lastName !== '' && $course !== ''){
            $sql = "
                INSERT INTO students(
                    student_first_name,
                    student_last_name,
                    student_course
                )
                VALUES(?,?,?)
            ";

            $stmt=$pdo->prepare($sql);

            $stmt->execute([
                $firstName,
                $lastName,
                $course
            ]); 

            header("Location: index.php?section=students");
            exit;
        }
    }
}

// Update Student
if($section==='students' && $action==='update'){
    
    $studentId = (int) ($_GET['id'] ?? 0);
    
    $firstName = trim($_POST['student_first_name'] ?? '');
    $lastName = trim($_POST['student_last_name'] ?? '');
    $course = trim($_POST['student_course'] ?? '');

    // Retrieve student info by default
    $stmt = $pdo->prepare("
        SELECT *
        FROM students
        WHERE student_id = ?
    ");
    
    $stmt->execute([$studentId]);

    $student = $stmt->fetch();

    if(!$student){
        die("Student Not Found");

    }

    if($_SERVER['REQUEST_METHOD'] ==='POST'){ 

        $sql=("
        UPDATE students
            SET
            student_first_name = ?,
            student_last_name = ?,
            student_course = ?
            WHERE student_id =?
        ");

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $firstName,
                $lastName,
                $course,
                $studentId
            ]);

            header("Location: index.php?section=students");
            exit;
    }

}

// Fetch books
if($section === 'books'){
    $stmt = $pdo->query("
        SELECT *
        FROM books
        ORDER BY book_id DESC
    ");

    $books = $stmt->fetchAll();
}

// Create Book
if($section==='books' && $action==='create'){

    if($_SERVER['REQUEST_METHOD']==='POST'){

        $bookTitle = trim($_POST['book_title'] ?? '');
        $bookAuthor = trim($_POST['book_author'] ?? '');
        $bookCategory = trim($_POST['book_category'] ?? '');

        if($bookTitle !== '' && $bookAuthor !== '' && $bookCategory !== ''){
            $sql = "
                INSERT INTO books(
                    book_title,
                    book_author,
                    book_category
                )
                VALUES(?,?,?)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $bookTitle,
                $bookAuthor,
                $bookCategory
            ]);

            header("Location: index.php?section=books");
            exit;
        }
    }
}

// Update Book
if($section==='books' && $action==='update'){

    $bookId = (int) ($_GET['id'] ?? 0);

    if($_SERVER['REQUEST_METHOD']==='POST'){

        $bookTitle = trim($_POST['book_title'] ?? '');
        $bookAuthor = trim($_POST['book_author'] ?? '');
        $bookCategory = trim($_POST['book_category'] ?? '');

        $sql = "
            UPDATE books
            SET
                book_title = ?,
                book_author = ?,
                book_category = ?
            WHERE book_id = ?
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $bookTitle,
            $bookAuthor,
            $bookCategory,
            $bookId
        ]);

        header("Location: index.php?section=books");
        exit;
    }

    // Retrieve book info by default
    $stmt = $pdo->prepare("
        SELECT *
        FROM books
        WHERE book_id = ?
    ");

    $stmt->execute([$bookId]);

    $book = $stmt->fetch();

    if(!$book){
        die("Book Not Found");
    }
}

// Retrieve borowed books
if($section === 'borrow'){

    // Fetch Students for borrow form
    $stmt = $pdo->query("
        SELECT 
            student_id,
            student_first_name,
            student_last_name
        FROM students
        ORDER BY student_last_name, student_first_name
        ");

        $students = $stmt->fetchAll();

    // Retrive Books from borrow form
    $stmt = $pdo->query("
        SELECT 
            book_id,
            book_title,
            book_author
        FROM books
        ORDER BY book_title
        ");

        $books = $stmt->fetchAll();

        
        // Fetch Borrowed Books from form
    $stmt = $pdo->query("
        SELECT
            br.borrow_id,
            s.student_first_name,
            s.student_last_name,
            b.book_title,
            br.borrow_date,
            br.borrow_return_date,
        CASE
            WHEN br.borrow_return_date IS NULL THEN 'Not Returned'
            ELSE 'Returned'
            END AS status
        FROM borrow br
        INNER JOIN students s ON br.student_id = s.student_id
        INNER JOIN books b ON br.book_id = b.book_id
        ORDER BY br.borrow_id DESC
    ");

    $borrows = $stmt->fetchAll();

}

// Borrow a Book
if($section==='borrow' && $action==='create' && $_SERVER['REQUEST_METHOD']==='POST') {

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $bookId = (int)($_POST['book_id'] ?? 0);
        
        if($studentId >0 && $bookId >0) {

            // Check if student has an unreturned book
            $stmt = $pdo->prepare("
            SELECT borrow_id FROM borrow
            WHERE student_id = ? 
                AND borrow_return_date IS NULL
            LIMIT 1
            ");

            $stmt->execute([$studentId]);

            $studentBorrow = $stmt->fetch();

            if($studentBorrow){
            $_SESSION['alert'] = "This student cannot borrow another book because a previous book has not been returned.";
            } else {

                // Check if the book is already borrowed.
                $stmt = $pdo->prepare("
                    SELECT borrow_id
                    FROM borrow
                    WHERE book_id = ?
                        AND borrow_return_date IS NULL
                    LIMIT 1
                ");

                $stmt->execute([$bookId]);
                $bookBorrow = $stmt->fetch();

                if($bookBorrow){
                    $_SESSION['alert'] = "This book cannot be borrowed because it has not been returned.";
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO borrow (student_id, book_id)
                            VALUES (?, ?)
                        ");
                        $stmt->execute([$studentId, $bookId]);
                        $_SESSION['alert'] = "Book borrowed successfully.";
                    } catch(PDOException $e){
                        // 23000 = constraint violation.
                        if($e->getCode() === '23000'){
                            $_SESSION['alert'] = "Borrow failed: student or book is no longer available.";
                        } else {
                            throw $e;
                        }
                    }
                }
            }
        }

                header("Location: index.php?section=borrow&action=create");
                exit;
}
    


// Return a Book
if($section === 'borrow' && $action === 'return') {

    $borrowId = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("
        UPDATE borrow
        SET borrow_return_date = CURRENT_TIMESTAMP()
        WHERE borrow_id = ?
        ");

    $stmt->execute([$borrowId]);

    $_SESSION['alert'] = "Book returned successfully.";

    header("Location: index.php?section=borrow");
    exit;
    
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System</title>
    <!-- Material Icons (used by the shared navbar) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Shared theme -->
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
    <div class="page">
        <?php
            // Reuse the same header/nav as the dashboard.
            $currentSection = $section;
            include __DIR__ . '/includes/navbar.php';
        ?>

        <?php if(isset($_SESSION['alert'])): ?>
            <div class="flash">
                <span class="material-icons">info</span>
                <?= htmlspecialchars($_SESSION['alert']) ?>
            </div>
            <?php unset($_SESSION['alert']); ?>
        <?php endif; ?>
    <?php if($section === 'students'):?>
        <div class="section-head">
            <h2 class="page-title">
                <span class="material-icons">people</span>
                Students
            </h2>

            <?php if($action !== 'create' && $action !== 'update'): ?>
                <a class="btn btn-primary" href="index.php?section=students&action=create">
                    <span class="material-icons">add</span>
                    Add student
                </a>
            <?php endif; ?>
        </div>

        <?php if($action==='create'): ?>
            <div class="card form-card">
                <h3>Create Student</h3>

                <form method="POST">
                    <div class="field">
                        <label for="student_first_name">First Name</label>
                        <input  type="text"
                                id="student_first_name"
                                name="student_first_name"
                                required>
                    </div>

                    <div class="field">
                        <label for="student_last_name">Last Name</label>
                        <input  type="text"
                                id="student_last_name"
                                name="student_last_name"
                                required>
                    </div>

                    <div class="field">
                        <label for="student_course">Course</label>
                        <input  type="text"
                                id="student_course"
                                name="student_course"
                                required>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <a class="btn btn-ghost" href="index.php?section=students">Cancel</a>
                    </div>
                </form>
            </div>

        <?php elseif($action==='update'): ?>
            <div class="card form-card">
                <h3>Update Student Info</h3>

                <form method="POST">
                    <div class="field">
                        <label for="student_first_name">First Name</label>
                        <input  type="text"
                                id="student_first_name"
                                name="student_first_name"
                                value="<?=htmlspecialchars($student['student_first_name']) ?>"
                                required>
                    </div>

                    <div class="field">
                        <label for="student_last_name">Last Name</label>
                        <input  type="text"
                                id="student_last_name"
                                name="student_last_name"
                                value="<?=htmlspecialchars($student['student_last_name']) ?>"
                                required>
                    </div>

                    <div class="field">
                        <label for="student_course">Course</label>
                        <input  type="text"
                                id="student_course"
                                name="student_course"
                                value="<?=htmlspecialchars($student['student_course']) ?>"
                                required>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <a class="btn btn-ghost" href="index.php?section=students">Cancel</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="card table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Course</th>
                                <th>Created at</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($students)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state">No students yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($students as $student): ?>
                                    <tr>
                                        <td class="col-id"><?=htmlspecialchars($student['student_id']) ?></td>
                                        <td><?=htmlspecialchars($student['student_first_name']) ?></td>
                                        <td><?=htmlspecialchars($student['student_last_name']) ?></td>
                                        <td><?=htmlspecialchars($student['student_course']) ?></td>
                                        <td><?=htmlspecialchars($student['student_created_at']) ?></td>
                                        <td>
                                            <div class="actions">
                                                <a class="btn btn-sm btn-ghost"
                                                   href="index.php?section=students&action=update&id=<?=$student['student_id']?>">
                                                    Edit
                                                </a>
                                                <a class="btn btn-sm btn-danger-ghost" href="#">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif;?>
    <?php endif; ?>

    <?php if($section === 'books'):?>
        <div class="section-head">
            <h2 class="page-title">
                <span class="material-icons">menu_book</span>
                Books
            </h2>

            <?php if($action !== 'create' && $action !== 'update'): ?>
                <a class="btn btn-primary" href="index.php?section=books&action=create">
                    <span class="material-icons">add</span>
                    Add book
                </a>
            <?php endif; ?>
        </div>

        <?php if($action==='create'): ?>
            <div class="card form-card">
                <h3>Create Book</h3>

                <form method="POST">
                    <div class="field">
                        <label for="book_title">Title</label>
                        <input type="text" id="book_title" name="book_title" required>
                    </div>

                    <div class="field">
                        <label for="book_author">Author</label>
                        <input type="text" id="book_author" name="book_author" required>
                    </div>

                    <div class="field">
                        <label for="book_category">Category</label>
                        <input type="text" id="book_category" name="book_category" required>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <a class="btn btn-ghost" href="index.php?section=books">Cancel</a>
                    </div>
                </form>
            </div>

        <?php elseif($action==='update'): ?>
            <div class="card form-card">
                <h3>Update Book Info</h3>

                <form method="POST">
                    <div class="field">
                        <label for="book_title">Title</label>
                        <input type="text"
                               id="book_title"
                               name="book_title"
                               value="<?=htmlspecialchars($book['book_title']) ?>"
                               required>
                    </div>

                    <div class="field">
                        <label for="book_author">Author</label>
                        <input type="text"
                               id="book_author"
                               name="book_author"
                               value="<?=htmlspecialchars($book['book_author']) ?>"
                               required>
                    </div>

                    <div class="field">
                        <label for="book_category">Category</label>
                        <input type="text"
                               id="book_category"
                               name="book_category"
                               value="<?=htmlspecialchars($book['book_category']) ?>"
                               required>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Save</button>
                        <a class="btn btn-ghost" href="index.php?section=books">Cancel</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="card table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Created at</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($books)): ?>
                                <tr>
                                    <td colspan="6" class="empty-state">No books yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($books as $book): ?>
                                    <tr>
                                        <td class="col-id"><?=htmlspecialchars($book['book_id']) ?></td>
                                        <td><?=htmlspecialchars($book['book_title']) ?></td>
                                        <td><?=htmlspecialchars($book['book_author']) ?></td>
                                        <td><?=htmlspecialchars($book['book_category']) ?></td>
                                        <td><?=htmlspecialchars($book['book_created_at']) ?></td>
                                        <td>
                                            <div class="actions">
                                                <a class="btn btn-sm btn-ghost"
                                                   href="index.php?section=books&action=update&id=<?=$book['book_id']?>">
                                                    Edit
                                                </a>
                                                <a class="btn btn-sm btn-danger-ghost" href="#">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($section === 'borrow'):?>
        <div class="section-head">
            <h2 class="page-title">
                <span class="material-icons">swap_horiz</span>
                Borrow
            </h2>

            <?php if($action !== 'create'): ?>
                <a class="btn btn-primary" href="index.php?section=borrow&action=create">
                    <span class="material-icons">add</span>
                    Borrow a book
                </a>
            <?php endif; ?>
        </div>

        <?php if ($action==='create'): ?>
            <div class="card form-card">
                <h3>Borrow a Book</h3>

                <form method="POST">
                    <div class="field">
                        <label for="student_id">Student</label>
                        <select id="student_id" name="student_id" required>
                            <option value="">-- Select Student --</option>
                            <?php foreach($students as $student): ?>
                                <option value="<?=(int) $student['student_id'] ?>">
                                    <?=htmlspecialchars($student['student_first_name'] . ' ' . $student['student_last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="book_id">Book</label>
                        <select id="book_id" name="book_id" required>
                            <option value="">-- Select Book --</option>
                            <?php foreach($books as $book): ?>
                                <option value="<?=(int) $book['book_id'] ?>">
                                    <?=htmlspecialchars($book['book_title'] . ' - ' . $book['book_author']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Borrow</button>
                        <a class="btn btn-ghost" href="index.php?section=borrow">Cancel</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="card table-card">
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Book</th>
                                <th>Borrow Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($borrows)): ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No borrow records yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($borrows as $borrow): ?>
                                    <tr>
                                        <td class="col-id"><?=htmlspecialchars($borrow['borrow_id']) ?></td>
                                        <td><?=htmlspecialchars($borrow['student_first_name'] . ' ' . $borrow['student_last_name']) ?></td>
                                        <td><?=htmlspecialchars($borrow['book_title']) ?></td>
                                        <td><?=htmlspecialchars($borrow['borrow_date']) ?></td>
                                        <td><?=htmlspecialchars($borrow['borrow_return_date'] ?? '-') ?></td>
                                        <td>
                                            <span class="chip <?= $borrow['borrow_return_date'] === null ? 'chip-borrowed' : 'chip-returned' ?>">
                                                <span class="material-icons">
                                                    <?= $borrow['borrow_return_date'] === null ? 'schedule' : 'check_circle' ?>
                                                </span>
                                                <?=htmlspecialchars($borrow['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($borrow['borrow_return_date'] === null): ?>
                                                <a class="btn btn-sm btn-success-ghost"
                                                   href="index.php?section=borrow&action=return&id=<?=$borrow['borrow_id']?>">
                                                    Return
                                                </a>
                                            <?php else: ?>
                                                <span class="muted">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    </div><!-- /.page -->
</body>
</html>
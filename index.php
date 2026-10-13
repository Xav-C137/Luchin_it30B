<?php

// Database connection
$host = 'localhost';
$db = 'it30_lab_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host; dbname=$db; charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try{
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch(PDOException $e){
    die("Database connection failed" . $e->getMessage());
}

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
    
    $studentId = (int) ($_GET['id']) ?? 00;
    
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
        FROM book
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
                INSERT INTO book(
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
            UPDATE book
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
        FROM book
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
        FROM book
        ORDER BY book_title
        ");

        $books = $stmt->fetchAll();

    // Fetch Borrowed Books from form
    // $stmt = $pdo->query("
    //     SELECT 
    //         b.borrow_id,
    //         s.student_first_name,
    //         s.student_last_name,
    //         bk.book_title,
    //         b.borrow_date,
    //         b.return_date,
    //         CASE
    //             WHEN b.return_date IS NULL THEN 'Not Returned'
    //             ELSE 'Returned'
    //         END AS status
    //     FROM borrow b
    //     JOIN students s ON b.student_id = s.student_id
    //     JOIN book bk ON b.book_id = bk.book_id
    //     ORDER BY b.borrow_id DESC
    //     ");
        
    //     $borrows = $stmt->fetchAll();
}

// Create Borrow
if($section==='borrow' && $action==='create'){

    if($_SERVER['REQUEST_METHOD']==='POST'){

        $studentId = (int)$_POST['student_id'] ?? 00;
        $bookId = (int)$_POST['book_id'] ?? 00;
        
        if($studentId >0 && $bookId >0) {

            // Check if student has an unreturned book
            $stmt = $pdo->prepare("
                SELECT borrow_id
                FROM borrow
                WHERE student_id = ? 
                    AND return_date IS NULL
                LIMIT 1
            ");

            $stmt->execute([$studentId]);

            $studentBorrow = $stmt->fetch();

            if($studentBorrow) {
                $_SESSION['alert'] = "This student cannot borrow another book because a previous book has not been returned ";
                header("Location: index.php?section=borrow&action=create");
                exit;
            } else {
                    // Check if book already borrowed
                    $stmt = $pdo->prepare("
                        SELECT borrow_id
                        FROM borrow
                        WHERE book_id = ? 
                            AND return_date IS NULL
                        LIMIT 1
                    ");

                    $stmt->execute([$borrowId]);

                    $bookBorrow = $stmt->fetch();

                    if ($bookBorrow) {
                        $_SESSION['alert'] = "This book cannot be borrowed because it has not been returned.";
                    } else {

                        // Create borrow record
                        $stmt = $pdo->prepare("
                            INSERT INTO borrow (
                                student_id,
                                book_id,
                            )
                            VALUES (?,?)
                        ");

                        $stmt->execute([
                            $studentId,
                            $bookId
                        ]);

                        $_SESSION['alert'] = "Book borrowed successfully.";

                    }
                }
                
                header("Location: index.php?section=borrow&action=create");
                exit;
            }
        }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System</title>
</head>
<body>
    <h1>Simple Library System</h1>
    <nav>
        <a href="index.php?section=students">Students</a>
        <a href="index.php?section=books">Books</a>
        <a href="index.php?section=borrow">Borrow</a>
    </nav>
    <hr>
    <?php if($section === 'students'):?>
        <h1>Students</h1>

        <p>
            <a href="index.php?section=students&action=create">
                Add student
            </a>
        </p>

        <?php if($action==='create'): ?>
            <h2>Create Student</h2>
            

            <form method="POST">

                <p>
                <label>First Name:</label>
                <br>
                <input  type="text"
                        name="student_first_name"
                        required
                />
                </p>
                <p>
                <label>Last Name:</label>
                <br>
                <input  type="text"
                        name="student_last_name"
                        required
                />
                </p>
                <p>
                <label>Course:</label>
                <br>
                <input  type="text"
                        name="student_course"
                        required
                />
                </p>

                <button type="submit">
                    Save
                </button>

                <a href="index.php?section=students">
                    Cancel
                </a>
            </form>
        
        <?php elseif($action==='update'): ?>
            <h2>Update Student Info</h2>
            
            <form method="POST">

                <p>
                <label>First Name:</label>
                <br>
                <input  type="text"
                        name="student_first_name"
                        value="<?=htmlspecialchars($student['student_first_name']) ?>"
                        required
                />
                </p>
                <p>
                <label>Last Name:</label>
                <br>
                <input  type="text"
                        name="student_last_name"
                        value="<?=htmlspecialchars($student['student_last_name']) ?>"
                        required
                />
                </p>
                <p>
                <label>Course:</label>
                <br>
                <input  type="text"
                        name="student_course"
                        value="<?=htmlspecialchars($student['student_course']) ?>"
                        required
                />
                </p>

                <button type="submit">
                    Save
                </button>

                <a href="index.php?section=students">
                    Cancel
                </a>
            </form>


        <?php else: ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Course</th>
                    <th>Create at</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $student): ?>
                    <tr>
                        <td><?=htmlspecialchars($student['student_id']) ?></td>
                        <td><?=htmlspecialchars($student['student_first_name']) ?></td>
                        <td><?=htmlspecialchars($student['student_last_name']) ?></td>
                        <td><?=htmlspecialchars($student['student_course']) ?></td>
                        <td><?=htmlspecialchars($student['student_created_at']) ?></td>
                        <td>
                            <a href="index.php?section=students&action=update&id=<?=$student['student_id']?> ">
                                Edit
                            </a>
                                |
                                <a href="#">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach?>
            </tbody>
        </table>
        <?php endif;?>
    <?php endif; ?>

    <?php if($section === 'books'):?>
        <h1>Books</h1>

        <p>
            <a href="index.php?section=books&action=create">
                Add book
            </a>
        </p>

        <?php if($action==='create'): ?>
            <h2>Create Book</h2>

            <form method="POST">
                <p>
                    <label>Title:</label>
                    <br>
                    <input type="text" name="book_title" required />
                </p>
                <p>
                    <label>Author:</label>
                    <br>
                    <input type="text" name="book_author" required />
                </p>
                <p>
                    <label>Category:</label>
                    <br>
                    <input type="text" name="book_category" required />
                </p>

                <button type="submit">Save</button>
                <a href="index.php?section=books">Cancel</a>
            </form>

        <?php elseif($action==='update'): ?>
            <h2>Update Book Info</h2>

            <form method="POST">
                <p>
                    <label>Title:</label>
                    <br>
                    <input type="text"
                            name="book_title"
                            value="<?=htmlspecialchars($book['book_title']) ?>"
                            required />
                </p>
                <p>
                    <label>Author:</label>
                    <br>
                    <input type="text"
                            name="book_author"
                            value="<?=htmlspecialchars($book['book_author']) ?>"
                            required />
                </p>
                <p>
                    <label>Category:</label>
                    <br>
                    <input type="text"
                            name="book_category"
                            value="<?=htmlspecialchars($book['book_category']) ?>"
                            required />
                </p>

                <button type="submit">Save</button>
                <a href="index.php?section=books">Cancel</a>
            </form>

        <?php else: ?>
            <table border="1">
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
                    <?php foreach($books as $book): ?>
                        <tr>
                            <td><?=htmlspecialchars($book['book_id']) ?></td>
                            <td><?=htmlspecialchars($book['book_title']) ?></td>
                            <td><?=htmlspecialchars($book['book_author']) ?></td>
                            <td><?=htmlspecialchars($book['book_category']) ?></td>
                            <td><?=htmlspecialchars($book['book_created_at']) ?></td>
                            <td>
                                <a href="index.php?section=books&action=update&id=<?=$book['book_id']?>">
                                    Edit
                                </a>
                                |
                                <a href="#">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($section === 'borrow'):?>
        <h1>Borrow</h1>

        <p>
            <a href="index.php?section=borrow&action=create">
                Borrow a book
            </a>
        </p>

        <?php if ($action==='create'): ?>
            <h2>Borrow a Book</h2>

            <form method="POST">
                <label>Student</label>
                <br>
                <select name=student_id required>
                    <option value="">
                        -- Select Student --    
                    </option>
                    <?php foreach($students as $student): ?>
                        <option value="<?=$student['student_id'] ?>">
                            <?=htmlspecialchars($student['student_first_name'] . ' ' . $student['student_last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <br><br>

                <label>Book</label>
                <br>
                <select name=book_id required>
                    <option value="">
                        -- Select Book --    
                    </option>
                    <?php foreach($books as $book): ?>
                        <option value="<?=$book['book_id'] ?>">
                            <?=htmlspecialchars($book['book_title']) . ' ' . $book['book_author'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <br>

                <button type="submit">
                    Borrow
                </button>

                <a href="index.php?section=borrow">
                    Cancel
                </a>

            </form>

            <?php else: ?>
            <!-- <table border="1">
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
                    <?php foreach($borrows as $borrow): ?>
                        <tr>
                            <td><?=htmlspecialchars($borrow['borrow_id']) ?></td>
                            <td><?=htmlspecialchars($borrow['student_first_name'] . ' ' . $borrow['student_last_name']) ?></td>
                            <td><?=htmlspecialchars($borrow['book_title']) ?></td>
                            <td><?=htmlspecialchars($borrow['borrow_date']) ?></td>
                            <td><?=htmlspecialchars($borrow['return_date'] ?? 'Not returned') ?></td>
                            <td><?=htmlspecialchars($borrow['status']) ?></td>
                            <td>
                                <a href="index.php?section=borrow&action=update&id=<?=$borrow['borrow_id']?>">
                                    Edit
                                </a>
                                |
                                <a href="#">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table> -->
            <?php endif; ?>

            

    <?php endif; ?>

</body>
<?php if(isset($_SESSION['alert'])): ?>
    
    <script>
        alert( <?= json_encode($_SESSION['alert']) ?> );
    </script>
    
    <?php unset($_SESSION['alert']); ?>

<?php endif; ?>

</html>
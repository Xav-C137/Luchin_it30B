CREATE TABLE students(
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_first_name VARCHAR(255) NOT NULL,
    student_last_name VARCHAR(255) NOT NULL,
    student_course VARCHAR(255) NOT NULL
);

CREATE TABLE book(
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    book_title VARCHAR(100) NOT NULL,
    book_author VARCHAR(100) NOT NULL,
    book_category VARCHAR(50) NOT NULL,
    book_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE borrow (
    borrow_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    borrow_return_date TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_student FOREIGN KEY (student_id) REFERENCES students(student_id),
    CONSTRAINT fk_book FOREIGN KEY (book_id) REFERENCES book(book_id)
);

    SELECT br.borrow_id, s.student_id,
        CONCAT(s.student_first_name, ' ', s.student_last_name) AS student_name,
        b.book_title, b.book_author, b.book_category,
        br.borrow_date 
    FROM borrow br
        JOIN students s ON br.student_id = s.student_id
        JOIN book b ON br.book_id = b.book_id
    ORDER BY br.borrow_date DESC;

--Alter the borrow_return_date column to allow NULL values
ALTER TABLE borrow
MODIFY borrow_return_date TIMESTAMP NULL DEFAULT NULL;

UPDATE borrow
SET borrow_return_date = NULL
WHERE borrow_return_date = '2026-08-25 08:52:01 ';

--Return a book by updating the borrow_return_date column with the current timestamp

UPDATE borrow
SET borrow_return_date = CURRENT_TIMESTAMP
    WHERE borrow_id = 4 AND borrow_return_date IS NULL;

-- Set the return date to the current timestamp

SELECT br.borrow_id, s.student_id,
        CONCAT(s.student_first_name, ' ', s.student_last_name) AS student_name,
        b.book_title, b.book_author, b.book_category,
        br.borrow_date, br.borrow_return_date
    FROM borrow br
        JOIN students s ON br.student_id = s.student_id
        JOIN book b ON br.book_id = b.book_id
        WHERE br.borrow_return_date IS NOT NULL
    ORDER BY br.borrow_date DESC;

     
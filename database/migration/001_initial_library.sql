-- #1 students table

CREATE TABLE IF NOT EXIST students (
    -- Primary key for the students table
    student_id INT AUTO_INCREMENT PRIMARY KEY,

    -- Student name
    student_first_name VARCHAR(50) NOT NULL,
    student_last_name VARCHAR(50) NOT NULL,
    -- Student course 
    student_course VARCHAR(50) NOT NULL,
    -- Student created at timestamp
    student_created_at TIMESTAMP NOT NULL 
    DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

-- #2 book table
CREATE TABLE IF NOT EXISTS books (
    -- Primary key for the books table
    book_id INT AUTO_INCREMENT PRIMARY KEY,

    -- Book details
    book_title VARCHAR(100) NOT NULL,
    book_author VARCHAR(100) NOT NULL,
    book_category VARCHAR(50) NOT NULL,

    -- Book created at timestamp
    book_created_at TIMESTAMP NOT NULL
    DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

-- #3 borrow table 
CREATE TABLE IF NOT EXISTS borrow (
    -- Primary key for the borrow table
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,

    -- Foreign keys for the students and books tables
    student_id INT NOT NULL,
    book_id INT NOT NULL,

    -- Borrow timestamp not null by default
    borrow_date TIMESTAMP NOT NULL 
    DEFAULT CURRENT_TIMESTAMP,

    -- Borrow return date null by default
    borrow_return_date TIMESTAMP NULL 
    DEFAULT NULL,

    -- Foreign key constraints
    CONSTRAINT fk_borrow_student 
    FOREIGN KEY (student_id) 
    REFERENCES students(student_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,

    CONSTRAINT fk_borrow_book 
    FOREIGN KEY (book_id) 
    REFERENCES books(book_id)
    ON UPDATE CASCADE
    ON DELETE RESTRICT

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

-- Insert statement #1: Insert Students
INSERT INTO students (
    student_first_name,
    student_last_name,
    student_course
) VALUES
    ('EARL TROY', 'LUCHIN', 'BSIT'),
    ('RAEIANAH', 'BAHAYNON', 'BSED'),
    ('SOVA', 'LOCHEN', 'BSIS'),
    ('JAMES', 'LEBRON', 'BSMT');

-- Insert statement #2: Insert Books
INSERT INTO books (
    book_title,
    book_author,
    book_category
) VALUES
    ('Atomic Habits', 'James Clear', 'Non-fiction'),
    ('Rich Dad Poor Dad', 'Robert Kiyosaki', 'Personal Finance'),
    ('48 Laws of Power', 'Robert Greene', 'Strategic Advice');


-- Insert statement #3: Insert Borrow
INSERT INTO borrow (
    student_id,
    book_id
) VALUES
    (1, 2),
    (2, 1),
    (3, 3);
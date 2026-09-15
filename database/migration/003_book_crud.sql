-- book SQL#1 : select all books
SELECT * FROM book;

-- book SQL#2 : select book in asc order by id
SELECT * FROM book ORDER BY book_id ASC;

-- book SQL#3 : select book in desc order by id
SELECT * FROM book ORDER BY book_id DESC;

-- book SQL#4 : select book in asc order by title
SELECT * FROM book ORDER BY book_title ASC;

-- book SQL#5 : select book in desc order by title
SELECT * FROM book ORDER BY book_title DESC;


-- book SQL#6 : select book in asc order by author
SELECT * FROM book ORDER BY book_author ASC;

-- book SQL#7 : select book in desc order by author
SELECT * FROM book ORDER BY book_author DESC;

-- you can modify displayed column by selecting specific columns
-- after SELECT command, for example: 
-- book SQL#8 : display all book title and author in asc order by first_name

SELECT book_title, 
       book_author,
       book_category
       FROM book;
       ORDER BY book_title ASC;

-- book SQL#9 : LIMIT 1
SELECT book_title, 
       book_author,
        book_category
       FROM book
       ORDER BY book_title ASC
       LIMIT 1;  
    
-- book SQL#10 : select book based on id
SELECT book_title,
       book_author,
       book_category
       FROM book
       WHERE book_id = 1
       LIMIT 1;

-- book SQL#11 : update student name a student based on id
UPDATE book
SET book_title = 'A_Test',
    book_author = 'A_Test',
    book_category = 'A_Test'
WHERE book_id = 2;
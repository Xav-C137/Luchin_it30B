-s- student SQL#1 : select all students
SELECT * FROM students;

-- students SQL#2 : select student in asc order by id
SELECT * FROM students ORDER BY student_id ASC;

-- students SQL#3 : select student in desc order by id
SELECT * FROM students ORDER BY student_id DESC;

-- students SQL#4 : select student in asc order by last_name
SELECT * FROM students ORDER BY student_last_name ASC;

-- students SQL#5 : select student in desc order by last_name
SELECT * FROM students ORDER BY student_last_name DESC;

-- students SQL#6 : select student in asc order by first_name
SELECT * FROM students ORDER BY student_first_name ASC;

-- students SQL#7 : select student in desc order by first_name
SELECT * FROM students ORDER BY student_first_name DESC;

-- you can modify displayed column by selecting specific columns
-- after SELECT command, for example: 
-- students SQL#8 : display all student first_name and last_name in asc order by first_name

SELECT student_first_name, 
       student_last_name 
       FROM students;
       ORDER BY student_first_name ASC;

-- student SQL#9 : LIMIT 1
SELECT student_first_name, 
       student_last_name 
       FROM students
       ORDER BY student_first_name ASC
       LIMIT 1;  
    
-- student SQL#10 : select student based on id
SELECT student_first_name,
       student_last_name
       FROM students
       WHERE student_id = 1
       LIMIT 1;

-- student SQL#11 : update student name a student based on id
UPDATE students
SET student_first_name = 'A_Test',
    student_last_name = 'A_Test'
WHERE student_id = 2;
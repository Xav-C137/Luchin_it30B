CREATE DATABASE <database_name>;
SHOW DATABASES;
CONNECT <database_name>;
CREATE TABLE <table_name_in_plural>();
INSERT INTO <table_name_in_plural>      
        (columns) VALUES
        (values);



# Utility Commands
\! cls
mysqldump -u root -p --database_name library_db < G:\dev\it30B\backups\date_db_name.sql

-- Create the database and user
CREATE DATABASE IF NOT EXISTS studentdb;
CREATE USER IF NOT EXISTS 'devops'@'172.31.16.214' IDENTIFIED BY 'Buraimoh7';

-- Grant privileges to the user for remote access
GRANT ALL PRIVILEGES ON studentdb.* TO 'devops'@'172.31.16.214';
FLUSH PRIVILEGES;

-- (Optional) Create table and insert data
USE studentdb;

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100)
);

INSERT INTO students (name, email) VALUES
  ('Alice Smith', 'alice@example.com'),
  ('Bob Johnson', 'bob@example.com');

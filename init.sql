-- Create the database and user
CREATE DATABASE IF NOT EXISTS studentdb;

-- Create devops user for both local and remote access
CREATE USER IF NOT EXISTS 'devops'@'localhost' IDENTIFIED BY 'devops123';
CREATE USER IF NOT EXISTS 'devops'@'%' IDENTIFIED BY 'devops123';

-- Grant privileges to the user for both local and remote access
GRANT ALL PRIVILEGES ON studentdb.* TO 'devops'@'localhost';
GRANT ALL PRIVILEGES ON studentdb.* TO 'devops'@'%';
FLUSH PRIVILEGES;

-- Use the database
USE studentdb;

-- Create table and insert data
CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100)
);

INSERT INTO students (name, email) VALUES
  ('Alice Smith', 'alice@example.com'),
  ('Bob Johnson', 'bob@example.com');

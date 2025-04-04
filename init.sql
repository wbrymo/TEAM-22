-- Create the database
CREATE DATABASE IF NOT EXISTS studentdb;

-- Create the user for local and remote (optional)
CREATE USER IF NOT EXISTS 'devops'@'localhost' IDENTIFIED BY 'password';
CREATE USER IF NOT EXISTS 'devops'@'%' IDENTIFIED BY 'password';

-- Grant full privileges
GRANT ALL PRIVILEGES ON studentdb.* TO 'devops'@'localhost';
GRANT ALL PRIVILEGES ON studentdb.* TO 'devops'@'%';
FLUSH PRIVILEGES;

-- Use the DB
USE studentdb;

-- Create table and sample data
CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100)
);

INSERT INTO students (name, email) VALUES
  ('Alice Smith', 'alice@example.com'),
  ('Bob Johnson', 'bob@example.com');

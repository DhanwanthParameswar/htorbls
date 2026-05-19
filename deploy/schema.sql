-- HTOR BLS (Balvihar Library System) - demo database schema
CREATE DATABASE IF NOT EXISTS library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS booklist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bookId VARCHAR(50) NOT NULL UNIQUE,
  bookName VARCHAR(255) NOT NULL,
  bookCategory VARCHAR(255) DEFAULT NULL,
  additionalNotes TEXT DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS librarylog (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patronName VARCHAR(255) NOT NULL,
  contactInfo VARCHAR(50) DEFAULT NULL,
  bookId VARCHAR(50) NOT NULL,
  issueDate DATE NOT NULL,
  dueDate DATE NOT NULL,
  fineAmount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  UNIQUE KEY unique_active_book (bookId),
  CONSTRAINT fk_librarylog_book FOREIGN KEY (bookId) REFERENCES booklist(bookId) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS libraryarchive (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patronName VARCHAR(255) NOT NULL,
  contactInfo VARCHAR(50) DEFAULT NULL,
  bookId VARCHAR(50) NOT NULL,
  issueDate DATE NOT NULL,
  dueDate DATE NOT NULL,
  returnDate DATE NOT NULL,
  fineAmountPaid DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB;

-- Demo user password is set by deploy.sh (default: demo / demo123)
-- Demo books and a sample checkout are inserted by deploy.sh after user creation.

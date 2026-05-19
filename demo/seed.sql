-- Daily demo reset for lms.dhanwanth.com
-- Generic community library snapshot (reseeded by reseed-demo-db.sh)
USE library;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE libraryarchive;
TRUNCATE TABLE librarylog;
TRUNCATE TABLE booklist;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Login: demo / DemoPass123 (user row inserted by reseed-demo-db.sh)

INSERT INTO booklist (bookId, bookName, bookCategory, additionalNotes) VALUES
  ('FIC-1001', 'The Midnight Library', 'Fiction', 'Bestseller — paperback'),
  ('FIC-1002', 'Project Hail Mary', 'Fiction', 'Sci-fi'),
  ('FIC-1003', 'Lessons in Chemistry', 'Fiction', 'Book club pick March'),
  ('FIC-1004', 'The Thursday Murder Club', 'Fiction', 'Mystery series #1'),
  ('FIC-1005', 'Where the Crawdads Sing', 'Fiction', 'Damaged jacket — still readable'),
  ('NF-2001', 'Sapiens: A Brief History of Humankind', 'Non-Fiction', '2 copies on shelf; this is copy A'),
  ('NF-2002', 'Atomic Habits', 'Non-Fiction', 'Self-help'),
  ('NF-2003', 'The Body Keeps the Score', 'Non-Fiction', 'Psychology / health'),
  ('NF-2004', 'Educated', 'Non-Fiction', 'Memoir'),
  ('YA-3001', 'The Hunger Games', 'Young Adult', 'Paperback'),
  ('YA-3002', 'They Both Die at the End', 'Young Adult', 'LGBTQ+ fiction'),
  ('YA-3003', 'Six of Crows', 'Young Adult', 'Fantasy'),
  ('REF-4001', 'Merriam-Webster Collegiate Dictionary', 'Reference', 'Library use only — do not loan'),
  ('REF-4002', 'World Almanac 2025', 'Reference', 'Updated annually'),
  ('CHI-5001', 'The Very Hungry Caterpillar', 'Children', 'Board book — picture books section'),
  ('CHI-5002', 'Charlotte''s Web', 'Children', 'Classic'),
  ('CHI-5003', 'Diary of a Wimpy Kid', 'Children', 'Volume 1'),
  ('MYS-6001', 'The Guest List', 'Mystery', 'Thriller'),
  ('MYS-6002', 'The Silent Patient', 'Mystery', 'On hold list — display returned copies here'),
  ('BIO-7001', 'Becoming', 'Biography', 'Michelle Obama'),
  ('BIO-7002', 'Steve Jobs', 'Biography', 'Walter Isaacson');

-- Active checkouts (librarylog)
INSERT INTO librarylog (patronName, contactInfo, bookId, issueDate, dueDate, fineAmount) VALUES
  ('Emily Hartwell', '(555) 234-8891', 'FIC-1001', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 0.00),
  ('Marcus Webb', '(555) 882-1044', 'NF-2002', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 0.00),
  ('Sofia Delgado', '(555) 441-2200', 'YA-3001', DATE_SUB(CURDATE(), INTERVAL 12 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 4.00),
  ('James O''Connor', '(555) 109-7733', 'FIC-1004', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), 0.00),
  ('Aisha Khan', '(555) 667-5521', 'CHI-5002', DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY), 0.00),
  ('Tyler Brennan', '(555) 318-9902', 'MYS-6001', DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_SUB(CURDATE(), INTERVAL 6 DAY), 6.00);

-- Recently returned (libraryarchive) — last ~3 months
INSERT INTO libraryarchive (patronName, contactInfo, bookId, issueDate, dueDate, returnDate, fineAmountPaid) VALUES
  ('Rachel Nguyen', '(555) 204-1188', 'FIC-1003', DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 14 DAY), DATE_SUB(CURDATE(), INTERVAL 13 DAY), 0.00),
  ('David Park', '(555) 771-3300', 'NF-2001', DATE_SUB(CURDATE(), INTERVAL 35 DAY), DATE_SUB(CURDATE(), INTERVAL 21 DAY), DATE_SUB(CURDATE(), INTERVAL 19 DAY), 2.00),
  ('Olivia Martinez', '(555) 556-9021', 'YA-3002', DATE_SUB(CURDATE(), INTERVAL 42 DAY), DATE_SUB(CURDATE(), INTERVAL 28 DAY), DATE_SUB(CURDATE(), INTERVAL 27 DAY), 0.00),
  ('Kevin Sullivan', '(555) 890-4412', 'FIC-1002', DATE_SUB(CURDATE(), INTERVAL 50 DAY), DATE_SUB(CURDATE(), INTERVAL 36 DAY), DATE_SUB(CURDATE(), INTERVAL 34 DAY), 0.00),
  ('Priya Nair', '(555) 123-6677', 'BIO-7001', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 46 DAY), DATE_SUB(CURDATE(), INTERVAL 44 DAY), 0.00),
  ('Chris Lambert', '(555) 445-2099', 'MYS-6002', DATE_SUB(CURDATE(), INTERVAL 18 DAY), DATE_SUB(CURDATE(), INTERVAL 4 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 0.00),
  ('Hannah Brooks', '(555) 332-8840', 'CHI-5003', DATE_SUB(CURDATE(), INTERVAL 22 DAY), DATE_SUB(CURDATE(), INTERVAL 8 DAY), DATE_SUB(CURDATE(), INTERVAL 7 DAY), 0.00),
  ('Michael Torres', '(555) 908-1155', 'NF-2004', DATE_SUB(CURDATE(), INTERVAL 75 DAY), DATE_SUB(CURDATE(), INTERVAL 61 DAY), DATE_SUB(CURDATE(), INTERVAL 58 DAY), 5.00),
  ('Jenny Walsh', '(555) 267-4401', 'FIC-1005', DATE_SUB(CURDATE(), INTERVAL 40 DAY), DATE_SUB(CURDATE(), INTERVAL 26 DAY), DATE_SUB(CURDATE(), INTERVAL 25 DAY), 0.00),
  ('Andre Williams', '(555) 601-7730', 'YA-3003', DATE_SUB(CURDATE(), INTERVAL 90 DAY), DATE_SUB(CURDATE(), INTERVAL 76 DAY), DATE_SUB(CURDATE(), INTERVAL 72 DAY), 8.00);

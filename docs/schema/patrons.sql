-- Patron registry for HTOR BLS
-- Prefer: php scripts/migrate_patrons.php (applies schema + data migration)
-- Or run this file manually after backup.

CREATE TABLE IF NOT EXISTS patrons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patronName VARCHAR(255) NOT NULL,
  contactInfo VARCHAR(255) NOT NULL DEFAULT '',
  phoneNormalized VARCHAR(20) NULL,
  notes TEXT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_patron_name (patronName),
  INDEX idx_phone_normalized (phoneNormalized),
  INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

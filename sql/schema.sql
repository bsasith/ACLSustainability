-- Create database
CREATE DATABASE IF NOT EXISTS simple_auth
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE simple_auth;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(60) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username)
) ENGINE=InnoDB;

-- Example: insert a user (replace HASH with a bcrypt hash)
-- INSERT INTO users (username, password_hash) VALUES ('admin', '$2y$10$...');

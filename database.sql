-- ConnectZW database (same structure as before; safe for a fresh install)
CREATE DATABASE IF NOT EXISTS connectzw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE connectzw;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer','business','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS businesses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT NULL,
  business_name VARCHAR(150) NOT NULL,
  description TEXT,
  location VARCHAR(150),
  phone VARCHAR(30),
  logo VARCHAR(255) NULL,
  email VARCHAR(150) NULL,
  website VARCHAR(200) NULL,
  city VARCHAR(80) NULL,
  province VARCHAR(60) NULL,
  opens TIME NULL,
  closes TIME NULL,
  open_days VARCHAR(10) NOT NULL DEFAULT 'daily',
  is_verified TINYINT(1) NOT NULL DEFAULT 0,
  views INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS business_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  image VARCHAR(255) NOT NULL,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS business_services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  service_name VARCHAR(120) NOT NULL,
  price VARCHAR(60) NULL,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS business_stats (
  business_id INT NOT NULL, day DATE NOT NULL,
  views INT NOT NULL DEFAULT 0, calls INT NOT NULL DEFAULT 0, whatsapp INT NOT NULL DEFAULT 0,
  emails INT NOT NULL DEFAULT 0, websites INT NOT NULL DEFAULT 0,
  PRIMARY KEY (business_id, day),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL UNIQUE,
  excerpt VARCHAR(300) NULL, content MEDIUMTEXT NOT NULL, image VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(150) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_id INT NOT NULL,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL,
  comment TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_review_per_user (business_id, user_id),
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  business_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_favorite (user_id, business_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  subject VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (category_name) VALUES
('Accounting & Bookkeeping'),('Agricultural Supplies'),('Agriculture & Farming'),
('Appliance Repairs'),('Auto Electrical Services'),('Auto Parts'),
('Automotive & Mechanics'),('Bakeries & Confectionery'),('Barbers & Salons'),
('Electricians'),('Plumbers'),('Restaurants & Cafes');

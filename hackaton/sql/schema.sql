-- Cria DB e tabelas. Após importar, rode scripts/set_password.php para definir senha do usuário seed.
CREATE DATABASE IF NOT EXISTS financehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE financehub;

-- users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  color VARCHAR(20) DEFAULT '#cccccc',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- transactions
CREATE TABLE IF NOT EXISTS transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  date DATETIME NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  category_id INT,
  merchant VARCHAR(255),
  icon VARCHAR(8),
  color VARCHAR(20),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- cashback
CREATE TABLE IF NOT EXISTS cashback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('available','used') DEFAULT 'available',
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- seed mínimo (senha a ser definida por script PHP)
INSERT INTO users (name,email,password,phone) VALUES
('Maria Silva','maria@email.com','1234567', '11999999999');

INSERT INTO categories (user_id,name,color) VALUES
(1,'Alimentação','#f6a623'),(1,'Transporte','#19b2c9'),(1,'Compras','#1fbf6b'),(1,'Saúde','#ff5c5c'),(1,'Entretenimento','#7b61ff'),(1,'Outros','#8a8f99');

INSERT INTO transactions (user_id,date,amount,category_id,merchant,icon,color) VALUES
(1,NOW()-INTERVAL 1 DAY,-45.90,1,'iFood','odor','#fff4ea'),
(1,NOW()-INTERVAL 1 DAY,-120.00,2,'Posto Shell','⛽','#eef7ff'),
(1,NOW()-INTERVAL 2 DAY,-89.99,3,'Amazon','🛒','#f0fff4'),
(1,NOW()-INTERVAL 3 DAY,1500.00,NULL,'Salário','💼','#e8f7ff');

INSERT INTO cashback (user_id,amount,status) VALUES (1,127.50,'available');
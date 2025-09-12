```sql
CREATE DATABASE IF NOT EXISTS cebelarstvo_cigoj;
USE cebelarstvo_cigoj;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert test admin
INSERT INTO admin_users (username, password_hash) 
VALUES ('admin', '$2y$10$J4o8/.jGJt4vB7R7hNQ0L.Q5Qd8RZ1Z1d5Gz0V1W5gX6t5i2b1V1S2');

-- Insert sample products
INSERT INTO products (name, description, price, stock, image_url) VALUES
('Cvetni prah 50g', 'Naravni cvetni prah, bogat s proteini in vitamini', 5.00, 100, 'https://static.photos/nature/320x240/101'),
('Balzam za ustnice iz čebeljega voska', 'Neguje in ščiti ustnice', 2.50, 50, 'https://static.photos/nature/320x240/102'),
('Med ajdov', 'Visokokakovosten med iz ajdovega cveta', 7.50, 30, 'https://static.photos/nature/320x240/103');
```

To complete the setup, you'll need to:
1. Install required dependencies via Composer:
   ```
   composer require firebase/php-jwt
   ```

2. Set up a web server (like Apache or Nginx) to serve these PHP files
3. Create the database using the schema.sql file
4. Configure the database credentials in config.php

The backend provides:
- Admin authentication with JWT
- CRUD operations for products
- Order processing
- Customer management
- Sales reporting
- Secure API endpoints

For production use, make sure to:
1. Change the default admin credentials
2. Use HTTPS
3. Add input validation and sanitization
4. Implement rate limiting
5. Regularly backup your database
6. Keep the system updated
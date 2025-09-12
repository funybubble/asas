-- SQLite schema
-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    price REAL NOT NULL,
    stock INTEGER NOT NULL DEFAULT 0,
    image_url TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    address TEXT,
    phone TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    customer_name TEXT NOT NULL,
    customer_email TEXT NOT NULL,
    customer_address TEXT NOT NULL,
    customer_phone TEXT,
    subtotal REAL,
    shipping_cost REAL,
    shipping_method TEXT,
    total_amount REAL NOT NULL,
    status TEXT DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,

    product_id INTEGER NOT NULL,
    product_name TEXT NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price REAL NOT NULL,
    total_price REAL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert test admin
INSERT OR IGNORE INTO admin_users (username, password_hash) 
VALUES ('admin', '$2y$10$J4o8/.jGJt4vB7R7hNQ0L.Q5Qd8RZ1Z1d5Gz0V1W5gX6t5i2b1V1S2');

-- Insert sample products
INSERT OR IGNORE INTO products (name, description, price, stock, image_url) VALUES
('Cvetni prah 50g', 'Naravni cvetni prah, bogat s proteini in vitamini', 5.00, 100, 'https://static.photos/nature/320x240/101'),
('Balzam za ustnice iz čebeljega voska', 'Neguje in ščiti ustnice', 2.50, 50, 'https://static.photos/nature/320x240/102'),
('Med ajdov', 'Visokokakovosten med iz ajdovega cveta', 7.50, 30, 'https://static.photos/nature/320x240/103');
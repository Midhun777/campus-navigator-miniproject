    CREATE DATABASE IF NOT EXISTS campus_navigator;
    USE campus_navigator;

    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(100) NOT NULL,
        role ENUM('user','faculty','admin') DEFAULT 'user',
        profile_pic VARCHAR(255) DEFAULT NULL
    );

    CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        icon VARCHAR(100) DEFAULT NULL
    );

    CREATE TABLE spots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        category_id INT,
        timing VARCHAR(100),
        direction VARCHAR(255),
        distance VARCHAR(100),
        status ENUM('pending','approved') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (category_id) REFERENCES categories(id)
    );

    CREATE TABLE ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        spot_id INT,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (spot_id) REFERENCES spots(id)
    );

    CREATE TABLE comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        spot_id INT,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (spot_id) REFERENCES spots(id)
    );

    CREATE TABLE favorites (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        spot_id INT,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (spot_id) REFERENCES spots(id)
    );

    CREATE TABLE reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        spot_id INT,
        reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (spot_id) REFERENCES spots(id)
    );

    CREATE TABLE suggested_edits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        spot_id INT,
        suggestion TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (spot_id) REFERENCES spots(id)
    ); 

    -- Audit logs
    CREATE TABLE IF NOT EXISTS audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(100) NOT NULL,
        entity_type VARCHAR(50) NULL,
        entity_id INT NULL,
        details TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );

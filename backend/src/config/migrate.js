const mysql = require('mysql2/promise');
require('dotenv').config();

// Script tạo database schema MySQL
const createTables = async () => {
  try {
    console.log('🚀 Starting database migration...\n');

    // Tạo connection không có database để tạo database trước
    const connection = await mysql.createConnection({
      host: process.env.DB_HOST || 'localhost',
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASSWORD || '',
    });

    // Tạo database nếu chưa tồn tại
    await connection.query(`CREATE DATABASE IF NOT EXISTS ${process.env.DB_NAME || 'booking_hotel'} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`);
    console.log('✅ Database created/verified\n');
    await connection.end();

    // Sau đó kết nối tới database để tạo tables
    const { pool } = require('./database');

    // 1. USERS TABLE
    await pool.query(`
      CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        role ENUM('user', 'admin') DEFAULT 'user',
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_username (username),
        INDEX idx_role (role)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `);
    console.log('✅ Table "users" created/verified');

    // 2. HOTELS TABLE (thêm location, images)
    await pool.query(`
      CREATE TABLE IF NOT EXISTS hotels (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(200) NOT NULL,
        address VARCHAR(255) NOT NULL,
        city VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        rating DECIMAL(2,1) DEFAULT 5.0,
        description TEXT,
        location VARCHAR(255),
        images JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_city (city),
        INDEX idx_rating (rating)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `);
    console.log('✅ Table "hotels" created/verified');

    // 3. ROOM_TYPES TABLE (thêm amenities, images)
    await pool.query(`
      CREATE TABLE IF NOT EXISTS room_types (
        id INT PRIMARY KEY AUTO_INCREMENT,
        hotel_id INT NOT NULL,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        max_guests INT DEFAULT 2,
        size VARCHAR(50),
        amenities JSON,
        images JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE,
        INDEX idx_hotel_id (hotel_id),
        INDEX idx_price (price)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `);
    console.log('✅ Table "room_types" created/verified');

    // 4. ROOMS TABLE
    await pool.query(`
      CREATE TABLE IF NOT EXISTS rooms (
        id INT PRIMARY KEY AUTO_INCREMENT,
        room_type_id INT NOT NULL,
        room_number VARCHAR(20) NOT NULL,
        floor INT,
        status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE CASCADE,
        INDEX idx_room_type_id (room_type_id),
        INDEX idx_status (status)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `);
    console.log('✅ Table "rooms" created/verified');

    // 5. BOOKINGS TABLE
    await pool.query(`
      CREATE TABLE IF NOT EXISTS bookings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        room_id INT NOT NULL,
        guest_name VARCHAR(100) NOT NULL,
        guest_email VARCHAR(100) NOT NULL,
        guest_phone VARCHAR(20) NOT NULL,
        checkin_date DATE NOT NULL,
        checkout_date DATE NOT NULL,
        guests INT DEFAULT 2,
        total_price DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
        payment_method VARCHAR(50),
        payment_id VARCHAR(100),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (room_id) REFERENCES room_types(id) ON DELETE CASCADE,
        INDEX idx_user_id (user_id),
        INDEX idx_room_id (room_id),
        INDEX idx_status (status),
        INDEX idx_checkin_date (checkin_date)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `);
    console.log('✅ Table "bookings" created/verified');

    // Insert admin user nếu chưa có
    const [adminExists] = await pool.query(
      'SELECT id FROM users WHERE username = ? OR email = ?',
      ['admin', 'admin@bookinghotel.com']
    );

    if (adminExists.length === 0) {
      const bcrypt = require('bcryptjs');
      const hashedPassword = await bcrypt.hash('admin123', 10);
      
      await pool.query(
        `INSERT INTO users (username, password, email, full_name, phone, role, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?)`,
        ['admin', hashedPassword, 'admin@bookinghotel.com', 'Administrator', '0123456789', 'admin', 'active']
      );
      console.log('\n✅ Admin user created:');
      console.log('   Email: admin@bookinghotel.com');
      console.log('   Password: admin123');
    }

    console.log('\n🎉 Database migration completed successfully!');
    process.exit(0);
  } catch (error) {
    console.error('❌ Migration failed:', error.message);
    process.exit(1);
  }
};

// Run migration
createTables();


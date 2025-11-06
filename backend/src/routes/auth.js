const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { pool } = require('../config/database');
const { authMiddleware } = require('../middleware/auth');

// REGISTER - Đăng ký user mới
router.post('/register', async (req, res) => {
  try {
    const { username, password, email, full_name, phone } = req.body;

    // Validation
    if (!username || !password || !email || !full_name) {
      return res.status(400).json({
        success: false,
        message: 'Vui lòng nhập đầy đủ thông tin bắt buộc'
      });
    }

    if (password.length < 6) {
      return res.status(400).json({
        success: false,
        message: 'Mật khẩu phải có ít nhất 6 ký tự'
      });
    }

    // Kiểm tra username đã tồn tại
    const [existingUser] = await pool.query(
      'SELECT id FROM users WHERE username = ? OR email = ?',
      [username, email]
    );

    if (existingUser.length > 0) {
      return res.status(400).json({
        success: false,
        message: 'Tên đăng nhập hoặc email đã được sử dụng'
      });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);

    // Auto set role = 'admin' nếu username là 'admin'
    const role = username === 'admin' ? 'admin' : 'user';

    // Insert user
    const [result] = await pool.query(
      `INSERT INTO users (username, password, email, full_name, phone, role, status) 
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [username, hashedPassword, email, full_name, phone || null, role, 'active']
    );

    res.status(201).json({
      success: true,
      message: 'Đăng ký thành công',
      data: {
        id: result.insertId,
        username,
        email,
        full_name,
        role
      }
    });
  } catch (error) {
    console.error('Register error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi đăng ký'
    });
  }
});

// LOGIN - Đăng nhập
router.post('/login', async (req, res) => {
  try {
    const { username_or_email, password } = req.body;

    if (!username_or_email || !password) {
      return res.status(400).json({
        success: false,
        message: 'Vui lòng nhập đầy đủ thông tin'
      });
    }

    // Tìm user theo username hoặc email
    const [users] = await pool.query(
      'SELECT * FROM users WHERE (username = ? OR email = ?) AND status = ?',
      [username_or_email, username_or_email, 'active']
    );

    if (users.length === 0) {
      return res.status(401).json({
        success: false,
        message: 'Tên đăng nhập/email hoặc mật khẩu không đúng'
      });
    }

    const user = users[0];

    // Verify password
    const isValidPassword = await bcrypt.compare(password, user.password);
    
    if (!isValidPassword) {
      return res.status(401).json({
        success: false,
        message: 'Tên đăng nhập/email hoặc mật khẩu không đúng'
      });
    }

    // Generate JWT token
    const token = jwt.sign(
      {
        id: user.id,
        username: user.username,
        email: user.email,
        role: user.role
      },
      process.env.JWT_SECRET || 'your_jwt_secret_key_here',
      { expiresIn: process.env.JWT_EXPIRES_IN || '7d' }
    );

    res.json({
      success: true,
      message: 'Đăng nhập thành công',
      data: {
        user: {
          id: user.id,
          username: user.username,
          email: user.email,
          full_name: user.full_name,
          phone: user.phone,
          role: user.role
        },
        token
      }
    });
  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi đăng nhập'
    });
  }
});

// GET PROFILE - Lấy thông tin user hiện tại
router.get('/profile', authMiddleware, async (req, res) => {
  try {
    const [users] = await pool.query(
      'SELECT id, username, email, full_name, phone, role, status, created_at FROM users WHERE id = ?',
      [req.user.id]
    );

    if (users.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy người dùng'
      });
    }

    res.json({
      success: true,
      data: users[0]
    });
  } catch (error) {
    console.error('Get profile error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy thông tin'
    });
  }
});

module.exports = router;


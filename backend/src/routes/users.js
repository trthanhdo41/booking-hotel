const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const { pool } = require('../config/database');
const { authMiddleware, adminMiddleware } = require('../middleware/auth');

// GET ALL USERS - Admin only
router.get('/', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    const [users] = await pool.query(
      `SELECT id, username, email, full_name, phone, role, status, created_at, updated_at 
       FROM users 
       ORDER BY created_at DESC`
    );

    res.json({
      success: true,
      data: users
    });
  } catch (error) {
    console.error('Get users error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy danh sách người dùng'
    });
  }
});

// GET USER BY ID - Admin hoặc chính user đó
router.get('/:id', authMiddleware, async (req, res) => {
  try {
    // Chỉ admin hoặc chính user đó mới xem được
    if (req.user.role !== 'admin' && req.user.id !== parseInt(req.params.id)) {
      return res.status(403).json({
        success: false,
        message: 'Bạn không có quyền xem thông tin người dùng này'
      });
    }

    const [users] = await pool.query(
      `SELECT id, username, email, full_name, phone, role, status, created_at, updated_at 
       FROM users 
       WHERE id = ?`,
      [req.params.id]
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
    console.error('Get user error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy thông tin người dùng'
    });
  }
});

// UPDATE USER - Admin hoặc chính user đó
router.put('/:id', authMiddleware, async (req, res) => {
  try {
    // Chỉ admin hoặc chính user đó mới update được
    if (req.user.role !== 'admin' && req.user.id !== parseInt(req.params.id)) {
      return res.status(403).json({
        success: false,
        message: 'Bạn không có quyền cập nhật thông tin người dùng này'
      });
    }

    const { full_name, phone, email, password, role, status } = req.body;

    // Lấy user hiện tại
    const [currentUser] = await pool.query('SELECT * FROM users WHERE id = ?', [req.params.id]);
    
    if (currentUser.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy người dùng'
      });
    }

    const updateFields = [];
    const params = [];

    // User thường chỉ được update thông tin cá nhân
    if (full_name) {
      updateFields.push('full_name = ?');
      params.push(full_name);
    }
    if (phone) {
      updateFields.push('phone = ?');
      params.push(phone);
    }
    if (email) {
      // Kiểm tra email đã tồn tại chưa
      const [existingEmail] = await pool.query(
        'SELECT id FROM users WHERE email = ? AND id != ?',
        [email, req.params.id]
      );
      if (existingEmail.length > 0) {
        return res.status(400).json({
          success: false,
          message: 'Email đã được sử dụng'
        });
      }
      updateFields.push('email = ?');
      params.push(email);
    }
    if (password) {
      const hashedPassword = await bcrypt.hash(password, 10);
      updateFields.push('password = ?');
      params.push(hashedPassword);
    }

    // Chỉ admin mới được update role và status
    if (req.user.role === 'admin') {
      if (role) {
        updateFields.push('role = ?');
        params.push(role);
      }
      if (status) {
        updateFields.push('status = ?');
        params.push(status);
      }
    }

    if (updateFields.length === 0) {
      return res.status(400).json({
        success: false,
        message: 'Không có thông tin để cập nhật'
      });
    }

    params.push(req.params.id);

    await pool.query(
      `UPDATE users SET ${updateFields.join(', ')} WHERE id = ?`,
      params
    );

    res.json({
      success: true,
      message: 'Cập nhật thông tin thành công'
    });
  } catch (error) {
    console.error('Update user error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi cập nhật thông tin'
    });
  }
});

// DELETE USER - Admin only
router.delete('/:id', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    // Không cho phép xóa chính mình
    if (req.user.id === parseInt(req.params.id)) {
      return res.status(400).json({
        success: false,
        message: 'Bạn không thể xóa tài khoản của chính mình'
      });
    }

    const [result] = await pool.query('DELETE FROM users WHERE id = ?', [req.params.id]);

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy người dùng'
      });
    }

    res.json({
      success: true,
      message: 'Xóa người dùng thành công'
    });
  } catch (error) {
    console.error('Delete user error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi xóa người dùng'
    });
  }
});

module.exports = router;


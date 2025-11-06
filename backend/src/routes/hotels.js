const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');
const { authMiddleware, adminMiddleware } = require('../middleware/auth');

// GET CITIES - Lấy danh sách thành phố với số lượng khách sạn (public)
router.get('/cities', async (req, res) => {
  try {
    const [cities] = await pool.query(`
      SELECT city, COUNT(*) as hotel_count, AVG(rating) as avg_rating
      FROM hotels
      GROUP BY city
      ORDER BY hotel_count DESC, avg_rating DESC
      LIMIT 20
    `);

    res.json({
      success: true,
      data: cities
    });
  } catch (error) {
    console.error('Get cities error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy danh sách thành phố'
    });
  }
});

// GET ALL HOTELS - Lấy tất cả khách sạn (public)
router.get('/', async (req, res) => {
  try {
    const { city, search } = req.query;
    
    let query = 'SELECT * FROM hotels WHERE 1=1';
    const params = [];

    // Filter by city
    if (city) {
      query += ' AND city LIKE ?';
      params.push(`%${city}%`);
    }

    // Search by name or address
    if (search) {
      query += ' AND (name LIKE ? OR address LIKE ?)';
      params.push(`%${search}%`, `%${search}%`);
    }

    query += ' ORDER BY rating DESC, created_at DESC';

    const [hotels] = await pool.query(query, params);

    res.json({
      success: true,
      data: hotels
    });
  } catch (error) {
    console.error('Get hotels error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy danh sách khách sạn'
    });
  }
});

// GET HOTEL BY ID - Lấy chi tiết khách sạn (public)
router.get('/:id', async (req, res) => {
  try {
    const [hotels] = await pool.query('SELECT * FROM hotels WHERE id = ?', [req.params.id]);

    if (hotels.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy khách sạn'
      });
    }

    res.json({
      success: true,
      data: hotels[0]
    });
  } catch (error) {
    console.error('Get hotel error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy thông tin khách sạn'
    });
  }
});

// CREATE HOTEL - Thêm khách sạn mới (admin only)
router.post('/', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    const { name, address, city, phone, email, rating, description, location, images } = req.body;

    if (!name || !address || !city) {
      return res.status(400).json({
        success: false,
        message: 'Vui lòng nhập đầy đủ thông tin bắt buộc (tên, địa chỉ, thành phố)'
      });
    }

    const [result] = await pool.query(
      `INSERT INTO hotels (name, address, city, phone, email, rating, description, location, images) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        name,
        address,
        city,
        phone || null,
        email || null,
        rating || 5.0,
        description || null,
        location || null,
        images ? JSON.stringify(images) : null
      ]
    );

    res.status(201).json({
      success: true,
      message: 'Thêm khách sạn thành công',
      data: {
        id: result.insertId,
        name,
        city
      }
    });
  } catch (error) {
    console.error('Create hotel error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi thêm khách sạn'
    });
  }
});

// UPDATE HOTEL - Cập nhật khách sạn (admin only)
router.put('/:id', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    const { name, address, city, phone, email, rating, description, location, images } = req.body;

    if (!name || !address || !city) {
      return res.status(400).json({
        success: false,
        message: 'Vui lòng nhập đầy đủ thông tin bắt buộc'
      });
    }

    const [result] = await pool.query(
      `UPDATE hotels 
       SET name = ?, address = ?, city = ?, phone = ?, email = ?, 
           rating = ?, description = ?, location = ?, images = ?
       WHERE id = ?`,
      [
        name,
        address,
        city,
        phone || null,
        email || null,
        rating || 5.0,
        description || null,
        location || null,
        images ? JSON.stringify(images) : null,
        req.params.id
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy khách sạn'
      });
    }

    res.json({
      success: true,
      message: 'Cập nhật khách sạn thành công'
    });
  } catch (error) {
    console.error('Update hotel error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi cập nhật khách sạn'
    });
  }
});

// DELETE HOTEL - Xóa khách sạn (admin only)
router.delete('/:id', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    const [result] = await pool.query('DELETE FROM hotels WHERE id = ?', [req.params.id]);

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy khách sạn'
      });
    }

    res.json({
      success: true,
      message: 'Xóa khách sạn thành công'
    });
  } catch (error) {
    console.error('Delete hotel error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi xóa khách sạn'
    });
  }
});

module.exports = router;


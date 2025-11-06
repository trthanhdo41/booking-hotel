const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');
const { authMiddleware, adminMiddleware } = require('../middleware/auth');

// GET ALL ROOM TYPES - Lấy tất cả loại phòng (có thể filter theo hotel_id)
router.get('/', async (req, res) => {
  try {
    const { hotel_id, city, max_guests, min_price, max_price } = req.query;
    
    let query = `
      SELECT rt.*, h.name as hotel_name, h.city as hotel_city, h.address as hotel_address, h.rating as hotel_rating
      FROM room_types rt
      LEFT JOIN hotels h ON rt.hotel_id = h.id
      WHERE 1=1
    `;
    const params = [];

    // Filter by hotel
    if (hotel_id) {
      query += ' AND rt.hotel_id = ?';
      params.push(hotel_id);
    }

    // Filter by city
    if (city) {
      query += ' AND h.city LIKE ?';
      params.push(`%${city}%`);
    }

    // Filter by max guests
    if (max_guests) {
      query += ' AND rt.max_guests >= ?';
      params.push(max_guests);
    }

    // Filter by price range
    if (min_price) {
      query += ' AND rt.price >= ?';
      params.push(min_price);
    }
    if (max_price) {
      query += ' AND rt.price <= ?';
      params.push(max_price);
    }

    query += ' ORDER BY rt.created_at DESC';

    const [roomTypes] = await pool.query(query, params);

    res.json({
      success: true,
      data: roomTypes
    });
  } catch (error) {
    console.error('Get room types error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy danh sách loại phòng'
    });
  }
});

// GET ROOM TYPE BY ID - Lấy chi tiết loại phòng
router.get('/:id', async (req, res) => {
  try {
    const [roomTypes] = await pool.query(
      `SELECT rt.*, h.name as hotel_name, h.city as hotel_city, h.address as hotel_address, 
              h.phone as hotel_phone, h.email as hotel_email, h.rating as hotel_rating
       FROM room_types rt
       LEFT JOIN hotels h ON rt.hotel_id = h.id
       WHERE rt.id = ?`,
      [req.params.id]
    );

    if (roomTypes.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy loại phòng'
      });
    }

    res.json({
      success: true,
      data: roomTypes[0]
    });
  } catch (error) {
    console.error('Get room type error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy thông tin loại phòng'
    });
  }
});

// CREATE ROOM TYPE - Thêm loại phòng mới (admin only)
router.post('/', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    const { hotel_id, name, description, price, max_guests, size, amenities, images } = req.body;

    if (!hotel_id || !name || !price || !max_guests) {
      return res.status(400).json({
        success: false,
        message: 'Vui lòng nhập đầy đủ thông tin bắt buộc (khách sạn, tên phòng, giá, số lượng khách)'
      });
    }

    // Kiểm tra hotel tồn tại
    const [hotels] = await pool.query('SELECT id FROM hotels WHERE id = ?', [hotel_id]);
    if (hotels.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy khách sạn'
      });
    }

    const [result] = await pool.query(
      `INSERT INTO room_types (hotel_id, name, description, price, max_guests, size, amenities, images) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        hotel_id,
        name,
        description || null,
        price,
        max_guests,
        size || null,
        amenities ? JSON.stringify(amenities) : null,
        images ? JSON.stringify(images) : null
      ]
    );

    res.status(201).json({
      success: true,
      message: 'Thêm loại phòng thành công',
      data: {
        id: result.insertId,
        name,
        price
      }
    });
  } catch (error) {
    console.error('Create room type error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi thêm loại phòng'
    });
  }
});

// UPDATE ROOM TYPE - Cập nhật loại phòng (admin only)
router.put('/:id', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    const { hotel_id, name, description, price, max_guests, size, amenities, images } = req.body;

    if (!hotel_id || !name || !price || !max_guests) {
      return res.status(400).json({
        success: false,
        message: 'Vui lòng nhập đầy đủ thông tin bắt buộc'
      });
    }

    const [result] = await pool.query(
      `UPDATE room_types 
       SET hotel_id = ?, name = ?, description = ?, price = ?, max_guests = ?, 
           size = ?, amenities = ?, images = ?
       WHERE id = ?`,
      [
        hotel_id,
        name,
        description || null,
        price,
        max_guests,
        size || null,
        amenities ? JSON.stringify(amenities) : null,
        images ? JSON.stringify(images) : null,
        req.params.id
      ]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy loại phòng'
      });
    }

    res.json({
      success: true,
      message: 'Cập nhật loại phòng thành công'
    });
  } catch (error) {
    console.error('Update room type error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi cập nhật loại phòng'
    });
  }
});

// DELETE ROOM TYPE - Xóa loại phòng (admin only)
router.delete('/:id', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    const [result] = await pool.query('DELETE FROM room_types WHERE id = ?', [req.params.id]);

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy loại phòng'
      });
    }

    res.json({
      success: true,
      message: 'Xóa loại phòng thành công'
    });
  } catch (error) {
    console.error('Delete room type error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi xóa loại phòng'
    });
  }
});

module.exports = router;


const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');
const { authMiddleware, adminMiddleware } = require('../middleware/auth');

// GET ALL BOOKINGS - Admin xem tất cả, User chỉ xem của mình
router.get('/', authMiddleware, async (req, res) => {
  try {
    const { status } = req.query;
    
    let query = `
      SELECT b.*, 
             rt.name as room_name, rt.price as room_price,
             h.name as hotel_name, h.city as hotel_city, h.address as hotel_address,
             u.username, u.email as user_email, u.full_name as user_full_name
      FROM bookings b
      LEFT JOIN room_types rt ON b.room_id = rt.id
      LEFT JOIN hotels h ON rt.hotel_id = h.id
      LEFT JOIN users u ON b.user_id = u.id
      WHERE 1=1
    `;
    const params = [];

    // Nếu không phải admin, chỉ xem booking của mình
    if (req.user.role !== 'admin') {
      query += ' AND b.user_id = ?';
      params.push(req.user.id);
    }

    // Filter by status
    if (status) {
      query += ' AND b.status = ?';
      params.push(status);
    }

    query += ' ORDER BY b.created_at DESC';

    const [bookings] = await pool.query(query, params);

    res.json({
      success: true,
      data: bookings
    });
  } catch (error) {
    console.error('Get bookings error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy danh sách đặt phòng'
    });
  }
});

// GET BOOKING BY ID - Lấy chi tiết booking
router.get('/:id', authMiddleware, async (req, res) => {
  try {
    const [bookings] = await pool.query(
      `SELECT b.*, 
              rt.name as room_name, rt.price as room_price, rt.description as room_description,
              h.name as hotel_name, h.city as hotel_city, h.address as hotel_address, 
              h.phone as hotel_phone, h.email as hotel_email,
              u.username, u.email as user_email, u.full_name as user_full_name
       FROM bookings b
       LEFT JOIN room_types rt ON b.room_id = rt.id
       LEFT JOIN hotels h ON rt.hotel_id = h.id
       LEFT JOIN users u ON b.user_id = u.id
       WHERE b.id = ?`,
      [req.params.id]
    );

    if (bookings.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy đặt phòng'
      });
    }

    const booking = bookings[0];

    // Nếu không phải admin và không phải booking của mình thì không được xem
    if (req.user.role !== 'admin' && booking.user_id !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Bạn không có quyền xem đặt phòng này'
      });
    }

    res.json({
      success: true,
      data: booking
    });
  } catch (error) {
    console.error('Get booking error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy thông tin đặt phòng'
    });
  }
});

// CREATE BOOKING - Tạo đặt phòng mới
router.post('/', authMiddleware, async (req, res) => {
  try {
    const {
      room_id,
      guest_name,
      guest_email,
      guest_phone,
      checkin_date,
      checkout_date,
      guests,
      total_price,
      notes
    } = req.body;

    if (!room_id || !guest_name || !guest_email || !guest_phone || !checkin_date || !checkout_date || !total_price) {
      return res.status(400).json({
        success: false,
        message: 'Vui lòng nhập đầy đủ thông tin bắt buộc'
      });
    }

    // Kiểm tra room type tồn tại
    const [roomTypes] = await pool.query('SELECT id FROM room_types WHERE id = ?', [room_id]);
    if (roomTypes.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy loại phòng'
      });
    }

    // Generate payment_id
    const payment_id = `PAY${Date.now()}${Math.floor(Math.random() * 1000)}`;

    const [result] = await pool.query(
      `INSERT INTO bookings 
       (user_id, room_id, guest_name, guest_email, guest_phone, checkin_date, checkout_date, 
        guests, total_price, status, payment_id, notes) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        req.user.id,
        room_id,
        guest_name,
        guest_email,
        guest_phone,
        checkin_date,
        checkout_date,
        guests || 2,
        total_price,
        'pending',
        payment_id,
        notes || null
      ]
    );

    res.status(201).json({
      success: true,
      message: 'Đặt phòng thành công',
      data: {
        id: result.insertId,
        payment_id,
        total_price
      }
    });
  } catch (error) {
    console.error('Create booking error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi đặt phòng'
    });
  }
});

// UPDATE BOOKING STATUS - Cập nhật trạng thái booking
router.patch('/:id/status', authMiddleware, async (req, res) => {
  try {
    const { status, payment_method } = req.body;

    if (!status) {
      return res.status(400).json({
        success: false,
        message: 'Vui lòng chọn trạng thái'
      });
    }

    // Kiểm tra status hợp lệ
    const validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!validStatuses.includes(status)) {
      return res.status(400).json({
        success: false,
        message: 'Trạng thái không hợp lệ'
      });
    }

    // Lấy booking info
    const [bookings] = await pool.query('SELECT * FROM bookings WHERE id = ?', [req.params.id]);
    
    if (bookings.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy đặt phòng'
      });
    }

    const booking = bookings[0];

    // Nếu không phải admin và không phải booking của mình thì không được sửa
    if (req.user.role !== 'admin' && booking.user_id !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'Bạn không có quyền cập nhật đặt phòng này'
      });
    }

    // User chỉ được cancel booking của mình
    if (req.user.role !== 'admin' && status !== 'cancelled') {
      return res.status(403).json({
        success: false,
        message: 'Bạn chỉ có thể hủy đặt phòng'
      });
    }

    const updateFields = ['status = ?'];
    const params = [status];

    if (payment_method) {
      updateFields.push('payment_method = ?');
      params.push(payment_method);
    }

    params.push(req.params.id);

    await pool.query(
      `UPDATE bookings SET ${updateFields.join(', ')} WHERE id = ?`,
      params
    );

    res.json({
      success: true,
      message: 'Cập nhật trạng thái thành công'
    });
  } catch (error) {
    console.error('Update booking status error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi cập nhật trạng thái'
    });
  }
});

// DELETE BOOKING - Xóa booking (admin only)
router.delete('/:id', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    const [result] = await pool.query('DELETE FROM bookings WHERE id = ?', [req.params.id]);

    if (result.affectedRows === 0) {
      return res.status(404).json({
        success: false,
        message: 'Không tìm thấy đặt phòng'
      });
    }

    res.json({
      success: true,
      message: 'Xóa đặt phòng thành công'
    });
  } catch (error) {
    console.error('Delete booking error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi xóa đặt phòng'
    });
  }
});

module.exports = router;


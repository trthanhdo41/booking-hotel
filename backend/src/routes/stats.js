const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');
const { authMiddleware, adminMiddleware } = require('../middleware/auth');

// GET DASHBOARD STATS - Admin only
router.get('/dashboard', authMiddleware, adminMiddleware, async (req, res) => {
  try {
    // Total hotels
    const [hotels] = await pool.query('SELECT COUNT(*) as total FROM hotels');
    const totalHotels = hotels[0].total;

    // Total room types
    const [roomTypes] = await pool.query('SELECT COUNT(*) as total FROM room_types');
    const totalRoomTypes = roomTypes[0].total;

    // Total bookings
    const [bookings] = await pool.query('SELECT COUNT(*) as total FROM bookings');
    const totalBookings = bookings[0].total;

    // Total users
    const [users] = await pool.query('SELECT COUNT(*) as total FROM users WHERE role = ?', ['user']);
    const totalUsers = users[0].total;

    // Total revenue (chỉ tính confirmed & completed)
    const [revenue] = await pool.query(
      'SELECT SUM(total_price) as total FROM bookings WHERE status IN (?, ?)',
      ['confirmed', 'completed']
    );
    const totalRevenue = revenue[0].total || 0;

    // Bookings by status
    const [bookingsByStatus] = await pool.query(`
      SELECT status, COUNT(*) as count 
      FROM bookings 
      GROUP BY status
    `);

    // Recent bookings (10 latest)
    const [recentBookings] = await pool.query(`
      SELECT b.*, 
             rt.name as room_name, 
             h.name as hotel_name, 
             u.full_name as guest_name
      FROM bookings b
      LEFT JOIN room_types rt ON b.room_id = rt.id
      LEFT JOIN hotels h ON rt.hotel_id = h.id
      LEFT JOIN users u ON b.user_id = u.id
      ORDER BY b.created_at DESC
      LIMIT 10
    `);

    res.json({
      success: true,
      data: {
        totalHotels,
        totalRoomTypes,
        totalBookings,
        totalUsers,
        totalRevenue,
        bookingsByStatus,
        recentBookings
      }
    });
  } catch (error) {
    console.error('Get stats error:', error);
    res.status(500).json({
      success: false,
      message: 'Lỗi server khi lấy thống kê'
    });
  }
});

module.exports = router;


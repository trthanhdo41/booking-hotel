import React, { useState, useEffect } from 'react';
import { statsAPI } from '../../services/api';
import { formatPrice } from '../../utils/helpers';
import LoadingSpinner from '../../components/LoadingSpinner';
import { toast } from 'react-toastify';

const AdminDashboard = () => {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStats();
  }, []);

  const loadStats = async () => {
    setLoading(true);
    try {
      const response = await statsAPI.getDashboard();
      setStats(response.data.data);
    } catch (error) {
      console.error('Error loading stats:', error);
      toast.error('Lỗi khi tải thống kê');
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <LoadingSpinner fullPage />;
  }

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-8">Dashboard</h1>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="bg-white rounded-lg shadow-sm p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-gray-600 text-sm mb-1">Tổng khách sạn</p>
              <p className="text-3xl font-bold text-gray-900">{stats?.totalHotels || 0}</p>
            </div>
            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <i className="fas fa-hotel text-2xl text-blue-600"></i>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-gray-600 text-sm mb-1">Tổng loại phòng</p>
              <p className="text-3xl font-bold text-gray-900">{stats?.totalRoomTypes || 0}</p>
            </div>
            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <i className="fas fa-bed text-2xl text-green-600"></i>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-gray-600 text-sm mb-1">Tổng đặt phòng</p>
              <p className="text-3xl font-bold text-gray-900">{stats?.totalBookings || 0}</p>
            </div>
            <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <i className="fas fa-calendar-check text-2xl text-yellow-600"></i>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-gray-600 text-sm mb-1">Tổng người dùng</p>
              <p className="text-3xl font-bold text-gray-900">{stats?.totalUsers || 0}</p>
            </div>
            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <i className="fas fa-users text-2xl text-purple-600"></i>
            </div>
          </div>
        </div>
      </div>

      {/* Revenue Card */}
      <div className="bg-gradient-to-r from-primary to-primary-dark rounded-lg shadow-lg p-8 mb-8 text-white">
        <h3 className="text-xl font-semibold mb-2">Tổng doanh thu</h3>
        <p className="text-4xl font-bold">{formatPrice(stats?.totalRevenue || 0)}</p>
        <p className="text-gray-200 mt-2">Chỉ tính đặt phòng đã xác nhận và hoàn thành</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Bookings by Status */}
        <div className="bg-white rounded-lg shadow-sm p-6">
          <h3 className="text-xl font-semibold mb-4">Đặt phòng theo trạng thái</h3>
          <div className="space-y-3">
            {stats?.bookingsByStatus?.map((item) => (
              <div key={item.status} className="flex items-center justify-between">
                <span className="text-gray-700 capitalize">{item.status}</span>
                <span className="font-semibold">{item.count}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Recent Bookings */}
        <div className="bg-white rounded-lg shadow-sm p-6">
          <h3 className="text-xl font-semibold mb-4">Đặt phòng gần đây</h3>
          <div className="space-y-3">
            {stats?.recentBookings?.slice(0, 5).map((booking) => (
              <div key={booking.id} className="flex items-center justify-between text-sm border-b pb-2">
                <div>
                  <div className="font-semibold">{booking.guest_name}</div>
                  <div className="text-gray-600">{booking.hotel_name}</div>
                </div>
                <div className="text-right">
                  <div className="font-semibold text-primary">{formatPrice(booking.total_price)}</div>
                  <div className="text-gray-600">{booking.status}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminDashboard;


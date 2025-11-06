import React, { useState, useEffect } from 'react';
import { bookingsAPI } from '../../services/api';
import { formatPrice, formatDate, getStatusColor, getStatusText } from '../../utils/helpers';
import LoadingSpinner from '../../components/LoadingSpinner';
import { toast } from 'react-toastify';

const AdminBookings = () => {
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState('all');

  useEffect(() => {
    loadBookings();
  }, []);

  const loadBookings = async () => {
    setLoading(true);
    try {
      const response = await bookingsAPI.getAll();
      setBookings(response.data.data);
    } catch (error) {
      console.error('Error loading bookings:', error);
      toast.error('Lỗi khi tải danh sách đặt phòng');
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateStatus = async (bookingId, newStatus) => {
    try {
      await bookingsAPI.updateStatus(bookingId, { status: newStatus });
      toast.success('Cập nhật trạng thái thành công');
      loadBookings();
    } catch (error) {
      console.error('Update status error:', error);
      toast.error('Lỗi khi cập nhật trạng thái');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Bạn có chắc muốn xóa đặt phòng này?')) {
      return;
    }

    try {
      await bookingsAPI.delete(id);
      toast.success('Xóa đặt phòng thành công');
      loadBookings();
    } catch (error) {
      console.error('Delete booking error:', error);
      toast.error('Không thể xóa đặt phòng');
    }
  };

  const filteredBookings = filter === 'all'
    ? bookings
    : bookings.filter(b => b.status === filter);

  if (loading) {
    return <LoadingSpinner fullPage />;
  }

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 mb-6">Quản lý đặt phòng</h1>

      {/* Filters */}
      <div className="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div className="flex flex-wrap gap-2">
          <button
            onClick={() => setFilter('all')}
            className={`px-4 py-2 rounded-lg transition ${
              filter === 'all'
                ? 'bg-primary text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Tất cả ({bookings.length})
          </button>
          <button
            onClick={() => setFilter('pending')}
            className={`px-4 py-2 rounded-lg transition ${
              filter === 'pending'
                ? 'bg-warning text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Chờ xác nhận ({bookings.filter(b => b.status === 'pending').length})
          </button>
          <button
            onClick={() => setFilter('confirmed')}
            className={`px-4 py-2 rounded-lg transition ${
              filter === 'confirmed'
                ? 'bg-info text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Đã xác nhận ({bookings.filter(b => b.status === 'confirmed').length})
          </button>
          <button
            onClick={() => setFilter('completed')}
            className={`px-4 py-2 rounded-lg transition ${
              filter === 'completed'
                ? 'bg-success text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Hoàn thành ({bookings.filter(b => b.status === 'completed').length})
          </button>
          <button
            onClick={() => setFilter('cancelled')}
            className={`px-4 py-2 rounded-lg transition ${
              filter === 'cancelled'
                ? 'bg-danger text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            Đã hủy ({bookings.filter(b => b.status === 'cancelled').length})
          </button>
        </div>
      </div>

      {/* Bookings Table */}
      <div className="bg-white rounded-lg shadow-sm overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã ĐP</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Khách hàng</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phòng</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Khách sạn</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tổng tiền</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
              <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hành động</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {filteredBookings.map((booking) => (
              <tr key={booking.id}>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  #{booking.id}
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-semibold text-gray-900">{booking.guest_name}</div>
                  <div className="text-sm text-gray-500">{booking.guest_email}</div>
                  <div className="text-sm text-gray-500">{booking.guest_phone}</div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-semibold text-gray-900">{booking.room_name}</div>
                  <div className="text-sm text-gray-500">
                    <i className="fas fa-users mr-1"></i>
                    {booking.guests} khách
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">{booking.hotel_name}</div>
                  <div className="text-sm text-gray-500">{booking.hotel_city}</div>
                </td>
                <td className="px-6 py-4 text-sm text-gray-700">
                  <div>{formatDate(booking.checkin_date)}</div>
                  <div>{formatDate(booking.checkout_date)}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary">
                  {formatPrice(booking.total_price)}
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <select
                    value={booking.status}
                    onChange={(e) => handleUpdateStatus(booking.id, e.target.value)}
                    className={`text-sm font-semibold px-3 py-1 rounded-full ${getStatusColor(booking.status)}`}
                  >
                    <option value="pending">Chờ xác nhận</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="completed">Hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                  </select>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-center text-sm">
                  <button
                    onClick={() => handleDelete(booking.id)}
                    className="text-danger hover:text-red-700"
                    title="Xóa"
                  >
                    <i className="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default AdminBookings;


import React, { useState, useEffect } from 'react';
import { bookingsAPI } from '../services/api';
import { formatPrice, formatDate, getStatusColor, getStatusText } from '../utils/helpers';
import LoadingSpinner from '../components/LoadingSpinner';
import { toast } from 'react-toastify';

const MyBookingsPage = () => {
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

  const handleCancelBooking = async (bookingId) => {
    if (!window.confirm('Bạn có chắc muốn hủy đặt phòng này?')) {
      return;
    }

    try {
      await bookingsAPI.updateStatus(bookingId, { status: 'cancelled' });
      toast.success('Đã hủy đặt phòng');
      loadBookings();
    } catch (error) {
      console.error('Cancel booking error:', error);
      toast.error('Không thể hủy đặt phòng');
    }
  };

  const filteredBookings = filter === 'all'
    ? bookings
    : bookings.filter(b => b.status === filter);

  if (loading) {
    return <LoadingSpinner fullPage />;
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">Đặt phòng của tôi</h1>

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

        {/* Bookings List */}
        {filteredBookings.length === 0 ? (
          <div className="bg-white rounded-lg shadow-sm p-12 text-center">
            <i className="fas fa-calendar-times text-6xl text-gray-400 mb-4"></i>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">
              Chưa có đặt phòng
            </h3>
            <p className="text-gray-600">Hãy bắt đầu tìm kiếm và đặt phòng ngay!</p>
          </div>
        ) : (
          <div className="space-y-4">
            {filteredBookings.map((booking) => (
              <div key={booking.id} className="bg-white rounded-lg shadow-sm p-6">
                <div className="flex flex-col lg:flex-row justify-between">
                  <div className="flex-1">
                    <div className="flex items-start justify-between mb-3">
                      <div>
                        <h3 className="text-xl font-semibold text-gray-900">{booking.room_name}</h3>
                        <p className="text-gray-600 mt-1">
                          <i className="fas fa-hotel text-primary mr-2"></i>
                          {booking.hotel_name}
                        </p>
                        <p className="text-gray-600">
                          <i className="fas fa-map-marker-alt text-primary mr-2"></i>
                          {booking.hotel_city}
                        </p>
                      </div>
                      <span className={`px-3 py-1 rounded-full text-sm font-semibold ${getStatusColor(booking.status)}`}>
                        {getStatusText(booking.status)}
                      </span>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-sm">
                      <div>
                        <div className="text-gray-600">Mã đặt phòng</div>
                        <div className="font-semibold">#{booking.id}</div>
                      </div>
                      <div>
                        <div className="text-gray-600">Ngày nhận phòng</div>
                        <div className="font-semibold">{formatDate(booking.checkin_date)}</div>
                      </div>
                      <div>
                        <div className="text-gray-600">Ngày trả phòng</div>
                        <div className="font-semibold">{formatDate(booking.checkout_date)}</div>
                      </div>
                      <div>
                        <div className="text-gray-600">Số khách</div>
                        <div className="font-semibold">{booking.guests} người</div>
                      </div>
                      <div>
                        <div className="text-gray-600">Tổng tiền</div>
                        <div className="font-semibold text-primary">{formatPrice(booking.total_price)}</div>
                      </div>
                      {booking.payment_method && (
                        <div>
                          <div className="text-gray-600">Thanh toán</div>
                          <div className="font-semibold capitalize">{booking.payment_method}</div>
                        </div>
                      )}
                    </div>
                  </div>

                  {/* Actions */}
                  {(booking.status === 'pending' || booking.status === 'confirmed') && (
                    <div className="mt-4 lg:mt-0 lg:ml-6 flex flex-col justify-center">
                      <button
                        onClick={() => handleCancelBooking(booking.id)}
                        className="px-6 py-2 bg-danger text-white rounded-lg hover:bg-red-600 transition"
                      >
                        <i className="fas fa-times mr-2"></i>
                        Hủy đặt phòng
                      </button>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default MyBookingsPage;


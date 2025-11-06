import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { roomTypesAPI, bookingsAPI } from '../services/api';
import { useAuth } from '../contexts/AuthContext';
import { formatPrice, formatDate, calculateDays, isValidEmail, isValidPhone } from '../utils/helpers';
import LoadingSpinner from '../components/LoadingSpinner';
import { toast } from 'react-toastify';

const BookingPage = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { user } = useAuth();
  const [room, setRoom] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  
  const roomTypeId = searchParams.get('room_type_id');
  const checkinParam = searchParams.get('checkin');
  const checkoutParam = searchParams.get('checkout');

  const [bookingData, setBookingData] = useState({
    checkin_date: checkinParam || '',
    checkout_date: checkoutParam || '',
    guests: 2,
    guest_name: user?.full_name || '',
    guest_email: user?.email || '',
    guest_phone: user?.phone || '',
    notes: '',
  });

  useEffect(() => {
    if (roomTypeId) {
      loadRoomDetails();
    }
  }, [roomTypeId]);

  const loadRoomDetails = async () => {
    setLoading(true);
    try {
      const response = await roomTypesAPI.getById(roomTypeId);
      setRoom(response.data.data);
    } catch (error) {
      console.error('Error loading room:', error);
      toast.error('Không tìm thấy thông tin phòng');
      navigate('/search');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (e) => {
    setBookingData({
      ...bookingData,
      [e.target.name]: e.target.value,
    });
  };

  const validateForm = () => {
    if (!bookingData.checkin_date || !bookingData.checkout_date) {
      toast.error('Vui lòng chọn ngày nhận và trả phòng');
      return false;
    }

    if (new Date(bookingData.checkin_date) >= new Date(bookingData.checkout_date)) {
      toast.error('Ngày trả phòng phải sau ngày nhận phòng');
      return false;
    }

    if (!bookingData.guest_name || !bookingData.guest_email || !bookingData.guest_phone) {
      toast.error('Vui lòng nhập đầy đủ thông tin khách hàng');
      return false;
    }

    if (!isValidEmail(bookingData.guest_email)) {
      toast.error('Email không hợp lệ');
      return false;
    }

    if (!isValidPhone(bookingData.guest_phone)) {
      toast.error('Số điện thoại không hợp lệ');
      return false;
    }

    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    const nights = calculateDays(bookingData.checkin_date, bookingData.checkout_date);
    const totalPrice = room.price * nights;

    setSubmitting(true);
    try {
      const response = await bookingsAPI.create({
        room_id: room.id,
        ...bookingData,
        total_price: totalPrice,
      });

      toast.success('Đặt phòng thành công!');
      
      // Redirect to payment page
      const booking = response.data.data;
      navigate(`/payment?booking_id=${booking.id}&amount=${totalPrice}`);
    } catch (error) {
      console.error('Booking error:', error);
      toast.error(error.response?.data?.message || 'Đặt phòng thất bại');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return <LoadingSpinner fullPage />;
  }

  if (!room) {
    return null;
  }

  const nights = bookingData.checkin_date && bookingData.checkout_date
    ? calculateDays(bookingData.checkin_date, bookingData.checkout_date)
    : 0;
  const totalPrice = room.price * nights;

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-6">Đặt phòng</h1>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Booking Form */}
          <div className="lg:col-span-2">
            <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-sm p-6 space-y-6">
              {/* Room Info */}
              <div className="pb-6 border-b">
                <h2 className="text-xl font-semibold mb-4">Thông tin phòng</h2>
                <div className="space-y-2">
                  <h3 className="font-semibold text-lg">{room.name}</h3>
                  <p className="text-gray-600">
                    <i className="fas fa-hotel text-primary mr-2"></i>
                    {room.hotel_name}
                  </p>
                  <p className="text-gray-600">
                    <i className="fas fa-map-marker-alt text-primary mr-2"></i>
                    {room.hotel_address}, {room.hotel_city}
                  </p>
                </div>
              </div>

              {/* Date Selection */}
              <div>
                <h2 className="text-xl font-semibold mb-4">Thời gian lưu trú</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Ngày nhận phòng
                    </label>
                    <input
                      type="date"
                      name="checkin_date"
                      value={bookingData.checkin_date}
                      onChange={handleChange}
                      min={new Date().toISOString().split('T')[0]}
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Ngày trả phòng
                    </label>
                    <input
                      type="date"
                      name="checkout_date"
                      value={bookingData.checkout_date}
                      onChange={handleChange}
                      min={bookingData.checkin_date || new Date().toISOString().split('T')[0]}
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                      required
                    />
                  </div>
                </div>
                <div className="mt-4">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Số lượng khách
                  </label>
                  <input
                    type="number"
                    name="guests"
                    value={bookingData.guests}
                    onChange={handleChange}
                    min="1"
                    max={room.max_guests}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    required
                  />
                  <p className="text-sm text-gray-500 mt-1">
                    Tối đa: {room.max_guests} khách
                  </p>
                </div>
              </div>

              {/* Guest Info */}
              <div>
                <h2 className="text-xl font-semibold mb-4">Thông tin khách hàng</h2>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Họ và tên
                    </label>
                    <input
                      type="text"
                      name="guest_name"
                      value={bookingData.guest_name}
                      onChange={handleChange}
                      placeholder="Nguyễn Văn A"
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Email
                    </label>
                    <input
                      type="email"
                      name="guest_email"
                      value={bookingData.guest_email}
                      onChange={handleChange}
                      placeholder="email@example.com"
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Số điện thoại
                    </label>
                    <input
                      type="tel"
                      name="guest_phone"
                      value={bookingData.guest_phone}
                      onChange={handleChange}
                      placeholder="0912345678"
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Ghi chú (tùy chọn)
                    </label>
                    <textarea
                      name="notes"
                      value={bookingData.notes}
                      onChange={handleChange}
                      rows="3"
                      placeholder="Yêu cầu đặc biệt..."
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    ></textarea>
                  </div>
                </div>
              </div>

              <button
                type="submit"
                disabled={submitting || nights === 0}
                className="w-full px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {submitting ? <LoadingSpinner size="sm" /> : 'Tiếp tục đến thanh toán'}
              </button>
            </form>
          </div>

          {/* Price Summary */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow-sm p-6 sticky top-20">
              <h2 className="text-xl font-semibold mb-4">Chi tiết giá</h2>
              <div className="space-y-3">
                <div className="flex justify-between text-gray-700">
                  <span>{formatPrice(room.price)} x {nights} đêm</span>
                  <span>{formatPrice(room.price * nights)}</span>
                </div>
                <div className="pt-3 border-t">
                  <div className="flex justify-between text-lg font-bold">
                    <span>Tổng cộng</span>
                    <span className="text-primary">{formatPrice(totalPrice)}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default BookingPage;


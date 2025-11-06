import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { bookingsAPI } from '../services/api';
import { formatPrice } from '../utils/helpers';
import LoadingSpinner from '../components/LoadingSpinner';
import { toast } from 'react-toastify';

const PaymentPage = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const bookingId = searchParams.get('booking_id');
  const amount = searchParams.get('amount');
  
  const [booking, setBooking] = useState(null);
  const [loading, setLoading] = useState(true);
  const [processing, setProcessing] = useState(false);
  const [selectedMethod, setSelectedMethod] = useState('credit_card');
  const [timeLeft, setTimeLeft] = useState(900); // 15 minutes = 900 seconds

  useEffect(() => {
    if (bookingId) {
      loadBooking();
    }

    // Countdown timer
    const timer = setInterval(() => {
      setTimeLeft((prev) => {
        if (prev <= 1) {
          clearInterval(timer);
          toast.error('Hết thời gian thanh toán');
          navigate('/my-bookings');
          return 0;
        }
        return prev - 1;
      });
    }, 1000);

    return () => clearInterval(timer);
  }, [bookingId]);

  const loadBooking = async () => {
    setLoading(true);
    try {
      const response = await bookingsAPI.getById(bookingId);
      setBooking(response.data.data);
    } catch (error) {
      console.error('Error loading booking:', error);
      toast.error('Không tìm thấy thông tin đặt phòng');
      navigate('/my-bookings');
    } finally {
      setLoading(false);
    }
  };

  const handlePayment = async () => {
    setProcessing(true);

    // Simulate payment processing
    setTimeout(async () => {
      try {
        // Update booking status
        await bookingsAPI.updateStatus(bookingId, {
          status: 'confirmed',
          payment_method: selectedMethod,
        });

        toast.success('Thanh toán thành công!');
        navigate(`/payment-success?booking_id=${bookingId}&amount=${amount}&payment_method=${selectedMethod}`);
      } catch (error) {
        console.error('Payment error:', error);
        toast.error('Thanh toán thất bại. Vui lòng thử lại.');
        setProcessing(false);
      }
    }, 2000);
  };

  if (loading) {
    return <LoadingSpinner fullPage />;
  }

  const minutes = Math.floor(timeLeft / 60);
  const seconds = timeLeft % 60;

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Timer */}
        <div className="bg-warning text-white p-4 rounded-lg text-center mb-6">
          <i className="fas fa-clock mr-2"></i>
          Thời gian thanh toán còn lại: <span className="font-bold text-xl">{minutes}:{seconds.toString().padStart(2, '0')}</span>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Payment Methods */}
          <div className="lg:col-span-2">
            <div className="bg-white rounded-lg shadow-sm p-6">
              <h2 className="text-2xl font-bold text-gray-900 mb-6">Chọn phương thức thanh toán</h2>

              <div className="space-y-4">
                {/* Credit Card */}
                <label
                  className={`flex items-center p-4 border-2 rounded-lg cursor-pointer transition ${
                    selectedMethod === 'credit_card'
                      ? 'border-primary bg-blue-50'
                      : 'border-gray-300 hover:border-gray-400'
                  }`}
                >
                  <input
                    type="radio"
                    name="payment_method"
                    value="credit_card"
                    checked={selectedMethod === 'credit_card'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="mr-4"
                  />
                  <i className="fas fa-credit-card text-2xl text-primary mr-4"></i>
                  <div>
                    <div className="font-semibold">Thẻ tín dụng/ghi nợ</div>
                    <div className="text-sm text-gray-600">Visa, Mastercard, JCB</div>
                  </div>
                </label>

                {/* Bank Transfer */}
                <label
                  className={`flex items-center p-4 border-2 rounded-lg cursor-pointer transition ${
                    selectedMethod === 'bank_transfer'
                      ? 'border-primary bg-blue-50'
                      : 'border-gray-300 hover:border-gray-400'
                  }`}
                >
                  <input
                    type="radio"
                    name="payment_method"
                    value="bank_transfer"
                    checked={selectedMethod === 'bank_transfer'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="mr-4"
                  />
                  <i className="fas fa-university text-2xl text-primary mr-4"></i>
                  <div>
                    <div className="font-semibold">Chuyển khoản ngân hàng</div>
                    <div className="text-sm text-gray-600">Chuyển khoản qua Internet Banking</div>
                  </div>
                </label>

                {/* E-Wallet */}
                <label
                  className={`flex items-center p-4 border-2 rounded-lg cursor-pointer transition ${
                    selectedMethod === 'e_wallet'
                      ? 'border-primary bg-blue-50'
                      : 'border-gray-300 hover:border-gray-400'
                  }`}
                >
                  <input
                    type="radio"
                    name="payment_method"
                    value="e_wallet"
                    checked={selectedMethod === 'e_wallet'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="mr-4"
                  />
                  <i className="fas fa-wallet text-2xl text-primary mr-4"></i>
                  <div>
                    <div className="font-semibold">Ví điện tử</div>
                    <div className="text-sm text-gray-600">MoMo, ZaloPay, VNPay</div>
                  </div>
                </label>

                {/* QR Code */}
                <label
                  className={`flex items-center p-4 border-2 rounded-lg cursor-pointer transition ${
                    selectedMethod === 'qr_code'
                      ? 'border-primary bg-blue-50'
                      : 'border-gray-300 hover:border-gray-400'
                  }`}
                >
                  <input
                    type="radio"
                    name="payment_method"
                    value="qr_code"
                    checked={selectedMethod === 'qr_code'}
                    onChange={(e) => setSelectedMethod(e.target.value)}
                    className="mr-4"
                  />
                  <i className="fas fa-qrcode text-2xl text-primary mr-4"></i>
                  <div>
                    <div className="font-semibold">Quét mã QR</div>
                    <div className="text-sm text-gray-600">Quét mã QR để thanh toán</div>
                  </div>
                </label>
              </div>

              <button
                onClick={handlePayment}
                disabled={processing}
                className="w-full mt-6 px-6 py-4 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition disabled:opacity-50 disabled:cursor-not-allowed text-lg"
              >
                {processing ? <LoadingSpinner size="sm" /> : 'Thanh toán ngay'}
              </button>
            </div>
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow-sm p-6 sticky top-20">
              <h3 className="text-xl font-semibold mb-4">Thông tin đặt phòng</h3>
              {booking && (
                <div className="space-y-3 text-sm">
                  <div>
                    <div className="text-gray-600">Phòng</div>
                    <div className="font-semibold">{booking.room_name}</div>
                  </div>
                  <div>
                    <div className="text-gray-600">Khách sạn</div>
                    <div className="font-semibold">{booking.hotel_name}</div>
                  </div>
                  <div>
                    <div className="text-gray-600">Khách hàng</div>
                    <div className="font-semibold">{booking.guest_name}</div>
                  </div>
                  <div>
                    <div className="text-gray-600">Nhận phòng</div>
                    <div className="font-semibold">{new Date(booking.checkin_date).toLocaleDateString('vi-VN')}</div>
                  </div>
                  <div>
                    <div className="text-gray-600">Trả phòng</div>
                    <div className="font-semibold">{new Date(booking.checkout_date).toLocaleDateString('vi-VN')}</div>
                  </div>
                  <div className="pt-3 border-t">
                    <div className="flex justify-between text-lg font-bold">
                      <span>Tổng tiền</span>
                      <span className="text-primary">{formatPrice(amount)}</span>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PaymentPage;


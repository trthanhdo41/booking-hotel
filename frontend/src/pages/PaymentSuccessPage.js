import React, { useEffect } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { formatPrice } from '../utils/helpers';

const PaymentSuccessPage = () => {
  const [searchParams] = useSearchParams();
  const bookingId = searchParams.get('booking_id');
  const amount = searchParams.get('amount');
  const paymentMethod = searchParams.get('payment_method');

  useEffect(() => {
    // Confetti animation
    const duration = 3 * 1000;
    const end = Date.now() + duration;

    const interval = setInterval(() => {
      if (Date.now() > end) {
        return clearInterval(interval);
      }

      const particleCount = 50;
      const defaults = {
        origin: { y: 0.7 }
      };

      function fire(particleRatio, opts) {
        if (window.confetti) {
          window.confetti({
            ...defaults,
            ...opts,
            particleCount: Math.floor(particleCount * particleRatio)
          });
        }
      }

      fire(0.25, {
        spread: 26,
        startVelocity: 55,
      });
      fire(0.2, {
        spread: 60,
      });
      fire(0.35, {
        spread: 100,
        decay: 0.91,
        scalar: 0.8
      });
      fire(0.1, {
        spread: 120,
        startVelocity: 25,
        decay: 0.92,
        scalar: 1.2
      });
      fire(0.1, {
        spread: 120,
        startVelocity: 45,
      });
    }, 250);

    return () => clearInterval(interval);
  }, []);

  const getPaymentMethodText = (method) => {
    const methods = {
      credit_card: 'Thẻ tín dụng/ghi nợ',
      bank_transfer: 'Chuyển khoản ngân hàng',
      e_wallet: 'Ví điện tử',
      qr_code: 'Quét mã QR',
    };
    return methods[method] || method;
  };

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
      <div className="max-w-2xl w-full">
        <div className="bg-white rounded-lg shadow-lg p-8 text-center">
          {/* Success Icon */}
          <div className="w-24 h-24 bg-success bg-opacity-10 rounded-full flex items-center justify-center mx-auto mb-6">
            <i className="fas fa-check-circle text-6xl text-success"></i>
          </div>

          {/* Title */}
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            Thanh toán thành công!
          </h1>
          <p className="text-gray-600 mb-8">
            Cảm ơn bạn đã đặt phòng. Thông tin xác nhận đã được gửi đến email của bạn.
          </p>

          {/* Transaction Details */}
          <div className="bg-gray-50 rounded-lg p-6 mb-8 text-left">
            <h3 className="font-semibold text-lg mb-4 text-center">Chi tiết giao dịch</h3>
            <div className="space-y-3">
              <div className="flex justify-between">
                <span className="text-gray-600">Mã đặt phòng</span>
                <span className="font-semibold">#{bookingId}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Phương thức thanh toán</span>
                <span className="font-semibold">{getPaymentMethodText(paymentMethod)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">Thời gian</span>
                <span className="font-semibold">{new Date().toLocaleString('vi-VN')}</span>
              </div>
              <div className="flex justify-between pt-3 border-t">
                <span className="text-gray-600">Tổng thanh toán</span>
                <span className="font-bold text-xl text-primary">{formatPrice(amount)}</span>
              </div>
            </div>
          </div>

          {/* Next Steps */}
          <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 text-left">
            <h3 className="font-semibold mb-3 flex items-center">
              <i className="fas fa-info-circle text-primary mr-2"></i>
              Bước tiếp theo
            </h3>
            <ul className="space-y-2 text-sm text-gray-700">
              <li className="flex items-start">
                <i className="fas fa-check text-success mr-2 mt-1"></i>
                <span>Kiểm tra email để xem thông tin chi tiết đặt phòng</span>
              </li>
              <li className="flex items-start">
                <i className="fas fa-check text-success mr-2 mt-1"></i>
                <span>Mang theo CMND/CCCD khi làm thủ tục nhận phòng</span>
              </li>
              <li className="flex items-start">
                <i className="fas fa-check text-success mr-2 mt-1"></i>
                <span>Liên hệ khách sạn nếu có thay đổi hoặc câu hỏi</span>
              </li>
            </ul>
          </div>

          {/* Actions */}
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              to="/my-bookings"
              className="px-8 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary-dark transition"
            >
              <i className="fas fa-list mr-2"></i>
              Xem đặt phòng của tôi
            </Link>
            <Link
              to="/"
              className="px-8 py-3 bg-white text-primary border-2 border-primary font-semibold rounded-lg hover:bg-gray-50 transition"
            >
              <i className="fas fa-home mr-2"></i>
              Về trang chủ
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PaymentSuccessPage;


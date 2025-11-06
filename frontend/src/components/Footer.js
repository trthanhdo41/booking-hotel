import React from 'react';
import { Link } from 'react-router-dom';

const Footer = () => {
  const currentYear = new Date().getFullYear();

  return (
    <footer className="bg-white border-t mt-auto">
      <div className="max-w-6xl mx-auto px-4 py-12">
        {/* Main Footer Columns - Booking.com Style */}
        <div className="grid grid-cols-2 md:grid-cols-5 gap-8 mb-8">
          {/* Support */}
          <div>
            <h3 className="text-sm font-bold text-gray-900 mb-4">Hỗ trợ</h3>
            <ul className="space-y-3">
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Quản lý chuyến đi của bạn
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Liên hệ Dịch vụ Khách hàng
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Trung tâm An toàn
                </a>
              </li>
            </ul>
          </div>

          {/* Discover */}
          <div>
            <h3 className="text-sm font-bold text-gray-900 mb-4">Khám phá</h3>
            <ul className="space-y-3">
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Chương trình Genius
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Ưu đãi theo mùa và ngày lễ
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Bài viết du lịch
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Booking.com cho Doanh nghiệp
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Giải thưởng Đánh giá Du khách
                </a>
              </li>
            </ul>
          </div>

          {/* Terms and settings */}
          <div>
            <h3 className="text-sm font-bold text-gray-900 mb-4">Điều khoản và cài đặt</h3>
            <ul className="space-y-3">
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Quyền riêng tư & cookie
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Điều khoản Dịch vụ
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Tuyên bố về Khả năng Truy cập
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Giải quyết tranh chấp đối tác
                </a>
              </li>
            </ul>
          </div>

          {/* Partners */}
          <div>
            <h3 className="text-sm font-bold text-gray-900 mb-4">Đối tác</h3>
            <ul className="space-y-3">
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Đăng nhập Extranet
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Trợ giúp Đối tác
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Đăng chỗ nghỉ của bạn
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Trở thành đối tác liên kết
                </a>
              </li>
            </ul>
          </div>

          {/* About */}
          <div>
            <h3 className="text-sm font-bold text-gray-900 mb-4">Về chúng tôi</h3>
            <ul className="space-y-3">
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Về Booking.com
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Cách chúng tôi hoạt động
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Tính bền vững
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Trung tâm Báo chí
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Tuyển dụng
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Quan hệ Nhà đầu tư
                </a>
              </li>
              <li>
                <a href="#" className="text-sm text-blue-600 hover:underline">
                  Liên hệ Doanh nghiệp
                </a>
              </li>
            </ul>
          </div>
        </div>

        {/* Bottom Bar - Booking.com Style */}
        <div className="border-t pt-8">
          <div className="flex flex-col md:flex-row justify-between items-center gap-4">
            {/* Left: Currency/Language */}
            <div className="flex items-center space-x-2 text-sm text-gray-600">
              <i className="fas fa-flag"></i>
              <span>VND</span>
            </div>

            {/* Center: Copyright */}
            <div className="text-center text-sm text-gray-600">
              <p>
                Booking.com là một phần của Booking Holdings Inc., công ty dẫn đầu thế giới về dịch vụ du lịch trực tuyến và các dịch vụ liên quan.
              </p>
              <p className="mt-1">
                Bản quyền © 1996-{currentYear} Booking.com™. Tất cả các quyền được bảo lưu.
              </p>
            </div>

            {/* Right: Social Links */}
            <div className="flex space-x-4">
              <a href="#" className="text-gray-600 hover:text-primary transition-colors">
                <i className="fab fa-facebook text-xl"></i>
              </a>
              <a href="#" className="text-gray-600 hover:text-primary transition-colors">
                <i className="fab fa-twitter text-xl"></i>
              </a>
              <a href="#" className="text-gray-600 hover:text-primary transition-colors">
                <i className="fab fa-instagram text-xl"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;


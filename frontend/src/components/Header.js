import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

const Header = () => {
  const { user, logout, isAuthenticated, isAdmin } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const handleLogout = () => {
    logout();
    navigate('/');
    setMobileMenuOpen(false);
  };

  const isHomePage = location.pathname === '/';

  return (
    <header className={`${isHomePage ? 'bg-primary' : 'bg-white'} shadow-sm sticky top-0 z-50 transition-colors duration-300`}>
      {/* Top Bar - Booking.com Style */}
      <div className={`${isHomePage ? 'bg-primary' : 'bg-white border-b border-gray-200'}`}>
        <div className="max-w-7xl mx-auto px-4">
          <div className="flex justify-between items-center h-16">
            {/* Logo */}
            <Link to="/" className="flex items-center space-x-2 group">
              <i className={`fas fa-hotel text-2xl ${isHomePage ? 'text-white' : 'text-primary'} group-hover:scale-110 transition-transform duration-300`}></i>
              <span className={`text-2xl font-bold ${isHomePage ? 'text-white' : 'text-primary'} group-hover:tracking-wide transition-all duration-300`}>
                Booking.com
              </span>
            </Link>

            {/* Right Side - Exact Booking.com Layout */}
            <div className="flex items-center space-x-1">
              {!isAuthenticated() && (
                <button className={`${isHomePage ? 'text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'} px-4 py-2 rounded-md transition-all duration-200 flex items-center space-x-1 text-sm font-medium`}>
                  <span>Đăng chỗ nghỉ của bạn</span>
                </button>
              )}

              <button className={`${isHomePage ? 'text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'} px-3 py-2 rounded-md transition-all duration-200 flex items-center space-x-1.5 text-sm`}>
                <i className="fas fa-globe text-base"></i>
                <span>VND</span>
              </button>

              <button className={`${isHomePage ? 'text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'} px-3 py-2 rounded-md transition-all duration-200 flex items-center space-x-1.5 text-sm`}>
                <img 
                  src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Vietnam.svg/1280px-Flag_of_Vietnam.svg.png" 
                  alt="VN"
                  className="w-4 h-3 object-cover rounded"
                />
                <span>VN</span>
              </button>

              <button className={`${isHomePage ? 'text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'} p-2 rounded-md transition-all duration-200 hover:rotate-12`}>
                <i className="fas fa-question-circle text-lg"></i>
              </button>

              {isAuthenticated() ? (
                <div className="relative group ml-2">
                  <button className={`${isHomePage ? 'text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'} px-4 py-2 rounded-md transition-all duration-200 flex items-center space-x-2 border ${isHomePage ? 'border-white/20' : 'border-gray-300'}`}>
                    <i className="fas fa-user-circle text-lg"></i>
                    <span className="text-sm hidden md:inline">{user?.full_name || user?.username}</span>
                    <i className="fas fa-chevron-down text-xs"></i>
                  </button>
                  
                  {/* Dropdown */}
                  <div className="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-0 -translate-y-2 z-50">
                    <div className="py-2">
                      {isAuthenticated() && (
                        <Link to="/my-bookings" className="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                          <i className="fas fa-calendar-check w-6"></i>
                          <span>Đặt phòng của tôi</span>
                        </Link>
                      )}
                      {isAdmin() && (
                        <Link to="/admin" className="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                          <i className="fas fa-cog w-6"></i>
                          <span>Quản trị</span>
                        </Link>
                      )}
                      <hr className="my-2" />
                      <button
                        onClick={handleLogout}
                        className="w-full flex items-center px-4 py-2 text-red-600 hover:bg-red-50 transition-colors"
                      >
                        <i className="fas fa-sign-out-alt w-6"></i>
                        <span>Đăng xuất</span>
                      </button>
                    </div>
                  </div>
                </div>
              ) : (
                <>
                  <Link
                    to="/register"
                    className={`${isHomePage ? 'text-white hover:bg-white/10' : 'text-gray-700 hover:bg-gray-100'} px-4 py-2 rounded-md font-medium transition-all duration-200 text-sm`}
                  >
                    Đăng ký
                  </Link>
                  <Link
                    to="/login"
                    className={`${isHomePage ? 'bg-white text-primary hover:bg-gray-100' : 'bg-primary text-white hover:bg-primary-dark'} px-4 py-2 rounded-md font-medium transition-all duration-200 text-sm border ${isHomePage ? 'border-white' : 'border-primary'} ml-2`}
                  >
                    Đăng nhập
                  </Link>
                </>
              )}

              {/* Mobile Menu Button */}
              <button
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                className={`md:hidden ${isHomePage ? 'text-white' : 'text-gray-700'}`}
              >
                <i className={`fas ${mobileMenuOpen ? 'fa-times' : 'fa-bars'} text-2xl transition-transform duration-300 ${mobileMenuOpen ? 'rotate-90' : ''}`}></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden bg-white border-t animate-slideDown">
          <nav className="px-4 py-4 space-y-3">
            <Link
              to="/"
              onClick={() => setMobileMenuOpen(false)}
              className="block text-gray-700 hover:text-primary transition-colors"
            >
              <i className="fas fa-home w-6"></i> Trang chủ
            </Link>
            {isAuthenticated() && (
              <>
                <Link
                  to="/search"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block text-gray-700 hover:text-primary transition-colors"
                >
                  <i className="fas fa-search w-6"></i> Tìm kiếm
                </Link>
                <Link
                  to="/my-bookings"
                  onClick={() => setMobileMenuOpen(false)}
                  className="block text-gray-700 hover:text-primary transition-colors"
                >
                  <i className="fas fa-calendar-check w-6"></i> Đặt phòng của tôi
                </Link>
              </>
            )}
            {isAdmin() && (
              <Link
                to="/admin"
                onClick={() => setMobileMenuOpen(false)}
                className="block text-gray-700 hover:text-primary transition-colors"
              >
                <i className="fas fa-cog w-6"></i> Quản trị
              </Link>
            )}
            
            <div className="pt-3 border-t space-y-2">
              {isAuthenticated() ? (
                <>
                  <div className="flex items-center space-x-2 text-gray-700 p-2 bg-gray-50 rounded">
                    <i className="fas fa-user-circle text-xl"></i>
                    <span>{user?.full_name || user?.username}</span>
                  </div>
                  <button
                    onClick={handleLogout}
                    className="w-full px-4 py-2 bg-danger text-white rounded-lg hover:bg-red-600 transition-all duration-300 hover:shadow-lg"
                  >
                    <i className="fas fa-sign-out-alt mr-2"></i>
                    Đăng xuất
                  </button>
                </>
              ) : (
                <>
                  <Link
                    to="/login"
                    onClick={() => setMobileMenuOpen(false)}
                    className="block w-full px-4 py-2 text-center text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition-all duration-300"
                  >
                    Đăng nhập
                  </Link>
                  <Link
                    to="/register"
                    onClick={() => setMobileMenuOpen(false)}
                    className="block w-full px-4 py-2 text-center bg-primary text-white rounded-lg hover:bg-primary-dark transition-all duration-300"
                  >
                    Đăng ký
                  </Link>
                </>
              )}
            </div>
          </nav>
        </div>
      )}
    </header>
  );
};

export default Header;


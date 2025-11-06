import React, { useState } from 'react';
import { Outlet, NavLink } from 'react-router-dom';

const AdminLayout = () => {
  const [sidebarOpen, setSidebarOpen] = useState(true);

  const menuItems = [
    { path: '/admin', label: 'Dashboard', icon: 'fa-chart-line', exact: true },
    { path: '/admin/hotels', label: 'Khách sạn', icon: 'fa-hotel' },
    { path: '/admin/room-types', label: 'Loại phòng', icon: 'fa-bed' },
    { path: '/admin/bookings', label: 'Đặt phòng', icon: 'fa-calendar-check' },
    { path: '/admin/users', label: 'Người dùng', icon: 'fa-users' },
  ];

  return (
    <div className="flex min-h-screen bg-gray-100">
      {/* Sidebar */}
      <aside
        className={`bg-gray-900 text-white transition-all duration-300 ${
          sidebarOpen ? 'w-64' : 'w-20'
        } flex-shrink-0`}
      >
        <div className="p-4 border-b border-gray-800">
          <div className="flex items-center justify-between">
            {sidebarOpen && (
              <div className="flex items-center space-x-2">
                <i className="fas fa-cog text-2xl text-secondary"></i>
                <span className="font-bold text-lg">Admin Panel</span>
              </div>
            )}
            <button
              onClick={() => setSidebarOpen(!sidebarOpen)}
              className="text-white hover:text-secondary transition"
            >
              <i className={`fas ${sidebarOpen ? 'fa-times' : 'fa-bars'} text-xl`}></i>
            </button>
          </div>
        </div>

        <nav className="mt-6">
          {menuItems.map((item) => (
            <NavLink
              key={item.path}
              to={item.path}
              end={item.exact}
              className={({ isActive }) =>
                `flex items-center px-6 py-3 transition ${
                  isActive
                    ? 'bg-primary text-white border-l-4 border-secondary'
                    : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                }`
              }
            >
              <i className={`fas ${item.icon} ${sidebarOpen ? 'mr-3' : ''} text-lg`}></i>
              {sidebarOpen && <span>{item.label}</span>}
            </NavLink>
          ))}
        </nav>
      </aside>

      {/* Main Content */}
      <main className="flex-1 overflow-y-auto">
        <div className="p-8">
          <Outlet />
        </div>
      </main>
    </div>
  );
};

export default AdminLayout;


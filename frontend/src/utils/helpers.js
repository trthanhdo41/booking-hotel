/**
 * Format giá tiền VND
 */
export const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price);
};

/**
 * Format ngày tháng
 */
export const formatDate = (date, format = 'dd/mm/yyyy') => {
  const d = new Date(date);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();

  if (format === 'yyyy-mm-dd') {
    return `${year}-${month}-${day}`;
  }
  return `${day}/${month}/${year}`;
};

/**
 * Tính số ngày giữa 2 ngày
 */
export const calculateDays = (checkin, checkout) => {
  const checkinDate = new Date(checkin);
  const checkoutDate = new Date(checkout);
  const diffTime = Math.abs(checkoutDate - checkinDate);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
};

/**
 * Validate email
 */
export const isValidEmail = (email) => {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
};

/**
 * Validate phone number (Vietnam)
 */
export const isValidPhone = (phone) => {
  const regex = /^(0|\+84)[3|5|7|8|9][0-9]{8}$/;
  return regex.test(phone);
};

/**
 * Get status badge color
 */
export const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-warning text-white',
    confirmed: 'bg-info text-white',
    completed: 'bg-success text-white',
    cancelled: 'bg-danger text-white',
    active: 'bg-success text-white',
    inactive: 'bg-gray-500 text-white',
  };
  return colors[status] || 'bg-gray-500 text-white';
};

/**
 * Get status text (Vietnamese)
 */
export const getStatusText = (status) => {
  const texts = {
    pending: 'Chờ xác nhận',
    confirmed: 'Đã xác nhận',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy',
    active: 'Hoạt động',
    inactive: 'Không hoạt động',
    user: 'Người dùng',
    admin: 'Quản trị viên',
  };
  return texts[status] || status;
};

/**
 * Truncate text
 */
export const truncateText = (text, maxLength) => {
  if (text.length <= maxLength) return text;
  return text.substring(0, maxLength) + '...';
};

/**
 * Parse JSON safely
 */
export const safeJSONParse = (jsonString, defaultValue = []) => {
  try {
    return JSON.parse(jsonString);
  } catch (error) {
    return defaultValue;
  }
};

/**
 * Debounce function
 */
export const debounce = (func, wait) => {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
};


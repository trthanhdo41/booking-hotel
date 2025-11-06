<?php
session_start();
include dirname(__DIR__) . '/config/database.php';

// Kiểm tra đăng nhập admin
$is_admin = false;

// Kiểm tra session admin (đăng nhập trực tiếp vào admin)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $_SESSION['admin_role'] === 'admin') {
    $is_admin = true;
}
// Kiểm tra session user với role admin (đăng nhập từ trang chủ)
elseif (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $is_admin = true;
    // Đồng bộ session admin
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $_SESSION['user_id'];
    $_SESSION['admin_username'] = $_SESSION['username'] ?? 'admin';
    $_SESSION['admin_role'] = 'admin';
}

if (!$is_admin) {
    header('Location: login.php');
    exit;
}

$page_title = "Tổng quan hệ thống - Booking Hotel";

// Lấy thống kê tổng quan
$hotels = getAllHotels();
$room_types = getAllRoomTypes();
$rooms = getAllRooms();
$bookings = getAllBookings();
$users = getAllUsers();

// Thống kê cơ bản
$total_hotels = count($hotels) - 1;
$total_room_types = count($room_types) - 1;
$total_rooms = count($rooms) - 1;
$total_bookings = count($bookings) - 1;
$total_users = count($users) - 1;

// Thống kê booking theo status
$booking_stats = [
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0
];

$total_revenue = 0;

// Cấu trúc bảng bookings: id, user_id, room_id, guest_name, guest_email, guest_phone, checkin_date, checkout_date, guests, total_price, status, created_at, payment_method, payment_id, notes
for ($i = 1; $i < count($bookings); $i++) {
    $booking = $bookings[$i];
    if (count($booking) >= 11) {
        $status = $booking[10] ?? 'pending'; // status ở cột 10 (index 10)
        $price = is_numeric($booking[9]) ? (float)$booking[9] : 0; // total_price ở cột 9 (index 9)
        
        if (isset($booking_stats[$status])) {
            $booking_stats[$status]++;
        }
        
        if ($status === 'completed') {
            $total_revenue += $price;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .stat-card-1 {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stat-card-2 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-card-3 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .stat-card-4 {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .feature-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-8">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center mr-4">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold">Tổng quan hệ thống</h1>
                        <p class="text-lg opacity-90">Quản lý toàn bộ hoạt động Booking Hotel</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm opacity-90">Xin chào,</p>
                        <p class="font-semibold"><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></p>
                    </div>
                    <a href="logout.php" class="glass-effect hover:bg-opacity-30 px-6 py-3 rounded-xl transition-all duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 py-4">
                <a href="index.php" class="text-blue-600 border-b-2 border-blue-600 pb-3 font-semibold flex items-center">
                    <i class="fas fa-tachometer-alt mr-2"></i>Tổng quan
                </a>
                <a href="hotels.php" class="text-gray-600 hover:text-blue-600 pb-3 font-medium flex items-center transition-colors">
                    <i class="fas fa-hotel mr-2"></i>Khách sạn
                </a>
                <a href="room-types.php" class="text-gray-600 hover:text-blue-600 pb-3 font-medium flex items-center transition-colors">
                    <i class="fas fa-bed mr-2"></i>Loại phòng
                </a>
                <a href="rooms.php" class="text-gray-600 hover:text-blue-600 pb-3 font-medium flex items-center transition-colors">
                    <i class="fas fa-door-open mr-2"></i>Phòng
                </a>
                <a href="bookings.php" class="text-gray-600 hover:text-blue-600 pb-3 font-medium flex items-center transition-colors">
                    <i class="fas fa-calendar-check mr-2"></i>Đặt phòng
                </a>
                <a href="users.php" class="text-gray-600 hover:text-blue-600 pb-3 font-medium flex items-center transition-colors">
                    <i class="fas fa-users mr-2"></i>Người dùng
                </a>
                <a href="prices.php" class="text-gray-600 hover:text-blue-600 pb-3 font-medium flex items-center transition-colors">
                    <i class="fas fa-dollar-sign mr-2"></i>Giá cả
                </a>
            </div>
            </div>
        </nav>

        <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Welcome Section -->
        <div class="mb-12 text-center">
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Chào mừng đến với Admin Dashboard</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">Quản lý toàn bộ hệ thống đặt phòng khách sạn một cách hiệu quả và chuyên nghiệp</p>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <div class="stat-card-1 rounded-2xl p-8 text-white card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-hotel text-3xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold"><?php echo number_format($total_hotels); ?></p>
                        <p class="text-sm opacity-90">Khách sạn</p>
                    </div>
                </div>
                <div class="border-t border-white border-opacity-20 pt-4">
                    <p class="text-sm opacity-90">Quản lý toàn bộ khách sạn trong hệ thống</p>
                </div>
                        </div>

            <div class="stat-card-2 rounded-2xl p-8 text-white card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-bed text-3xl"></i>
                        </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold"><?php echo number_format($total_rooms); ?></p>
                        <p class="text-sm opacity-90">Phòng</p>
                    </div>
                </div>
                <div class="border-t border-white border-opacity-20 pt-4">
                    <p class="text-sm opacity-90"><?php echo number_format($total_room_types); ?> loại phòng khác nhau</p>
                    </div>
                </div>

            <div class="stat-card-3 rounded-2xl p-8 text-white card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-calendar-check text-3xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold"><?php echo number_format($total_bookings); ?></p>
                        <p class="text-sm opacity-90">Đặt phòng</p>
                    </div>
                </div>
                <div class="border-t border-white border-opacity-20 pt-4">
                    <p class="text-sm opacity-90"><?php echo number_format($booking_stats['completed']); ?> đã hoàn thành</p>
                </div>
                        </div>

            <div class="stat-card-4 rounded-2xl p-8 text-white card-hover">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold"><?php echo number_format($total_revenue, 0, ',', '.'); ?>₫</p>
                        <p class="text-sm opacity-90">Doanh thu</p>
                    </div>
                </div>
                <div class="border-t border-white border-opacity-20 pt-4">
                    <p class="text-sm opacity-90">Từ <?php echo number_format($total_users); ?> người dùng</p>
                        </div>
                    </div>
                </div>

        <!-- Thống kê chi tiết -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Trạng thái đặt phòng -->
            <div class="feature-card rounded-2xl p-8 card-hover">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-chart-pie text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Trạng thái đặt phòng</h3>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-orange-50 rounded-xl">
                    <div class="flex items-center">
                            <div class="w-3 h-3 bg-orange-500 rounded-full mr-3"></div>
                            <span class="font-medium text-gray-700">Chờ xác nhận</span>
                        </div>
                        <span class="text-2xl font-bold text-orange-600"><?php echo number_format($booking_stats['pending']); ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                            <span class="font-medium text-gray-700">Đã xác nhận</span>
                        </div>
                        <span class="text-2xl font-bold text-blue-600"><?php echo number_format($booking_stats['confirmed']); ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                    <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <span class="font-medium text-gray-700">Hoàn thành</span>
                        </div>
                        <span class="text-2xl font-bold text-green-600"><?php echo number_format($booking_stats['completed']); ?></span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                            <span class="font-medium text-gray-700">Đã hủy</span>
                        </div>
                        <span class="text-2xl font-bold text-red-600"><?php echo number_format($booking_stats['cancelled']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Thống kê hệ thống -->
            <div class="feature-card rounded-2xl p-8 card-hover">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-cogs text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Thống kê hệ thống</h3>
                </div>
                <div class="space-y-6">
                    <div class="text-center p-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl">
                        <i class="fas fa-users text-3xl text-blue-600 mb-3"></i>
                        <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_users); ?></p>
                        <p class="text-gray-600">Người dùng đã đăng ký</p>
                    </div>
                    <div class="text-center p-6 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl">
                        <i class="fas fa-bed text-3xl text-green-600 mb-3"></i>
                        <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_room_types); ?></p>
                        <p class="text-gray-600">Loại phòng khác nhau</p>
                    </div>
                </div>
            </div>

            <!-- Hành động nhanh -->
            <div class="feature-card rounded-2xl p-8 card-hover">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mr-4">
                        <i class="fas fa-bolt text-yellow-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Hành động nhanh</h3>
                </div>
                <div class="space-y-4">
                    <a href="hotels.php" class="block w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-xl text-center font-semibold hover:from-blue-600 hover:to-blue-700 transition-all duration-300">
                        <i class="fas fa-hotel mr-2"></i>Quản lý khách sạn
                    </a>
                    <a href="rooms.php" class="block w-full bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-xl text-center font-semibold hover:from-green-600 hover:to-green-700 transition-all duration-300">
                        <i class="fas fa-bed mr-2"></i>Quản lý phòng
                    </a>
                    <a href="bookings.php" class="block w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-xl text-center font-semibold hover:from-purple-600 hover:to-purple-700 transition-all duration-300">
                        <i class="fas fa-calendar-check mr-2"></i>Xem đặt phòng
                    </a>
                    <a href="users.php" class="block w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white p-4 rounded-xl text-center font-semibold hover:from-orange-600 hover:to-orange-700 transition-all duration-300">
                        <i class="fas fa-users mr-2"></i>Quản lý người dùng
                    </a>
                </div>
            </div>
    </div>

    </main>
</body>
</html>
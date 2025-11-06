<?php
session_start();
include 'config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?message=login_required');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_info = getUserById($user_id);
$user_bookings = getBookingsByUserId($user_id);

$page_title = "Thông tin cá nhân - Booking Hotel";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-hotel text-2xl text-blue-600"></i>
                        <span class="text-xl font-bold text-gray-800">Booking Hotel</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-home mr-1"></i>Trang chủ
                    </a>
                    <a href="search.php" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-search mr-1"></i>Tìm phòng
                    </a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8" data-aos="fade-up">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Thông tin cá nhân</h1>
                <p class="text-gray-600">Quản lý thông tin tài khoản và lịch sử đặt phòng</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Profile Info -->
                <div class="lg:col-span-1" data-aos="fade-right">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="text-center mb-6">
                            <div class="w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user text-3xl text-white"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($user_info['full_name']); ?></h2>
                            <p class="text-gray-600"><?php echo htmlspecialchars($user_info['email']); ?></p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-user text-blue-600 w-5"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Tên đăng nhập</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($user_info['username']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-envelope text-blue-600 w-5"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Email</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($user_info['email']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-phone text-blue-600 w-5"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Số điện thoại</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($user_info['phone']); ?></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-calendar text-blue-600 w-5"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Ngày tham gia</p>
                                    <p class="font-semibold"><?php echo date('d/m/Y', strtotime($user_info['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <a href="booking-history.php" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-history mr-2"></i>
                                Xem lịch sử đặt phòng
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="lg:col-span-2" data-aos="fade-left">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-800">Đặt phòng gần đây</h3>
                            <a href="booking-history.php" class="text-blue-600 hover:text-blue-700 font-semibold">
                                Xem tất cả <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        
                        <?php if (empty($user_bookings)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                            <h4 class="text-xl font-semibold text-gray-600 mb-2">Chưa có đặt phòng nào</h4>
                            <p class="text-gray-500 mb-6">Bắt đầu tìm kiếm và đặt phòng ngay hôm nay!</p>
                            <a href="search.php" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300">
                                <i class="fas fa-search mr-2"></i>Tìm phòng ngay
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach (array_slice($user_bookings, 0, 3) as $booking): ?>
                            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all duration-300">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($booking['hotel_name']); ?></h4>
                                        <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($booking['room_type']); ?></p>
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span><i class="fas fa-calendar-check mr-1"></i><?php echo date('d/m/Y', strtotime($booking['checkin_date'])); ?></span>
                                            <span><i class="fas fa-calendar-times mr-1"></i><?php echo date('d/m/Y', strtotime($booking['checkout_date'])); ?></span>
                                            <span><i class="fas fa-dollar-sign mr-1"></i><?php echo isset($booking['total_price']) && is_numeric($booking['total_price']) ? number_format($booking['total_price'], 0, ',', '.') . ' VNĐ' : 'N/A'; ?></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                            <?php 
                                            switch($booking['status']) {
                                                case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php 
                                            switch($booking['status']) {
                                                case 'confirmed': echo 'Đã xác nhận'; break;
                                                case 'pending': echo 'Chờ xác nhận'; break;
                                                case 'cancelled': echo 'Đã hủy'; break;
                                                case 'completed': echo 'Hoàn thành'; break;
                                                default: echo ucfirst($booking['status']);
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6" data-aos="fade-up">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <i class="fas fa-calendar-check text-3xl text-blue-600 mb-3"></i>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo count($user_bookings); ?></h3>
                    <p class="text-gray-600">Tổng đặt phòng</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <i class="fas fa-check-circle text-3xl text-green-600 mb-3"></i>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?php echo count(array_filter($user_bookings, function($b) { return $b['status'] === 'confirmed'; })); ?>
                    </h3>
                    <p class="text-gray-600">Đã xác nhận</p>
                </div>
                
                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <i class="fas fa-clock text-3xl text-yellow-600 mb-3"></i>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?php echo count(array_filter($user_bookings, function($b) { return $b['status'] === 'pending'; })); ?>
                    </h3>
                    <p class="text-gray-600">Chờ xác nhận</p>
                </div>
            </div>
        </div>
    </main>

    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>

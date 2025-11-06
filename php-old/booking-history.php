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

// Lọc theo trạng thái nếu có
$status_filter = $_GET['status'] ?? '';
if ($status_filter) {
    $user_bookings = array_filter($user_bookings, function($booking) use ($status_filter) {
        return $booking['status'] === $status_filter;
    });
}

$page_title = "Lịch sử đặt phòng - Booking Hotel";
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
                    <a href="profile.php" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-user mr-1"></i>Thông tin cá nhân
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Lịch sử đặt phòng</h1>
                <p class="text-gray-600">Xem tất cả các đặt phòng của bạn</p>
            </div>

            <!-- Filter Tabs -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8" data-aos="fade-up">
                <div class="flex flex-wrap gap-4">
                    <a href="booking-history.php" class="px-6 py-3 rounded-xl font-semibold transition-all duration-300 <?php echo !$status_filter ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-list mr-2"></i>Tất cả
                    </a>
                    <a href="booking-history.php?status=confirmed" class="px-6 py-3 rounded-xl font-semibold transition-all duration-300 <?php echo $status_filter === 'confirmed' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-check-circle mr-2"></i>Đã xác nhận
                    </a>
                    <a href="booking-history.php?status=pending" class="px-6 py-3 rounded-xl font-semibold transition-all duration-300 <?php echo $status_filter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-clock mr-2"></i>Chờ xác nhận
                    </a>
                    <a href="booking-history.php?status=cancelled" class="px-6 py-3 rounded-xl font-semibold transition-all duration-300 <?php echo $status_filter === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <i class="fas fa-times-circle mr-2"></i>Đã hủy
                    </a>
                </div>
            </div>

            <!-- Bookings List -->
            <div class="space-y-6" data-aos="fade-up">
                <?php if (empty($user_bookings)): ?>
                <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">
                        <?php if ($status_filter): ?>
                            Không có đặt phòng <?php echo $status_filter === 'confirmed' ? 'đã xác nhận' : ($status_filter === 'pending' ? 'chờ xác nhận' : 'đã hủy'); ?>
                        <?php else: ?>
                            Chưa có đặt phòng nào
                        <?php endif; ?>
                    </h3>
                    <p class="text-gray-500 mb-6">
                        <?php if ($status_filter): ?>
                            Thử chọn bộ lọc khác hoặc đặt phòng mới
                        <?php else: ?>
                            Bắt đầu tìm kiếm và đặt phòng ngay hôm nay!
                        <?php endif; ?>
                    </p>
                    <a href="search.php" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-300">
                        <i class="fas fa-search mr-2"></i>Tìm phòng ngay
                    </a>
                </div>
                <?php else: ?>
                <?php foreach ($user_bookings as $booking): ?>
                <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($booking['hotel_name']); ?></h3>
                                    <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($booking['room_type']); ?></p>
                                    <p class="text-sm text-gray-500">Mã đặt phòng: #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                                        <?php 
                                        switch($booking['status']) {
                                            case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <i class="fas fa-circle text-xs mr-2"></i>
                                        <?php 
                                        switch($booking['status']) {
                                            case 'confirmed': echo 'Đã xác nhận'; break;
                                            case 'pending': echo 'Chờ xác nhận'; break;
                                            case 'cancelled': echo 'Đã hủy'; break;
                                            default: echo ucfirst($booking['status']);
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-calendar-check text-blue-600 w-5"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Ngày nhận phòng</p>
                                        <p class="font-semibold"><?php echo date('d/m/Y', strtotime($booking['checkin_date'])); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-calendar-times text-blue-600 w-5"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Ngày trả phòng</p>
                                        <p class="font-semibold"><?php echo date('d/m/Y', strtotime($booking['checkout_date'])); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-user text-blue-600 w-5"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Tên khách</p>
                                        <p class="font-semibold"><?php echo htmlspecialchars($booking['guest_name']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-dollar-sign text-blue-600 w-5"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Tổng tiền</p>
                                        <p class="font-semibold text-green-600"><?php echo formatPrice($booking['total_price']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($booking['payment_method']): ?>
                            <div class="flex items-center space-x-3 mb-4">
                                <i class="fas fa-credit-card text-blue-600 w-5"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Phương thức thanh toán</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($booking['payment_method']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($booking['notes']): ?>
                            <div class="flex items-start space-x-3">
                                <i class="fas fa-sticky-note text-blue-600 w-5 mt-1"></i>
                                <div>
                                    <p class="text-sm text-gray-600">Ghi chú</p>
                                    <p class="font-semibold"><?php echo htmlspecialchars($booking['notes']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
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

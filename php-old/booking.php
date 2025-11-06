<?php
session_start();
include 'config/database.php';

$is_logged_in = isset($_SESSION['user_id']);
$booking_data = [];
$error = '';

// Lấy thông tin từ URL parameters
$room_type_id = $_GET['room_type_id'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';

if (!$room_type_id || !$checkin || !$checkout) {
    header('Location: search.php');
    exit;
}

// Lấy thông tin phòng từ Google Sheets
$hotels_data = getAllHotels();
$room_types_data = getAllRoomTypes();

$room_info = null;
foreach ($room_types_data as $index => $room_type_row) {
    if ($index === 0) continue; // Skip header row
    if (count($room_type_row) >= 5 && $room_type_row[0] == $room_type_id) {
        // Tìm thông tin khách sạn
        foreach ($hotels_data as $hotel_index => $hotel_row) {
            if ($hotel_index === 0) continue; // Skip header row
            if (count($hotel_row) >= 6 && $hotel_row[0] == $room_type_row[1]) {
                $room_info = [
                    'id' => $room_type_row[0],
                    'hotel_id' => $room_type_row[1],
                    'name' => $room_type_row[2],
                    'description' => $room_type_row[3],
                    'price' => $room_type_row[4],
                    'max_guests' => $room_type_row[5] ?? 2,
                    'hotel_name' => $hotel_row[1],
                    'address' => $hotel_row[2],
                    'phone' => $hotel_row[4],
                    'email' => $hotel_row[5],
                    'rating' => $hotel_row[6] ?? 5,
                    'image_url' => $hotel_row[7] ?? ''
                ];
                break 2;
            }
        }
    }
}

if (!$room_info) {
    header('Location: search.php');
    exit;
}

// Tính toán giá
$nights = calculateDays($checkin, $checkout);
$total_price = $room_info['price'] * $nights;

// Xử lý đặt phòng
if ($_POST) {
    $customer_name = $_POST['customer_name'] ?? '';
    $customer_email = $_POST['customer_email'] ?? '';
    $customer_phone = $_POST['customer_phone'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (!$customer_name || !$customer_email || !$customer_phone) {
        $error = "Vui lòng điền đầy đủ thông tin";
    } else {
        // Tạo booking
        $booking_data = [
            'user_id' => $_SESSION['user_id'] ?? 0, // Thêm user_id
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'checkin_date' => $checkin,
            'checkout_date' => $checkout,
            'guests' => 2, // Mặc định 2 khách
            'total_price' => $total_price,
            'status' => 'pending',
            'payment_method' => 'qr_code',
            'notes' => $notes
        ];
        
        // Lưu vào database (Google Sheets)
        $result = addBooking($booking_data);
        if ($result) {
            // Lấy ID thực từ database (ID mới nhất)
            $bookings = getAllBookings();
            $latest_booking_id = 1;
            for ($i = count($bookings) - 1; $i >= 1; $i--) {
                $booking = $bookings[$i];
                if (count($booking) >= 1 && is_numeric($booking[0])) {
                    $latest_booking_id = (int)$booking[0];
                    break;
                }
            }
            
            // Gửi email xác nhận
            include 'config/email.php';
            $email_data = array_merge($booking_data, [
                'id' => $latest_booking_id,
                'customer_name' => $booking_data['guest_name'],
                'customer_email' => $booking_data['guest_email'],
                'hotel_name' => $room_info['hotel_name'],
                'room_type_name' => $room_info['name'],
                'max_guests' => $room_info['max_guests']
            ]);
            sendBookingConfirmation($email_data);
            
            header('Location: payment.php?booking_id=' . $latest_booking_id . '&amount=' . $total_price);
            exit;
        } else {
            $error = "Có lỗi xảy ra khi đặt phòng. Vui lòng thử lại.";
        }
    }
}

$page_title = "Đặt phòng - " . $room_info['hotel_name'];
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
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-bg {
            background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&h=1080&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.8) 0%, rgba(147, 51, 234, 0.6) 100%);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        @keyframes wiggle {
            0%, 7% { transform: rotateZ(0); }
            15% { transform: rotateZ(-15deg); }
            20% { transform: rotateZ(10deg); }
            25% { transform: rotateZ(-10deg); }
            30% { transform: rotateZ(6deg); }
            35% { transform: rotateZ(-4deg); }
            40%, 100% { transform: rotateZ(0); }
        }
        .animate-wiggle {
            animation: wiggle 2s ease-in-out infinite;
        }
        .animate-wiggle:nth-child(2) {
            animation-delay: 0.5s;
        }
    </style>
</head>
<body class="bg-gray-50 pt-20">
    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-24 right-4 z-[60] space-y-2"></div>

    <!-- Header -->
    <header class="fixed top-0 left-0 right-0 z-50 backdrop-blur-md bg-white/90 border-b border-white/20 shadow-lg">
        <nav class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="index.php" class="flex items-center space-x-3 group">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                            <i class="fas fa-hotel text-white text-xl"></i>
                        </div>
                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full animate-pulse"></div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            Booking Hotel
                        </h1>
                        <p class="text-xs text-gray-500 -mt-1">Premium Experience</p>
                    </div>
                </a>

                <!-- Navigation -->
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                        <i class="fas fa-home mr-2"></i>Trang chủ
                    </a>
                    <a href="search.php" class="text-gray-700 hover:text-blue-600 transition-colors">
                        <i class="fas fa-search mr-2"></i>Tìm phòng
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-bg py-20 relative overflow-hidden">
        <div class="container mx-auto px-4 hero-content">
            <div class="max-w-4xl mx-auto text-center text-white" data-aos="fade-up">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight drop-shadow-2xl">
                    Đặt phòng
                    <span class="block text-yellow-300"><?php echo $room_info['hotel_name']; ?></span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-95 drop-shadow-lg">
                    Hoàn tất thông tin để đặt phòng của bạn
                </p>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Booking Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-8 hover-lift" data-aos="fade-up">
                    <h2 class="text-3xl font-bold text-gray-800 mb-8">
                        <i class="fas fa-user-edit mr-3 text-blue-600"></i>Thông tin đặt phòng
                    </h2>
                    
                    <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700"><?php echo $error; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-user mr-2 text-blue-600"></i>Họ và tên *
                                </label>
                                <input type="text" name="customer_name" required
                                       class="w-full p-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                       placeholder="Nhập họ và tên">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-2 text-blue-600"></i>Email *
                                </label>
                                <input type="email" name="customer_email" required
                                       class="w-full p-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                       placeholder="Nhập email">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-phone mr-2 text-blue-600"></i>Số điện thoại *
                            </label>
                            <input type="tel" name="customer_phone" required
                                   class="w-full p-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Nhập số điện thoại">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-comment mr-2 text-blue-600"></i>Ghi chú thêm
                            </label>
                            <textarea name="notes" rows="4"
                                      class="w-full p-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                      placeholder="Yêu cầu đặc biệt, ghi chú..."></textarea>
                        </div>
                        
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg">
                            <i class="fas fa-credit-card mr-2"></i>Xác nhận đặt phòng
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Booking Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-8 sticky top-24 hover-lift" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-receipt mr-3 text-green-600"></i>Tóm tắt đặt phòng
                    </h3>
                    
                    <!-- Hotel Info -->
                    <div class="mb-6">
                        <div class="relative h-48 rounded-xl overflow-hidden mb-4">
                            <img src="<?php echo $room_info['image_url'] ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop'; ?>" 
                                 alt="<?php echo $room_info['hotel_name']; ?>" 
                                 class="w-full h-full object-cover">
                        </div>
                        <h4 class="text-xl font-bold text-gray-800 mb-2"><?php echo $room_info['hotel_name']; ?></h4>
                        <p class="text-gray-600 mb-2"><?php echo $room_info['name']; ?></p>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <?php echo $room_info['address']; ?>
                        </p>
                    </div>
                    
                    <!-- Booking Details -->
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Nhận phòng:</span>
                            <span class="font-semibold"><?php echo formatDate($checkin); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Trả phòng:</span>
                            <span class="font-semibold"><?php echo formatDate($checkout); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Số đêm:</span>
                            <span class="font-semibold"><?php echo $nights; ?> đêm</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Số khách:</span>
                            <span class="font-semibold"><?php echo $room_info['max_guests']; ?> khách</span>
                        </div>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Giá/đêm:</span>
                            <span class="font-semibold"><?php echo formatPrice($room_info['price']); ?></span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Số đêm:</span>
                            <span class="font-semibold"><?php echo $nights; ?></span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold text-blue-600">
                            <span>Tổng cộng:</span>
                            <span><?php echo formatPrice($total_price); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Buttons -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col space-y-4">
        <!-- Call Button -->
        <button onclick="simulateCall()" class="w-14 h-14 bg-green-500 hover:bg-green-600 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300 flex items-center justify-center group animate-wiggle">
            <i class="fas fa-phone text-xl group-hover:animate-pulse"></i>
        </button>
        
        <!-- Messenger Button -->
        <button onclick="simulateMessenger()" class="w-14 h-14 bg-blue-500 hover:bg-blue-600 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-300 flex items-center justify-center group animate-wiggle">
            <i class="fab fa-facebook-messenger text-xl group-hover:animate-bounce"></i>
        </button>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-hotel text-2xl text-blue-400"></i>
                        <span class="text-xl font-bold">Booking Hotel</span>
                    </div>
                    <p class="text-gray-400">Dịch vụ đặt phòng khách sạn hàng đầu Việt Nam</p>
                </div>
                
                <div>
                    <h3 class="font-bold mb-4">Liên kết</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Về chúng tôi</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Liên hệ</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Hỗ trợ</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold mb-4">Dịch vụ</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition-colors">Đặt phòng</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Tìm kiếm</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Thanh toán</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold mb-4">Liên hệ</h3>
                    <div class="space-y-2 text-gray-400">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-phone text-blue-400"></i>
                            <span>1900 1234</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-envelope text-blue-400"></i>
                            <span>info@bookinghotel.com</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-map-marker-alt text-blue-400"></i>
                            <span>Hà Nội, Việt Nam</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 Booking Hotel. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-24 right-4 z-[60] p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
            
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            toast.className += ` ${colors[type]}`;
            toast.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 hover:opacity-70">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }, 5000);
        }

        // Simulate call function
        function simulateCall() {
            alert('📞 Đang gọi hotline: 1900 1234\n\nChức năng này sẽ mở ứng dụng gọi điện thoại trên thiết bị của bạn.');
        }

        // Simulate messenger function
        function simulateMessenger() {
            alert('💬 Đang mở Facebook Messenger\n\nChức năng này sẽ mở ứng dụng Messenger để chat với chúng tôi.');
        }
    </script>
</body>
</html>

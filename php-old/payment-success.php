<?php
session_start();
include 'config/database.php';

$booking_id = $_GET['booking_id'] ?? '';
$amount = $_GET['amount'] ?? 0;
$payment_method = $_GET['payment_method'] ?? '';

// Mapping phương thức thanh toán
$payment_methods = [
    'creditCard' => 'Thẻ tín dụng/ghi nợ',
    'banking' => 'Chuyển khoản ngân hàng',
    'ewallet' => 'Ví điện tử',
    'qr' => 'QR Code'
];

$payment_method_name = $payment_methods[$payment_method] ?? 'Không xác định';

// Lấy thông tin booking để gửi email
$booking_info = null;
if ($booking_id) {
    $bookings = getAllBookings();
    foreach ($bookings as $index => $booking) {
        if ($index === 0) continue; // Skip header
        if (count($booking) >= 1 && $booking[0] == $booking_id) {
            // Lấy thông tin khách sạn và loại phòng
            $hotels = getAllHotels();
            $room_types = getAllRoomTypes();
            
            $hotel_name = 'Khách sạn không xác định';
            $room_type_name = 'Loại phòng không xác định';
            
            // Tìm thông tin loại phòng trước
            foreach ($room_types as $room_type_index => $room_type) {
                if ($room_type_index === 0) continue;
                if (count($room_type) >= 2 && $room_type[0] == $booking[2]) { // room_id
                    $room_type_name = $room_type[2] ?? 'Loại phòng không xác định';
                    // Lấy hotel_id từ room_type (cột 1)
                    $hotel_id = $room_type[1] ?? null;
                    
                    // Tìm thông tin khách sạn dựa trên hotel_id
                    if ($hotel_id) {
                        foreach ($hotels as $hotel_index => $hotel) {
                            if ($hotel_index === 0) continue;
                            if (count($hotel) >= 2 && $hotel[0] == $hotel_id) {
                                $hotel_name = $hotel[1] ?? 'Khách sạn không xác định';
                                break;
                            }
                        }
                    }
                    break;
                }
            }
            
            // Tìm total_price - thường ở vị trí 9 nhưng có thể bị lệch
            $total_price = 0;
            // Thử vị trí 9 trước (theo cột chuẩn)
            if (isset($booking[9]) && is_numeric($booking[9]) && $booking[9] > 0) {
                $total_price = (int)$booking[9];
            } else {
                // Nếu không tìm thấy, tìm trong các cột khác
                for ($i = 8; $i < count($booking); $i++) {
                    if (isset($booking[$i]) && is_numeric($booking[$i]) && $booking[$i] > 0) {
                        $total_price = (int)$booking[$i];
                        break;
                    }
                }
            }
            
            // Nếu vẫn không tìm thấy, dùng amount từ URL
            if ($total_price == 0 && isset($_GET['amount']) && is_numeric($_GET['amount'])) {
                $total_price = (int)$_GET['amount'];
            }
            
            $booking_info = [
                'id' => $booking[0],
                'customer_name' => $booking[3] ?? 'Khách hàng', // guest_name
                'customer_email' => $booking[4] ?? '', // guest_email
                'hotel_name' => $hotel_name,
                'room_type_name' => $room_type_name,
                'checkin_date' => $booking[6] ?? '', // checkin_date
                'checkout_date' => $booking[7] ?? '', // checkout_date
                'total_price' => $total_price, // Tìm giá trị numeric thật
                'status' => 'completed',
                'payment_method' => $payment_method_name
            ];
            break;
        }
    }
}

// Gửi email thông báo thanh toán thành công
if ($booking_info && $booking_info['customer_email']) {
    include 'config/email.php';
    $email_result = sendPaymentConfirmation($booking_info);
    
    // Log kết quả gửi email (có thể xóa sau)
    error_log("Email sent to: " . $booking_info['customer_email'] . " - Result: " . ($email_result ? 'SUCCESS' : 'FAILED'));
}

$page_title = "Thanh toán thành công - Booking Hotel";
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
            background-image: url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1920&h=1080&fit=crop');
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
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.8) 0%, rgba(16, 185, 129, 0.6) 100%);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .success-animation {
            animation: successPulse 2s ease-in-out infinite;
        }
        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .receipt-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 2px solid #e2e8f0;
        }
    </style>
</head>
<body class="bg-gray-50 pt-20">
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

    <!-- Success Section -->
    <section class="hero-bg py-20 relative overflow-hidden">
        <div class="container mx-auto px-4 hero-content">
            <div class="max-w-4xl mx-auto text-center text-white" data-aos="fade-up">
                <div class="success-animation mb-8">
                    <div class="w-32 h-32 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto">
                        <i class="fas fa-check-circle text-6xl text-green-400"></i>
                    </div>
                </div>
                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight drop-shadow-2xl">
                    Thanh toán thành công!
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-95 drop-shadow-lg">
                    Giao dịch đã được xử lý thành công
                </p>
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 inline-block">
                    <p class="text-lg font-semibold">Mã giao dịch: <span class="text-yellow-300"><?php echo uniqid('TXN'); ?></span></p>
                </div>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Receipt -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 receipt-card" data-aos="fade-up">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-receipt mr-3 text-green-600"></i>Hóa đơn thanh toán
                    </h2>
                    <p class="text-gray-600">Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Thông tin giao dịch:</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Mã đặt phòng:</span>
                                <span class="font-semibold text-gray-800"><?php echo $booking_id; ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Phương thức thanh toán:</span>
                                <span class="font-semibold text-blue-600"><?php echo $payment_method_name; ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Mã giao dịch:</span>
                                <span class="font-semibold text-gray-800"><?php echo uniqid('TXN'); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Thời gian:</span>
                                <span class="font-semibold text-gray-800"><?php echo date('d/m/Y'); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Phương thức:</span>
                                <span class="font-semibold text-gray-800">Thanh toán online</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Trạng thái:</span>
                                <span class="font-semibold text-green-600">
                                    <i class="fas fa-check-circle mr-1"></i>Thành công
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Chi tiết thanh toán:</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Số tiền:</span>
                                <span class="font-semibold text-gray-800"><?php echo formatPrice($amount); ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Phí giao dịch:</span>
                                <span class="font-semibold text-gray-800">Miễn phí</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between items-center text-lg font-bold text-green-600">
                                    <span>Tổng thanh toán:</span>
                                    <span><?php echo formatPrice($amount); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8" data-aos="fade-up" data-aos-delay="200">
                <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">
                    <i class="fas fa-list-check mr-3 text-blue-600"></i>Bước tiếp theo
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center p-6 bg-blue-50 rounded-xl">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-envelope text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Email xác nhận</h3>
                        <p class="text-gray-600 text-sm">Chúng tôi đã gửi email xác nhận chi tiết đặt phòng và thanh toán</p>
                    </div>
                    
                    <div class="text-center p-6 bg-green-50 rounded-xl">
                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-phone text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Liên hệ khách sạn</h3>
                        <p class="text-gray-600 text-sm">Gọi điện xác nhận trước ngày nhận phòng để đảm bảo</p>
                    </div>
                    
                    <div class="text-center p-6 bg-purple-50 rounded-xl">
                        <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-check text-white text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Check-in</h3>
                        <p class="text-gray-600 text-sm">Mang theo CMND/CCCD và mã đặt phòng để check-in</p>
                    </div>
                </div>
            </div>
            
            <!-- Support -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl p-8 text-white" data-aos="fade-up" data-aos-delay="400">
                <h2 class="text-3xl font-bold mb-6 text-center">
                    <i class="fas fa-headset mr-3"></i>Hỗ trợ khách hàng
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-phone text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Hotline 24/7</h3>
                        <p class="text-white/80">1900-xxxx</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-envelope text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Email hỗ trợ</h3>
                        <p class="text-white/80">support@bookinghotel.com</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fab fa-facebook-messenger text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Chat trực tuyến</h3>
                        <p class="text-white/80">Messenger 24/7</p>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="text-center space-y-4 mt-8" data-aos="fade-up" data-aos-delay="600">
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="search.php" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg inline-flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i>Đặt phòng khác
                    </a>
                    <a href="index.php" class="bg-white border-2 border-blue-600 text-blue-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-blue-50 transform hover:scale-105 transition-all duration-300 shadow-lg inline-flex items-center justify-center">
                        <i class="fas fa-home mr-2"></i>Về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Confetti effect
        function createConfetti() {
            const colors = ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EF4444'];
            const confettiCount = 100;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'fixed';
                confetti.style.width = '10px';
                confetti.style.height = '10px';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-10px';
                confetti.style.zIndex = '9999';
                confetti.style.borderRadius = '50%';
                confetti.style.animation = `fall ${Math.random() * 3 + 2}s linear forwards`;
                
                document.body.appendChild(confetti);
                
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
        }

        // Add CSS for confetti animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                }
            }
        `;
        document.head.appendChild(style);

        // Trigger confetti on page load
        window.addEventListener('load', () => {
            setTimeout(createConfetti, 500);
        });
    </script>
</body>
</html>

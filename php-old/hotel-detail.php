<?php
session_start();
include 'config/database.php';

$hotel_id = $_GET['id'] ?? '';
$checkin = $_GET['checkin'] ?? '';
$checkout = $_GET['checkout'] ?? '';

if (!$hotel_id) {
    header('Location: search.php');
    exit;
}

// Lấy thông tin khách sạn từ Google Sheets
$hotels_data = getAllHotels();
$room_types_data = getAllRoomTypes();

$hotel = null;
foreach ($hotels_data as $index => $hotel_row) {
    if ($index === 0) continue; // Skip header row
    if (count($hotel_row) >= 6 && $hotel_row[0] == $hotel_id) {
        $hotel = [
            'id' => $hotel_row[0],
            'name' => $hotel_row[1],
            'address' => $hotel_row[2],
            'city' => $hotel_row[3],
            'phone' => $hotel_row[4],
            'email' => $hotel_row[5],
            'rating' => $hotel_row[6] ?? 5,
            'image_url' => $hotel_row[7] ?? '',
            'description' => $hotel_row[8] ?? ''
        ];
        break;
    }
}

if (!$hotel) {
    header('Location: search.php');
    exit;
}

// Lấy các loại phòng của khách sạn
$room_types = [];
foreach ($room_types_data as $index => $room_type_row) {
    if ($index === 0) continue; // Skip header row
    if (count($room_type_row) >= 5 && $room_type_row[1] == $hotel_id) {
        $room_types[] = [
            'id' => $room_type_row[0],
            'hotel_id' => $room_type_row[1],
            'name' => $room_type_row[2],
            'description' => $room_type_row[3],
            'price' => $room_type_row[4],
            'max_guests' => $room_type_row[5] ?? 2,
            'size' => $room_type_row[6] ?? '25m²',
            'image_url' => $room_type_row[7] ?? '',
            'available_rooms' => 1 // Simplified
        ];
    }
}

$page_title = $hotel['name'] . " - Booking Hotel";
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
            background-image: url('<?php echo $hotel['image_url'] ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&h=1080&fit=crop'; ?>');
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
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0.3) 100%);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
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
                    <?php echo $hotel['name']; ?>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-95 drop-shadow-lg">
                    <?php echo $hotel['address']; ?>
                </p>
                <div class="flex items-center justify-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <div class="flex text-yellow-400">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $hotel['rating'] ? '' : 'opacity-30'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-lg font-semibold"><?php echo $hotel['rating']; ?>/5</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-phone text-green-400"></i>
                        <span><?php echo $hotel['phone']; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <!-- Hotel Info -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-8 hover-lift" data-aos="fade-up">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-info-circle mr-3 text-blue-600"></i>Thông tin khách sạn
                    </h2>
                    
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-3">Mô tả</h3>
                            <p class="text-gray-600 leading-relaxed">
                                <?php echo $hotel['description'] ?: 'Khách sạn hiện đại với đầy đủ tiện nghi, phục vụ khách hàng 24/7. Vị trí thuận tiện, gần các điểm du lịch nổi tiếng.'; ?>
                            </p>
                        </div>
                        
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-3">Tiện nghi</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-wifi text-blue-600"></i>
                                    <span class="text-gray-600">WiFi miễn phí</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-car text-green-600"></i>
                                    <span class="text-gray-600">Bãi đỗ xe</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-swimming-pool text-cyan-600"></i>
                                    <span class="text-gray-600">Hồ bơi</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-utensils text-orange-600"></i>
                                    <span class="text-gray-600">Nhà hàng</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-dumbbell text-purple-600"></i>
                                    <span class="text-gray-600">Phòng gym</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-concierge-bell text-red-600"></i>
                                    <span class="text-gray-600">Dịch vụ 24/7</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-xl font-semibold text-gray-800 mb-3">Liên hệ</h3>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                                    <span class="text-gray-600"><?php echo $hotel['address']; ?></span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-phone text-green-600"></i>
                                    <span class="text-gray-600"><?php echo $hotel['phone']; ?></span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-envelope text-purple-600"></i>
                                    <span class="text-gray-600"><?php echo $hotel['email']; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-8 sticky top-24 hover-lift" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-calendar-alt mr-3 text-green-600"></i>Đặt phòng
                    </h3>
                    
                    <form method="GET" action="search.php" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-check mr-2 text-blue-600"></i>Nhận phòng
                            </label>
                            <input type="date" name="checkin" value="<?php echo $checkin; ?>" 
                                   class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-calendar-times mr-2 text-blue-600"></i>Trả phòng
                            </label>
                            <input type="date" name="checkout" value="<?php echo $checkout; ?>" 
                                   class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-users mr-2 text-blue-600"></i>Số khách
                            </label>
                            <select name="guests" class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="1">1 khách</option>
                                <option value="2" selected>2 khách</option>
                                <option value="3">3 khách</option>
                                <option value="4">4 khách</option>
                                <option value="5">5+ khách</option>
                            </select>
                        </div>
                        
                        <input type="hidden" name="location" value="<?php echo $hotel['address']; ?>">
                        
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl font-bold hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg">
                            <i class="fas fa-search mr-2"></i>Tìm phòng
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Room Types -->
        <div class="mb-12" data-aos="fade-up">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">
                <i class="fas fa-bed mr-3 text-blue-600"></i>Các loại phòng
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($room_types as $index => $room): ?>
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover-lift" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="relative h-48 overflow-hidden">
                        <img src="<?php echo $room['image_url'] ?: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=400&h=300&fit=crop'; ?>" 
                             alt="<?php echo $room['name']; ?>" 
                             class="w-full h-full object-cover">
                        <div class="absolute top-4 right-4">
                            <div class="bg-white/90 backdrop-blur-sm rounded-full px-3 py-1 flex items-center space-x-1">
                                <i class="fas fa-bed text-blue-600"></i>
                                <span class="font-semibold text-gray-800"><?php echo $room['available_rooms']; ?> phòng</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo $room['name']; ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo $room['description']; ?></p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-users text-blue-600"></i>
                                <span class="text-gray-600">Tối đa <?php echo $room['max_guests']; ?> khách</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-expand-arrows-alt text-purple-600"></i>
                                <span class="text-gray-600"><?php echo $room['size'] ?: '25m²'; ?></span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <div class="text-2xl font-bold text-blue-600"><?php echo formatPrice($room['price']); ?></div>
                                <div class="text-sm text-gray-500">/ đêm</div>
                            </div>
                        </div>
                        
                        <a href="booking.php?room_type_id=<?php echo $room['id']; ?>&checkin=<?php echo $checkin; ?>&checkout=<?php echo $checkout; ?>" 
                           class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-3 rounded-xl font-bold hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg text-center block">
                            <i class="fas fa-calendar-check mr-2"></i>Đặt phòng
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
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

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="checkin"]').setAttribute('min', today);
        document.querySelector('input[name="checkout"]').setAttribute('min', today);

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

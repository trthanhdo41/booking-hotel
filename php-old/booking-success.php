<?php
session_start();
include 'config/database.php';

$booking_id = $_GET['booking_id'] ?? '';
$page_title = "Đặt phòng thành công - Booking Hotel";
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
                        <i class="fas fa-check text-6xl text-green-400"></i>
                    </div>
                </div>
                <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight drop-shadow-2xl">
                    Đặt phòng thành công!
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-95 drop-shadow-lg">
                    Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi
                </p>
                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 inline-block">
                    <p class="text-lg font-semibold">Mã đặt phòng: <span class="text-yellow-300"><?php echo $booking_id; ?></span></p>
                </div>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <!-- Success Info -->
            <div class="bg-white rounded-2xl shadow-xl p-8 mb-8" data-aos="fade-up">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">
                        <i class="fas fa-info-circle mr-3 text-blue-600"></i>Thông tin đặt phòng
                    </h2>
                    <p class="text-gray-600">Chúng tôi đã gửi email xác nhận đến địa chỉ email của bạn</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Bước tiếp theo:</h3>
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-envelope text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Kiểm tra email</p>
                                    <p class="text-gray-600 text-sm">Chúng tôi đã gửi email xác nhận chi tiết đặt phòng</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-phone text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Liên hệ khách sạn</p>
                                    <p class="text-gray-600 text-sm">Gọi điện xác nhận trước ngày nhận phòng</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-calendar-check text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Đến nhận phòng</p>
                                    <p class="text-gray-600 text-sm">Mang theo CMND/CCCD để check-in</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Hỗ trợ khách hàng:</h3>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                                <i class="fas fa-phone text-green-600"></i>
                                <div>
                                    <p class="font-semibold text-gray-800">Hotline 24/7</p>
                                    <p class="text-gray-600 text-sm">1900-xxxx</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                                <i class="fas fa-envelope text-blue-600"></i>
                                <div>
                                    <p class="font-semibold text-gray-800">Email hỗ trợ</p>
                                    <p class="text-gray-600 text-sm">support@bookinghotel.com</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-xl">
                                <i class="fab fa-facebook-messenger text-purple-600"></i>
                                <div>
                                    <p class="font-semibold text-gray-800">Chat trực tuyến</p>
                                    <p class="text-gray-600 text-sm">Messenger 24/7</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="text-center space-y-4" data-aos="fade-up" data-aos-delay="200">
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
            const confettiCount = 50;
            
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

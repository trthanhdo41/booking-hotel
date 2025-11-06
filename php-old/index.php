<?php
session_start();
include 'config/database.php';

$page_title = "Booking Hotel - Tìm phòng khách sạn tốt nhất";
$is_logged_in = isset($_SESSION['user_id']);
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
    
    <!-- Custom CSS -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-bg {
            background-image: url('https://www.hotellinksolutions.com/images/blog/cac-nguon-booking-khach-san.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }
        @media (max-width: 768px) {
            .hero-bg {
                background-attachment: scroll;
            }
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
        .search-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        header {
            transition: transform 0.3s ease-in-out;
        }
        .floating-search {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .search-input:focus {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
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

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="index.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium transition-all duration-300 group">
                        <i class="fas fa-home text-sm group-hover:scale-110 transition-transform"></i>
                        <span>Trang chủ</span>
                    </a>
                    <a href="search.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium shadow-lg hover:shadow-xl transition-all duration-300 group">
                        <i class="fas fa-search text-sm group-hover:scale-110 transition-transform"></i>
                        <span>Tìm phòng</span>
                    </a>
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin/index.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-gray-900 font-medium transition-all duration-300 group">
                        <i class="fas fa-cog text-sm group-hover:scale-110 transition-transform"></i>
                        <span>Admin</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- User Actions -->
                <div class="hidden md:flex items-center space-x-3">
                    <?php if ($is_logged_in): ?>
                        <button class="flex items-center space-x-2 px-4 py-2 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300 group">
                            <i class="fas fa-bell text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="hidden lg:inline">Thông báo</span>
                        </button>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 px-4 py-2 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300">
                                <i class="fas fa-user-circle text-sm"></i>
                                <span class="hidden lg:inline"><?php echo $_SESSION['user_name'] ?? 'User'; ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                                <div class="py-2">
                                    <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-user mr-2"></i>Thông tin cá nhân
                                    </a>
                                    <a href="booking-history.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-calendar mr-2"></i>Lịch sử đặt phòng
                                    </a>
                                    <hr class="my-1">
                                    <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="register.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300 group">
                            <i class="fas fa-user-plus text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="hidden lg:inline">Đăng ký</span>
                        </a>
                        <a href="login.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium shadow-lg hover:shadow-xl transition-all duration-300 group">
                            <i class="fas fa-sign-in-alt text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="hidden lg:inline">Đăng nhập</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-gray-100 transition-colors" onclick="toggleMobileMenu()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="lg:hidden mt-4 pb-4 border-t border-gray-200 hidden">
                <div class="flex flex-col space-y-2 pt-4">
                    <a href="index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-300">
                        <i class="fas fa-home w-5"></i>
                        <span>Trang chủ</span>
                    </a>
                    <a href="search.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                        <i class="fas fa-search w-5"></i>
                        <span>Tìm phòng</span>
                    </a>
                    <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin/index.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300">
                        <i class="fas fa-cog w-5"></i>
                        <span>Admin</span>
                    </a>
                    <?php endif; ?>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <?php if ($is_logged_in): ?>
                            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300">
                                <i class="fas fa-bell w-5"></i>
                                <span>Thông báo</span>
                            </a>
                            <a href="profile.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300">
                                <i class="fas fa-user w-5"></i>
                                <span><?php echo $_SESSION['user_name'] ?? 'User'; ?></span>
                            </a>
                            <a href="booking-history.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300">
                                <i class="fas fa-calendar w-5"></i>
                                <span>Lịch sử đặt phòng</span>
                            </a>
                            <a href="logout.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 transition-all duration-300">
                                <i class="fas fa-sign-out-alt w-5"></i>
                                <span>Đăng xuất</span>
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300">
                                <i class="fas fa-user-plus w-5"></i>
                                <span>Đăng ký</span>
                            </a>
                            <a href="login.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                                <i class="fas fa-sign-in-alt w-5"></i>
                                <span>Đăng nhập</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-bg min-h-[120vh] flex items-center relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10 z-0">
            <div class="absolute top-20 left-20 w-32 h-32 bg-white rounded-full animate-pulse"></div>
            <div class="absolute top-40 right-32 w-24 h-24 bg-white rounded-full animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-32 left-1/4 w-40 h-40 bg-white rounded-full animate-pulse" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="container mx-auto px-4 hero-content">
            <div class="max-w-4xl mx-auto text-center text-white" data-aos="fade-up">
                <h1 class="text-5xl md:text-7xl font-bold mb-6 leading-tight drop-shadow-2xl">
                    Tìm phòng khách sạn
                    <span class="block text-yellow-300 bg-gradient-to-r from-yellow-300 to-yellow-500 bg-clip-text text-transparent drop-shadow-lg">tuyệt vời nhất</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-95 drop-shadow-lg">
                    Khám phá hàng nghìn khách sạn và đặt phòng với giá tốt nhất
                </p>
                
                <!-- Simple Search Button -->
                <div class="relative max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                    <button onclick="checkLoginAndSearch()" class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 text-white px-12 py-6 rounded-2xl font-bold text-xl hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-2xl hover:shadow-3xl">
                        <i class="fas fa-search mr-3"></i>
                        Tìm phòng ngay
                            </button>
                </div>
                
                <!-- Quick Actions -->
                <div class="flex flex-wrap justify-center gap-4 mt-8" data-aos="fade-up" data-aos-delay="400">
                    <button onclick="checkLoginAndSearch()" class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-full hover:bg-white/30 transition-all duration-300 group">
                        <i class="fas fa-fire text-orange-400 group-hover:scale-110 transition-transform"></i>
                        <span>Khuyến mãi hot</span>
                    </button>
                    <button onclick="checkLoginAndSearch()" class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-full hover:bg-white/30 transition-all duration-300 group">
                        <i class="fas fa-star text-yellow-400 group-hover:scale-110 transition-transform"></i>
                        <span>Khách sạn 5 sao</span>
                    </button>
                    <button onclick="checkLoginAndSearch()" class="flex items-center space-x-2 bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-full hover:bg-white/30 transition-all duration-300 group">
                        <i class="fas fa-heart text-red-400 group-hover:scale-110 transition-transform"></i>
                        <span>Yêu thích</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 text-white animate-bounce">
            <i class="fas fa-chevron-down text-2xl"></i>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-100 via-indigo-100 to-purple-100">
            <div class="absolute top-0 left-0 w-full h-full">
                <div class="absolute top-20 left-20 w-64 h-64 bg-blue-200/30 rounded-full animate-pulse"></div>
                <div class="absolute top-40 right-32 w-48 h-48 bg-purple-200/30 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                <div class="absolute bottom-32 left-1/4 w-56 h-56 bg-pink-200/30 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute bottom-20 right-20 w-40 h-40 bg-green-200/30 rounded-full animate-pulse" style="animation-delay: 3s;"></div>
            </div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Tại sao chọn chúng tôi?</h2>
                <p class="text-xl text-gray-600">Dịch vụ đặt phòng khách sạn hàng đầu Việt Nam</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-8 rounded-2xl hover-lift bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200/50" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">An toàn & Bảo mật</h3>
                    <p class="text-gray-600">Thanh toán an toàn, thông tin được bảo mật tuyệt đối</p>
                </div>
                
                <div class="text-center p-8 rounded-2xl hover-lift bg-gradient-to-br from-green-50 to-emerald-100 border-2 border-green-200/50" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-dollar-sign text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Giá tốt nhất</h3>
                    <p class="text-gray-600">So sánh giá từ hàng nghìn khách sạn, đảm bảo giá tốt nhất</p>
                </div>
                
                <div class="text-center p-8 rounded-2xl hover-lift bg-gradient-to-br from-purple-50 to-pink-100 border-2 border-purple-200/50" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-headset text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Hỗ trợ 24/7</h3>
                    <p class="text-gray-600">Đội ngũ chăm sóc khách hàng luôn sẵn sàng hỗ trợ bạn</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-20 relative overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-fixed" 
             style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&h=1080&fit=crop');">
        </div>
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-900/80 via-teal-900/80 to-cyan-900/80"></div>
        
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="4"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-white mb-4">Con số ấn tượng</h2>
                <p class="text-xl text-white/90">Những thành tựu đáng tự hào của chúng tôi</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-8 hover:bg-white/30 transition-all duration-300">
                        <div class="text-4xl font-bold text-white mb-2">
                            <span class="counter" data-target="10000" data-suffix="+">0</span>
                        </div>
                        <div class="text-white/90">Khách sạn đối tác</div>
                    </div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-8 hover:bg-white/30 transition-all duration-300">
                        <div class="text-4xl font-bold text-white mb-2">
                            <span class="counter" data-target="500000" data-suffix="+">0</span>
                        </div>
                        <div class="text-white/90">Khách hàng hài lòng</div>
                    </div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-8 hover:bg-white/30 transition-all duration-300">
                        <div class="text-4xl font-bold text-white mb-2">
                            <span class="counter" data-target="63">0</span>
                        </div>
                        <div class="text-white/90">Tỉnh thành phủ sóng</div>
                    </div>
                </div>
                <div class="text-center" data-aos="fade-up" data-aos-delay="400">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-8 hover:bg-white/30 transition-all duration-300">
                        <div class="text-4xl font-bold text-white mb-2">
                            <span class="counter" data-target="99.9" data-suffix="%">0</span>
                        </div>
                        <div class="text-white/90">Tỷ lệ thành công</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-20 bg-gradient-to-br from-rose-100 via-pink-100 to-fuchsia-100 relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-10 w-32 h-32 bg-yellow-300/20 rounded-full animate-bounce"></div>
            <div class="absolute top-20 right-20 w-24 h-24 bg-orange-300/20 rounded-full animate-bounce" style="animation-delay: 0.5s;"></div>
            <div class="absolute bottom-20 left-1/3 w-28 h-28 bg-red-300/20 rounded-full animate-bounce" style="animation-delay: 1s;"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Dịch vụ của chúng tôi</h2>
                <p class="text-xl text-gray-600">Đa dạng dịch vụ, đáp ứng mọi nhu cầu du lịch</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-yellow-200/50" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-bed text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Đặt phòng khách sạn</h3>
                    <p class="text-gray-600 text-sm">Hơn 10,000 khách sạn từ bình dân đến 5 sao</p>
                </div>
                
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-blue-200/50" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-plane text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Vé máy bay</h3>
                    <p class="text-gray-600 text-sm">Giá vé tốt nhất, nhiều hãng hàng không</p>
                </div>
                
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-green-200/50" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-car text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Thuê xe du lịch</h3>
                    <p class="text-gray-600 text-sm">Xe đời mới, tài xế chuyên nghiệp</p>
                </div>
                
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-purple-200/50" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-map-marked-alt text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Tour du lịch</h3>
                    <p class="text-gray-600 text-sm">Tour trong nước và quốc tế chất lượng cao</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Destinations Slider -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Điểm đến phổ biến</h2>
                <p class="text-xl text-gray-600">Khám phá những thành phố tuyệt vời nhất Việt Nam</p>
            </div>
            
            <!-- Slider Container -->
            <div class="relative max-w-6xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                <div class="destinations-slider overflow-hidden rounded-3xl shadow-2xl">
                    <div class="slider-wrapper flex transition-transform duration-500 ease-in-out">
                <?php
                $destinations = [
                            ['name' => 'TP. Hồ Chí Minh', 'image' => 'https://encrypted-tbn0.gstatic.com/licensed-image?q=tbn:ANd9GcQ2NsQDfgJef6_92Scmli6TKT19SwlTM1sUj3NL_BoToFYunOxZkrS-Sjf6ut2Cg57uH15grdohrsaMkaTWaXQsRTAAcuSgVjwMWrmwQw', 'hotels' => 150, 'description' => 'Thành phố năng động nhất Việt Nam'],
                            ['name' => 'Hà Nội', 'image' => 'https://cdn-media.sforum.vn/storage/app/media/wp-content/uploads/2024/01/dia-diem-du-lich-o-ha-noi-thumb.jpg', 'hotels' => 120, 'description' => 'Thủ đô ngàn năm văn hiến'],
                            ['name' => 'Đà Nẵng', 'image' => 'https://cdn-media.sforum.vn/storage/app/media/ctvseo_MH/%E1%BA%A3nh%20%C4%91%E1%BA%B9p%20%C4%91%C3%A0%20n%E1%BA%B5ng/anh-dep-da-nang-thumb.jpg', 'hotels' => 80, 'description' => 'Thành phố đáng sống nhất Việt Nam'],
                            ['name' => 'Nha Trang', 'image' => 'https://media.vneconomy.vn/images/upload/2024/05/15/nha-trang-2.jpg', 'hotels' => 60, 'description' => 'Thiên đường biển đảo']
                ];
                
                foreach ($destinations as $index => $dest): ?>
                        <div class="slider-slide w-full flex-shrink-0 relative">
                            <div class="relative h-96 md:h-[500px] overflow-hidden">
                    <img src="<?php echo $dest['image']; ?>" alt="<?php echo $dest['name']; ?>" 
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                
                                <!-- Content Overlay -->
                                <div class="absolute bottom-0 left-0 right-0 p-8 md:p-12 text-white">
                                    <div class="max-w-2xl">
                                        <h3 class="text-3xl md:text-5xl font-bold mb-4 leading-tight">
                                            <?php echo $dest['name']; ?>
                                        </h3>
                                        <p class="text-lg md:text-xl mb-4 opacity-90">
                                            <?php echo $dest['description']; ?>
                                        </p>
                                        <div class="flex items-center space-x-6">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-hotel text-2xl text-blue-400"></i>
                                                <span class="text-lg font-semibold"><?php echo $dest['hotels']; ?> khách sạn</span>
                                            </div>
                                            <button onclick="selectDestinationAndSearch('<?php echo $dest['name']; ?>')" 
                                                    class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl font-bold hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg">
                                                <i class="fas fa-search mr-2"></i>Tìm phòng
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Navigation Arrows -->
                <button class="slider-prev absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110 z-10">
                    <i class="fas fa-chevron-left text-xl"></i>
                </button>
                <button class="slider-next absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110 z-10">
                    <i class="fas fa-chevron-right text-xl"></i>
                </button>
                
                <!-- Dots Indicator -->
                <div class="flex justify-center space-x-3 mt-6">
                    <?php foreach ($destinations as $index => $dest): ?>
                    <button class="slider-dot w-3 h-3 rounded-full bg-gray-300 hover:bg-blue-600 transition-all duration-300 <?php echo $index === 0 ? 'bg-blue-600' : ''; ?>" 
                            data-slide="<?php echo $index; ?>"></button>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-gradient-to-br from-green-50 via-teal-50 to-blue-50 relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-20 w-40 h-40 bg-green-300/20 rounded-full animate-pulse"></div>
            <div class="absolute top-40 right-40 w-32 h-32 bg-teal-300/20 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-40 left-1/3 w-36 h-36 bg-blue-300/20 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Khách hàng nói gì về chúng tôi</h2>
                <p class="text-xl text-gray-600">Những đánh giá chân thực từ khách hàng</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-green-200/50" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"Dịch vụ tuyệt vời! Đặt phòng nhanh chóng, giá cả hợp lý. Sẽ quay lại sử dụng dịch vụ."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-4">
                            <span class="text-white font-bold">A</span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">Anh Minh</div>
                            <div class="text-sm text-gray-500">TP. Hồ Chí Minh</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-blue-200/50" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"Giao diện đẹp, dễ sử dụng. Hỗ trợ khách hàng nhiệt tình, giải đáp mọi thắc mắc."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-4">
                            <span class="text-white font-bold">L</span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">Chị Lan</div>
                            <div class="text-sm text-gray-500">Hà Nội</div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-purple-200/50" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6">"Khách sạn chất lượng tốt, đúng như mô tả. Cảm ơn team đã hỗ trợ tận tình!"</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-full flex items-center justify-center mr-4">
                            <span class="text-white font-bold">T</span>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">Anh Tuấn</div>
                            <div class="text-sm text-gray-500">Đà Nẵng</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 relative overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-fixed" 
             style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1920&h=1080&fit=crop');">
        </div>
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-pink-900/80 via-purple-900/80 to-indigo-900/80"></div>
        
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="50" cy="50" r="2"/%3E%3C/g%3E%3C/svg%3E')]"></div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-20 w-32 h-32 bg-white/10 rounded-full animate-bounce"></div>
            <div class="absolute top-40 right-32 w-24 h-24 bg-white/10 rounded-full animate-bounce" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-32 left-1/4 w-40 h-40 bg-white/10 rounded-full animate-bounce" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-20 right-20 w-20 h-20 bg-white/10 rounded-full animate-bounce" style="animation-delay: 3s;"></div>
        </div>
        
        <!-- Content -->
        <div class="container mx-auto px-4 text-center text-white relative z-10" data-aos="fade-up">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight drop-shadow-2xl">
                    Sẵn sàng cho chuyến đi của bạn?
                </h2>
                <p class="text-xl md:text-2xl mb-8 opacity-95 drop-shadow-lg">
                    Đặt phòng ngay hôm nay và tận hưởng những trải nghiệm tuyệt vời
                </p>
                
                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <button onclick="checkLoginAndSearch()" class="bg-white text-blue-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-100 transform hover:scale-105 transition-all duration-300 shadow-lg inline-flex items-center">
                <i class="fas fa-rocket mr-2"></i>Bắt đầu tìm kiếm
            </button>
                    <button onclick="checkLoginAndSearch()" class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-white hover:text-blue-600 transform hover:scale-105 transition-all duration-300 shadow-lg inline-flex items-center">
                        <i class="fas fa-star mr-2"></i>Khách sạn 5 sao
                    </button>
                </div>
                
                <!-- Features -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 hover:bg-white/20 transition-all duration-300">
                        <i class="fas fa-shield-alt text-3xl text-yellow-400 mb-3"></i>
                        <h3 class="text-lg font-semibold mb-2">An toàn tuyệt đối</h3>
                        <p class="text-sm opacity-90">Thanh toán bảo mật, thông tin được mã hóa</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 hover:bg-white/20 transition-all duration-300">
                        <i class="fas fa-clock text-3xl text-green-400 mb-3"></i>
                        <h3 class="text-lg font-semibold mb-2">Hỗ trợ 24/7</h3>
                        <p class="text-sm opacity-90">Đội ngũ chăm sóc khách hàng luôn sẵn sàng</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 hover:bg-white/20 transition-all duration-300">
                        <i class="fas fa-gift text-3xl text-pink-400 mb-3"></i>
                        <h3 class="text-lg font-semibold mb-2">Ưu đãi độc quyền</h3>
                        <p class="text-sm opacity-90">Giá tốt nhất, khuyến mãi hấp dẫn</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-20 w-32 h-32 bg-white/10 rounded-full animate-pulse"></div>
            <div class="absolute top-40 right-32 w-24 h-24 bg-white/10 rounded-full animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-32 left-1/4 w-40 h-40 bg-white/10 rounded-full animate-pulse" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-20 right-20 w-20 h-20 bg-white/10 rounded-full animate-pulse" style="animation-delay: 3s;"></div>
        </div>
    </section>

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
                        <li><a href="#" class="hover:text-white transition-colors">Thanh toán</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Hủy đặt phòng</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-bold mb-4">Theo dõi chúng tôi</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-pink-600 rounded-full flex items-center justify-center hover:bg-pink-700 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center hover:bg-blue-500 transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
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
        
        // Check login before searching
        function checkLoginAndSearch() {
            <?php if ($is_logged_in): ?>
                window.location.href = 'search.php';
            <?php else: ?>
                window.location.href = 'login.php?message=login_required';
            <?php endif; ?>
        }
        
        // Select destination and search (with login check)
        function selectDestinationAndSearch(destination) {
            <?php if ($is_logged_in): ?>
                window.location.href = 'search.php?location=' + encodeURIComponent(destination);
            <?php else: ?>
                window.location.href = 'login.php?message=login_required';
            <?php endif; ?>
        }

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
            showToast('📞 Đang kết nối với hotline: 1900-xxxx', 'info');
            
            // Simulate call animation
            setTimeout(() => {
                showToast('📞 Cuộc gọi đã được kết nối!', 'success');
            }, 2000);
        }

        // Simulate messenger function
        function simulateMessenger() {
            showToast('Đang mở Facebook Messenger...', 'info');
            
            setTimeout(() => {
                showToast('Chat với chúng tôi qua Messenger!', 'success');
            }, 1500);
        }


        // Form validation (if form exists)
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const checkinInput = document.querySelector('input[name="checkin"]');
                const checkoutInput = document.querySelector('input[name="checkout"]');
                
                if (checkinInput && checkoutInput) {
                    const checkin = checkinInput.value;
                    const checkout = checkoutInput.value;
            
            if (checkin && checkout) {
                if (new Date(checkin) >= new Date(checkout)) {
                    e.preventDefault();
                    showToast('Ngày trả phòng phải sau ngày nhận phòng', 'error');
                    return;
                }
                
                if (new Date(checkin) < new Date()) {
                    e.preventDefault();
                    showToast('Ngày nhận phòng không được là ngày trong quá khứ', 'error');
                    return;
                        }
                }
            }
        });
        }

        // Set minimum date to today (if inputs exist)
        const today = new Date().toISOString().split('T')[0];
        const checkinInput = document.querySelector('input[name="checkin"]');
        const checkoutInput = document.querySelector('input[name="checkout"]');
        
        if (checkinInput) {
            checkinInput.setAttribute('min', today);
        }
        if (checkoutInput) {
            checkoutInput.setAttribute('min', today);
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        }

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobile-menu');
            const menuButton = event.target.closest('button[onclick="toggleMobileMenu()"]');
            
            if (!mobileMenu.contains(event.target) && !menuButton) {
                mobileMenu.classList.add('hidden');
            }
        });

        // Header scroll effect
        let lastScrollTop = 0;
        const header = document.querySelector('header');
        
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                header.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                header.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop;
        });

        // Destinations Slider
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slider-slide');
        const totalSlides = slides.length;
        const sliderWrapper = document.querySelector('.slider-wrapper');
        const dots = document.querySelectorAll('.slider-dot');
        const prevBtn = document.querySelector('.slider-prev');
        const nextBtn = document.querySelector('.slider-next');

        function updateSlider() {
            const translateX = -currentSlide * 100;
            sliderWrapper.style.transform = `translateX(${translateX}%)`;
            
            // Update dots
            dots.forEach((dot, index) => {
                dot.classList.toggle('bg-blue-600', index === currentSlide);
                dot.classList.toggle('bg-gray-300', index !== currentSlide);
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateSlider();
        }

        function goToSlide(slideIndex) {
            currentSlide = slideIndex;
            updateSlider();
        }

        // Event listeners
        nextBtn.addEventListener('click', nextSlide);
        prevBtn.addEventListener('click', prevSlide);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });

        // Auto slide
        setInterval(nextSlide, 5000);

        // Select destination function
        function selectDestination(destinationName) {
            showToast(`📍 Đã chọn địa điểm: ${destinationName}`, 'success');
            
            // Scroll to search form
            document.querySelector('form').scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
        }

        // Counter Animation
        function animateCounter(element, target, duration = 2000) {
            const start = 0;
            const increment = target / (duration / 16); // 60fps
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                // Format number with commas
                const formatted = Math.floor(current).toLocaleString();
                element.textContent = formatted;
            }, 16);
        }

        // Intersection Observer for counter animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -10px 0px'
        };

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseFloat(counter.getAttribute('data-target'));
                    const suffix = counter.getAttribute('data-suffix') || '';
                    
                    // Animate the counter
                    animateCounter(counter, target, 2500);
                    
                    // Add suffix after animation
                    setTimeout(() => {
                        if (target % 1 === 0) {
                            counter.textContent = Math.floor(target).toLocaleString() + suffix;
                        } else {
                            counter.textContent = target.toFixed(1) + suffix;
                        }
                    }, 2500);
                    
                    // Stop observing this element
                    counterObserver.unobserve(counter);
                }
            });
        }, observerOptions);

        // Start observing all counters when page loads
        document.addEventListener('DOMContentLoaded', () => {
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                counterObserver.observe(counter);
            });
        });
    </script>
</body>
</html>

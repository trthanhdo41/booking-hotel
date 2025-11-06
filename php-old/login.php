<?php
session_start();
include 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle logout message
if (isset($_GET['message']) && $_GET['message'] === 'logout_success') {
    $success = 'Đăng xuất thành công!';
}

// Handle login required message
if (isset($_GET['message']) && $_GET['message'] === 'login_required') {
    $error = 'Vui lòng đăng nhập để tìm phòng!';
}

// Handle login form submission
if ($_POST) {
    $username_or_email = $_POST['username_or_email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username_or_email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        // Tìm user trong Google Sheets
        $user = findUser($username_or_email);
        
        if ($user && password_verify($password, $user['password']) && $user['status'] === 'active') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_username'] = $user['username'];
            
            header('Location: index.php');
            exit();
        } else {
            $error = 'Tên đăng nhập/email hoặc mật khẩu không đúng';
        }
    }
}

$page_title = "Đăng nhập - Booking Hotel";
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
        .floating-form {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        header {
            transition: transform 0.3s ease-in-out;
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

                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="index.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium transition-all duration-300 group">
                        <i class="fas fa-home text-sm group-hover:scale-110 transition-transform"></i>
                        <span>Trang chủ</span>
                    </a>
                    <a href="search.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl text-gray-700 hover:bg-gray-50 hover:text-gray-900 font-medium transition-all duration-300 group">
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
                    <a href="register.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300 group">
                        <i class="fas fa-user-plus text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="hidden lg:inline">Đăng ký</span>
                    </a>
                    <a href="login.php" class="flex items-center space-x-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium shadow-lg hover:shadow-xl transition-all duration-300 group">
                        <i class="fas fa-sign-in-alt text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="hidden lg:inline">Đăng nhập</span>
                    </a>
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
                    <a href="search.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300">
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
                        <a href="register.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300">
                            <i class="fas fa-user-plus w-5"></i>
                            <span>Đăng ký</span>
                        </a>
                        <a href="login.php" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                            <i class="fas fa-sign-in-alt w-5"></i>
                            <span>Đăng nhập</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-bg min-h-screen flex items-center relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10 z-0">
            <div class="absolute top-20 left-20 w-32 h-32 bg-white rounded-full animate-pulse"></div>
            <div class="absolute top-40 right-32 w-24 h-24 bg-white rounded-full animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-32 left-1/4 w-40 h-40 bg-white rounded-full animate-pulse" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="container mx-auto px-4 hero-content">
            <div class="max-w-md mx-auto" data-aos="fade-up">
                <!-- Login Form -->
                <div class="bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-white/20 p-8 floating-form">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-sign-in-alt text-white text-2xl"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Đăng nhập</h2>
                        <p class="text-gray-600">Chào mừng bạn quay trở lại!</p>
                    </div>

                    <!-- Error Message -->
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

                    <!-- Success Message -->
                    <?php if ($success): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700"><?php echo $success; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-user mr-2 text-blue-600"></i>Tên đăng nhập hoặc Email
                            </label>
                            <input type="text" name="username_or_email" required
                                   class="w-full p-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                   placeholder="Nhập tên đăng nhập hoặc email">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-lock mr-2 text-blue-600"></i>Mật khẩu
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required
                                       class="w-full p-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all pr-12"
                                       placeholder="Nhập mật khẩu">
                                <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" id="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="remember" class="ml-2 text-sm text-gray-600">Ghi nhớ đăng nhập</label>
                            </div>
                            <button type="button" onclick="showForgotPassword()" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                                Quên mật khẩu?
                            </button>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-4 rounded-xl font-bold hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Đăng nhập
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-gray-600">Chưa có tài khoản? 
                            <a href="register.php" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                Đăng ký ngay
                            </a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </section>

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

        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Show forgot password (non-functional)
        function showForgotPassword() {
            showToast('Tính năng quên mật khẩu đang được phát triển', 'info');
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
    </script>
</body>
</html>

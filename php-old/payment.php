<?php
session_start();
include 'config/database.php';

$booking_id = $_GET['booking_id'] ?? '';
$amount = $_GET['amount'] ?? 0;

if (!$booking_id || !$amount) {
    header('Location: search.php');
    exit;
}

$page_title = "Thanh toán - Booking Hotel";
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
        .payment-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .payment-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(30deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(30deg); }
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
                    Thanh toán an toàn
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-95 drop-shadow-lg">
                    Hoàn tất thanh toán để xác nhận đặt phòng
                </p>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 py-12">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Payment Methods -->
                <div class="space-y-6" data-aos="fade-up">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-credit-card mr-3 text-blue-600"></i>Phương thức thanh toán
                    </h2>
                    
                    <!-- Credit Card -->
                    <div class="bg-white rounded-2xl shadow-xl p-6 hover-lift cursor-pointer payment-method ring-2 ring-blue-500" data-method="creditCard">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-credit-card text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">Thẻ tín dụng/ghi nợ</h3>
                                    <p class="text-gray-600">Visa, Mastercard, JCB</p>
                                </div>
                            </div>
                            <div class="w-6 h-6 border-2 border-blue-500 bg-blue-500 rounded-full payment-radio"></div>
                        </div>
                    </div>
                    
                    <!-- Banking -->
                    <div class="bg-white rounded-2xl shadow-xl p-6 hover-lift cursor-pointer payment-method" data-method="banking">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-green-600 to-teal-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-university text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">Chuyển khoản ngân hàng</h3>
                                    <p class="text-gray-600">Internet Banking, Mobile Banking</p>
                                </div>
                            </div>
                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full payment-radio"></div>
                        </div>
                    </div>
                    
                    <!-- E-Wallet -->
                    <div class="bg-white rounded-2xl shadow-xl p-6 hover-lift cursor-pointer payment-method" data-method="ewallet">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-orange-600 to-red-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-mobile-alt text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">Ví điện tử</h3>
                                    <p class="text-gray-600">MoMo, ZaloPay, VNPay</p>
                                </div>
                            </div>
                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full payment-radio"></div>
                        </div>
                    </div>
                    
                    <!-- QR Code -->
                    <div class="bg-white rounded-2xl shadow-xl p-6 hover-lift cursor-pointer payment-method" data-method="qr">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-pink-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-qrcode text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800">QR Code</h3>
                                    <p class="text-gray-600">Quét mã QR để thanh toán</p>
                                </div>
                            </div>
                            <div class="w-6 h-6 border-2 border-gray-300 rounded-full payment-radio"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Summary -->
                <div class="space-y-6" data-aos="fade-up" data-aos-delay="200">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6">
                        <i class="fas fa-receipt mr-3 text-green-600"></i>Tóm tắt thanh toán
                    </h2>
                    
                    <!-- Payment Card -->
                    <div class="payment-card hover-lift">
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-xl font-bold">Booking Hotel</h3>
                                    <p class="opacity-80">Premium Experience</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm opacity-80">Mã đặt phòng</p>
                                    <p class="font-bold"><?php echo $booking_id; ?></p>
                                </div>
                            </div>
                            
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="opacity-80">Số tiền:</span>
                                    <span class="text-2xl font-bold"><?php echo formatPrice($amount); ?></span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="opacity-80">Phí giao dịch:</span>
                                    <span class="font-semibold">Miễn phí</span>
                                </div>
                                <div class="border-t border-white/20 pt-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-lg font-bold">Tổng cộng:</span>
                                        <span class="text-2xl font-bold"><?php echo formatPrice($amount); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Countdown Timer -->
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <i class="fas fa-clock text-red-600"></i>
                                    <span class="text-red-800 font-semibold">Thời gian thanh toán còn lại:</span>
                                    <span id="countdown" class="text-red-600 font-bold text-lg">15:00</span>
                                </div>
                                <p class="text-red-600 text-sm text-center mt-2">
                                    Vui lòng hoàn tất thanh toán trong thời gian trên
                                </p>
                            </div>
                            
                            <div class="flex items-center space-x-2 text-sm opacity-80">
                                <i class="fas fa-shield-alt"></i>
                                <span>Giao dịch được bảo mật SSL 256-bit</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Form -->
                    <div class="bg-white rounded-2xl shadow-xl p-6 hover-lift">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Thông tin thanh toán</h3>
                        
                        <form id="paymentForm" class="space-y-4">
                            <div id="creditCardForm" class="payment-form">
                                <div class="space-y-4">
                                    <!-- Card Type Icons -->
                                    <div class="flex justify-center space-x-4 mb-4">
                                        <div class="flex items-center space-x-2">
                                            <i class="fab fa-cc-visa text-2xl text-blue-600"></i>
                                            <span class="text-sm text-gray-600">Visa</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <i class="fab fa-cc-mastercard text-2xl text-red-600"></i>
                                            <span class="text-sm text-gray-600">Mastercard</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <i class="fab fa-cc-jcb text-2xl text-orange-600"></i>
                                            <span class="text-sm text-gray-600">JCB</span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-credit-card mr-2 text-blue-600"></i>Số thẻ
                                        </label>
                                        <input type="text" placeholder="1234 5678 9012 3456" 
                                               class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               maxlength="19">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                <i class="fas fa-calendar-alt mr-2 text-green-600"></i>Ngày hết hạn
                                            </label>
                                            <input type="text" placeholder="MM/YY" 
                                                   class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                   maxlength="5">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                                <i class="fas fa-lock mr-2 text-red-600"></i>CVV
                                            </label>
                                            <input type="text" placeholder="123" 
                                                   class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                   maxlength="4">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                                            <i class="fas fa-user mr-2 text-purple-600"></i>Tên chủ thẻ
                                        </label>
                                        <input type="text" placeholder="NGUYEN VAN A" 
                                               class="w-full p-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                               style="text-transform: uppercase;">
                                    </div>
                                    
                                    <!-- Security Notice -->
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <div class="flex items-center">
                                            <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
                                            <span class="text-sm text-blue-800">Thông tin thẻ được mã hóa và bảo mật SSL 256-bit</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="bankingForm" class="payment-form hidden">
                                <div class="text-center space-y-4">
                                    <div class="w-48 h-48 bg-white rounded-xl mx-auto flex items-center justify-center border-2 border-gray-200 shadow-lg">
                                        <img src="https://qrcode-gen.com/images/qrcode-default.png" 
                                             alt="QR Code chuyển khoản ngân hàng" 
                                             class="w-44 h-44 object-contain rounded-lg">
                                    </div>
                                    <p class="text-gray-600">Quét mã QR bằng ứng dụng Internet Banking hoặc Mobile Banking</p>
                                </div>
                            </div>
                            
                            <div id="ewalletForm" class="payment-form hidden">
                                <div class="text-center space-y-4">
                                    <div class="w-48 h-48 bg-white rounded-xl mx-auto flex items-center justify-center border-2 border-gray-200 shadow-lg">
                                        <img src="https://qrcode-gen.com/images/qrcode-default.png" 
                                             alt="QR Code ví điện tử" 
                                             class="w-44 h-44 object-contain rounded-lg">
                                    </div>
                                    <p class="text-gray-600">Quét mã QR bằng ứng dụng MoMo, ZaloPay hoặc VNPay</p>
                                </div>
                            </div>
                            
                            <div id="qrForm" class="payment-form hidden">
                                <div class="text-center space-y-4">
                                    <div class="w-48 h-48 bg-white rounded-xl mx-auto flex items-center justify-center border-2 border-gray-200 shadow-lg">
                                        <img src="https://qrcode-gen.com/images/qrcode-default.png" 
                                             alt="QR Code thanh toán" 
                                             class="w-44 h-44 object-contain rounded-lg">
                                    </div>
                                    <p class="text-gray-600">Quét mã QR bằng ứng dụng ngân hàng hoặc ví điện tử</p>
                                </div>
                            </div>
                            
                            <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-teal-600 text-white px-6 py-4 rounded-xl font-bold text-lg hover:from-green-700 hover:to-teal-700 transform hover:scale-105 transition-all duration-300 shadow-lg">
                                <i class="fas fa-lock mr-2"></i>Thanh toán <?php echo formatPrice($amount); ?>
                            </button>
                        </form>
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

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Payment method selection
        let selectedMethod = null;
        const paymentMethods = document.querySelectorAll('.payment-method');
        const paymentForms = document.querySelectorAll('.payment-form');

        paymentMethods.forEach(method => {
            method.addEventListener('click', () => {
                // Remove active state from all methods
                paymentMethods.forEach(m => {
                    m.classList.remove('ring-2', 'ring-blue-500');
                    m.querySelector('.payment-radio').classList.remove('bg-blue-500', 'border-blue-500');
                    m.querySelector('.payment-radio').classList.add('border-gray-300');
                });

                // Hide all forms
                paymentForms.forEach(form => {
                    form.classList.add('hidden');
                });

                // Add active state to selected method
                method.classList.add('ring-2', 'ring-blue-500');
                const radio = method.querySelector('.payment-radio');
                radio.classList.add('bg-blue-500', 'border-blue-500');
                radio.classList.remove('border-gray-300');

                // Show corresponding form
                selectedMethod = method.dataset.method;
                let formId;
                if (selectedMethod === 'creditCard') {
                    formId = 'creditCardForm';
                } else {
                    formId = selectedMethod + 'Form';
                }
                const form = document.getElementById(formId);
                if (form) {
                    form.classList.remove('hidden');
                }
            });
        });

        // Payment form submission
        document.getElementById('paymentForm').addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (!selectedMethod) {
                alert('Vui lòng chọn phương thức thanh toán');
                return;
            }

            // Simulate payment processing
            const button = e.target.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';
            button.disabled = true;

            setTimeout(() => {
                // Update payment status in Google Sheets
                fetch('update-payment-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: '<?php echo $booking_id; ?>',
                        payment_method: selectedMethod,
                        amount: <?php echo $amount; ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Send payment confirmation email
                        fetch('send-payment-email.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                booking_id: '<?php echo $booking_id; ?>',
                                amount: <?php echo $amount; ?>,
                                payment_method: selectedMethod,
                                transaction_id: 'TXN' + Date.now()
                            })
                        });
                        
                        // Redirect to success page
                        window.location.href = 'payment-success.php?booking_id=<?php echo $booking_id; ?>&amount=<?php echo $amount; ?>&payment_method=' + selectedMethod;
                    } else {
                        alert('Lỗi cập nhật thanh toán: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật thanh toán');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }, 3000);
        });

        // Auto-select first payment method
        if (paymentMethods.length > 0) {
            paymentMethods[0].click();
        }

        // Countdown Timer (15 minutes)
        let timeLeft = 15 * 60; // 15 minutes in seconds
        const countdownElement = document.getElementById('countdown');
        
        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            countdownElement.textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                // Time's up - redirect or show message
                countdownElement.textContent = '00:00';
                countdownElement.parentElement.innerHTML = 
                    '<div class="text-center"><i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>' +
                    '<span class="text-red-800 font-bold">Hết thời gian thanh toán!</span></div>';
                
                // Disable payment form
                const paymentForm = document.getElementById('paymentForm');
                if (paymentForm) {
                    paymentForm.style.opacity = '0.5';
                    paymentForm.style.pointerEvents = 'none';
                }
                
                // Show alert
                setTimeout(() => {
                    alert('Hết thời gian thanh toán! Vui lòng tạo đặt phòng mới.');
                    window.location.href = 'search.php';
                }, 1000);
                
                return;
            }
            
            timeLeft--;
        }
        
        // Update countdown every second
        const countdownInterval = setInterval(updateCountdown, 1000);
        
        // Clear interval when page is unloaded
        window.addEventListener('beforeunload', () => {
            clearInterval(countdownInterval);
        });

        // Credit card formatting
        const cardNumberInput = document.querySelector('input[placeholder="1234 5678 9012 3456"]');
        const expiryInput = document.querySelector('input[placeholder="MM/YY"]');
        const cvvInput = document.querySelector('input[placeholder="123"]');
        const cardNameInput = document.querySelector('input[placeholder="NGUYEN VAN A"]');

        // Format card number with spaces
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
            });
        }

        // Format expiry date
        if (expiryInput) {
            expiryInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });
        }

        // CVV only numbers
        if (cvvInput) {
            cvvInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
            });
        }

        // Card name uppercase
        if (cardNameInput) {
            cardNameInput.addEventListener('input', function(e) {
                e.target.value = e.target.value.toUpperCase();
            });
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
</body>
</html>

<?php
// Include database functions để sử dụng helper functions
include_once 'database.php';

// Cấu hình n8n webhook
$n8n_config = [
    'webhook_url' => 'https://2ada7f.n8nvps.site/webhook/booking_email'
];

// Hàm gửi email qua n8n webhook
function sendEmail($to, $subject, $message, $isHTML = true) {
    global $n8n_config;
    
    $data = [
        'to' => $to,
        'subject' => $subject,
        'message' => $message,
        'isHTML' => $isHTML ? '1' : '0',
        'from_name' => 'Booking Hotel',
        'from_email' => $to, // Gửi từ email của user đang đăng nhập
        'reply_to' => $to,   // Reply về email của user đang đăng nhập
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Encode data as URL parameters for GET request
    // Truncate message if too long for URL
    if (strlen($message) > 1000) {
        $data['message'] = substr($message, 0, 1000) . '... [truncated]';
    }
    
    $query_string = http_build_query($data);
    $webhook_url = $n8n_config['webhook_url'] . '?' . $query_string;
    
    $options = [
        'http' => [
            'header' => "User-Agent: BookingHotel/1.0\r\n",
            'method' => 'GET',
            'timeout' => 30
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($webhook_url, false, $context);
    
    if ($result === FALSE) {
        $error = error_get_last();
        error_log("Failed to send email via n8n webhook: " . ($error['message'] ?? 'Unknown error'));
        return false;
    }
    
    error_log("N8N Webhook response: " . $result);
    return true;
}


// Hàm gửi email xác nhận đặt phòng - KHÔNG GỬI EMAIL
function sendBookingConfirmation($booking) {
    // Không gửi email khi đặt phòng, chỉ gửi khi thanh toán thành công
    return true;
    
}

// Hàm gửi email thanh toán thành công
function sendPaymentConfirmation($booking) {
    $subject = "🎉 Thanh toán thành công - Đặt phòng #{$booking['id']}";
    
    $message = "🎉 THANH TOÁN THÀNH CÔNG!

Xin chào {$booking['customer_name']},

Thanh toán cho đặt phòng #{$booking['id']} đã được xử lý thành công.

═══════════════════════════════════════════════════════════════
📋 HÓA ĐƠN THANH TOÁN
═══════════════════════════════════════════════════════════════

• Mã đặt phòng: #{$booking['id']}
• Khách sạn: {$booking['hotel_name']}
• Loại phòng: {$booking['room_type_name']}
• Ngày nhận phòng: " . formatDate($booking['checkin_date']) . "
• Ngày trả phòng: " . formatDate($booking['checkout_date']) . "
• Số đêm: " . calculateDays($booking['checkin_date'], $booking['checkout_date']) . " đêm
• Phương thức thanh toán: {$booking['payment_method']}
• Tổng thanh toán: " . formatPrice($booking['total_price']) . "

Booking Hotel - Hệ thống đặt phòng trực tuyến";
    
    return sendEmail($booking['customer_email'], $subject, $message, false);
}

?>

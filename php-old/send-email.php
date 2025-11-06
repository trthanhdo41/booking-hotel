<?php
// Email confirmation functionality
function sendBookingConfirmation($booking_data) {
    $to = $booking_data['customer_email'];
    $subject = "Xác nhận đặt phòng - Booking Hotel";
    
    $message = generateBookingEmailTemplate($booking_data);
    $headers = [
        'From: noreply@bookinghotel.com',
        'Reply-To: support@bookinghotel.com',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

function sendPaymentConfirmation($payment_data) {
    $to = $payment_data['customer_email'];
    $subject = "Xác nhận thanh toán - Booking Hotel";
    
    $message = generatePaymentEmailTemplate($payment_data);
    $headers = [
        'From: noreply@bookinghotel.com',
        'Reply-To: support@bookinghotel.com',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

function generateBookingEmailTemplate($booking_data) {
    $nights = calculateDays($booking_data['checkin_date'], $booking_data['checkout_date']);
    
    return "
    <!DOCTYPE html>
    <html lang='vi'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Xác nhận đặt phòng</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #eee; }
            .detail-row:last-child { border-bottom: none; }
            .label { font-weight: bold; color: #666; }
            .value { color: #333; }
            .total { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Đặt phòng thành công!</h1>
                <p>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ của chúng tôi</p>
            </div>
            
            <div class='content'>
                <h2>Thông tin đặt phòng</h2>
                
                <div class='booking-details'>
                    <div class='detail-row'>
                        <span class='label'>Mã đặt phòng:</span>
                        <span class='value'><strong>{$booking_data['booking_id']}</strong></span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Khách sạn:</span>
                        <span class='value'>{$booking_data['hotel_name']}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Loại phòng:</span>
                        <span class='value'>{$booking_data['room_type_name']}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Nhận phòng:</span>
                        <span class='value'>" . formatDate($booking_data['checkin_date']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Trả phòng:</span>
                        <span class='value'>" . formatDate($booking_data['checkout_date']) . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Số đêm:</span>
                        <span class='value'>{$nights} đêm</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Số khách:</span>
                        <span class='value'>{$booking_data['max_guests']} khách</span>
                    </div>
                </div>
                
                <div class='total'>
                    <div class='detail-row'>
                        <span class='label'>Tổng cộng:</span>
                        <span class='value'><strong>" . formatPrice($booking_data['total_price']) . "</strong></span>
                    </div>
                </div>
                
                <h3>Bước tiếp theo:</h3>
                <ul>
                    <li>✅ Kiểm tra email xác nhận (email này)</li>
                    <li>📞 Gọi điện xác nhận với khách sạn trước ngày nhận phòng</li>
                    <li>🆔 Mang theo CMND/CCCD để check-in</li>
                    <li>📱 Giữ mã đặt phòng để tra cứu khi cần</li>
                </ul>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='https://bookinghotel.com' class='button'>Truy cập website</a>
                </div>
                
                <div class='footer'>
                    <p><strong>Hỗ trợ khách hàng 24/7:</strong></p>
                    <p>📞 Hotline: 1900-xxxx | 📧 Email: support@bookinghotel.com</p>
                    <p>© 2024 Booking Hotel. Tất cả quyền được bảo lưu.</p>
                </div>
            </div>
        </div>
    </body>
    </html>";
}

function generatePaymentEmailTemplate($payment_data) {
    return "
    <!DOCTYPE html>
    <html lang='vi'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Xác nhận thanh toán</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .payment-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
            .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #eee; }
            .detail-row:last-child { border-bottom: none; }
            .label { font-weight: bold; color: #666; }
            .value { color: #333; }
            .total { background: #d1fae5; padding: 15px; border-radius: 8px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            .success-icon { font-size: 48px; color: #10b981; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='success-icon'>✅</div>
                <h1>Thanh toán thành công!</h1>
                <p>Giao dịch đã được xử lý thành công</p>
            </div>
            
            <div class='content'>
                <h2>Thông tin giao dịch</h2>
                
                <div class='payment-details'>
                    <div class='detail-row'>
                        <span class='label'>Mã giao dịch:</span>
                        <span class='value'><strong>{$payment_data['transaction_id']}</strong></span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Mã đặt phòng:</span>
                        <span class='value'>{$payment_data['booking_id']}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Thời gian:</span>
                        <span class='value'>" . date('d/m/Y H:i:s') . "</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Phương thức:</span>
                        <span class='value'>{$payment_data['payment_method']}</span>
                    </div>
                    <div class='detail-row'>
                        <span class='label'>Trạng thái:</span>
                        <span class='value' style='color: #10b981; font-weight: bold;'>✅ Thành công</span>
                    </div>
                </div>
                
                <div class='total'>
                    <div class='detail-row'>
                        <span class='label'>Số tiền thanh toán:</span>
                        <span class='value'><strong>" . formatPrice($payment_data['amount']) . "</strong></span>
                    </div>
                </div>
                
                <h3>Đặt phòng đã được xác nhận!</h3>
                <p>Bạn sẽ nhận được email xác nhận đặt phòng chi tiết trong vài phút tới.</p>
                
                <div class='footer'>
                    <p><strong>Hỗ trợ khách hàng 24/7:</strong></p>
                    <p>📞 Hotline: 1900-xxxx | 📧 Email: support@bookinghotel.com</p>
                    <p>© 2024 Booking Hotel. Tất cả quyền được bảo lưu.</p>
                </div>
            </div>
        </div>
    </body>
    </html>";
}

// Test email functionality
if (isset($_GET['test'])) {
    $test_booking = [
        'booking_id' => 'BK' . uniqid(),
        'hotel_name' => 'Hotel ABC',
        'room_type_name' => 'Phòng Deluxe',
        'checkin_date' => '2024-01-15',
        'checkout_date' => '2024-01-17',
        'max_guests' => 2,
        'total_price' => 1600000,
        'customer_email' => 'test@example.com'
    ];
    
    if (sendBookingConfirmation($test_booking)) {
        echo "Email gửi thành công!";
    } else {
        echo "Lỗi gửi email!";
    }
}
?>

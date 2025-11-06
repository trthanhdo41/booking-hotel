<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$booking_id = $input['booking_id'] ?? '';
$payment_method = $input['payment_method'] ?? '';
$amount = $input['amount'] ?? 0;

if (!$booking_id || !$payment_method) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Lấy dữ liệu bookings hiện tại
    $bookings_data = getAllBookings();
    
    // Tìm booking cần cập nhật theo booking_id
    $booking_found = false;
    $booking_row_index = -1;
    
    // Tìm booking theo ID (cột 0) - thử cả số và chuỗi
    for ($i = 1; $i < count($bookings_data); $i++) {
        $booking = $bookings_data[$i];
        if (count($booking) >= 1 && ($booking[0] == $booking_id || $booking[0] == (int)$booking_id)) {
            $booking_found = true;
            $booking_row_index = $i;
            break;
        }
    }
    
    // Nếu không tìm thấy theo ID, tìm booking gần nhất có status pending
    if (!$booking_found) {
        for ($i = count($bookings_data) - 1; $i >= 1; $i--) {
            $booking = $bookings_data[$i];
            if (count($booking) >= 10 && isset($booking[9]) && $booking[9] === 'pending') {
                $booking_found = true;
                $booking_row_index = $i;
                break;
            }
        }
    }
    
    if (!$booking_found) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }
    
    // Cập nhật trạng thái thanh toán
    $service = getGoogleSheetsClient();
    $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
    
    // Cập nhật status thành 'completed' (cột K = 11)
    $status_range = "bookings!K" . ($booking_row_index + 1);
    $status_values = [['completed']];
    $status_body = new Google_Service_Sheets_ValueRange(['values' => $status_values]);
    $service->spreadsheets_values->update($spreadsheetId, $status_range, $status_body, ['valueInputOption' => 'RAW']);
    
    // Cập nhật payment_method (cột M = 13)
    $payment_method_range = "bookings!M" . ($booking_row_index + 1);
    $payment_method_values = [[$payment_method]];
    $payment_method_body = new Google_Service_Sheets_ValueRange(['values' => $payment_method_values]);
    $service->spreadsheets_values->update($spreadsheetId, $payment_method_range, $payment_method_body, ['valueInputOption' => 'RAW']);
    
    // Cập nhật payment_id nếu chưa có (cột N = 14)
    $current_booking = $bookings_data[$booking_row_index];
    if (count($current_booking) >= 14 && empty($current_booking[13])) {
        $payment_id = 'PAY_' . strtoupper(substr(md5($booking_id . time()), 0, 8));
        $payment_id_range = "bookings!N" . ($booking_row_index + 1);
        $payment_id_values = [[$payment_id]];
        $payment_id_body = new Google_Service_Sheets_ValueRange(['values' => $payment_id_values]);
        $service->spreadsheets_values->update($spreadsheetId, $payment_id_range, $payment_id_body, ['valueInputOption' => 'RAW']);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Payment status updated successfully',
        'booking_id' => $booking_id,
        'payment_method' => $payment_method,
        'status' => 'completed'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating payment: ' . $e->getMessage()]);
}
?>

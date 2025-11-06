<?php
require_once 'config/database.php';

echo "<h2>🔧 Tự động tạo Google Sheets Database</h2>";

try {
    $service = getGoogleSheetsClient();
    $spreadsheet_id = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
    
    echo "✅ Kết nối Google Sheets thành công!<br><br>";
    
    // Tạo các sheet trước
    $sheet_names = ['hotels', 'room_types', 'rooms', 'bookings'];
    
    foreach ($sheet_names as $sheet_name) {
        echo "📋 Tạo sheet '$sheet_name'...<br>";
        
        // Tạo sheet mới
        $sheetProperties = new Google_Service_Sheets_SheetProperties();
        $sheetProperties->setTitle($sheet_name);
        
        $addSheetRequest = new Google_Service_Sheets_AddSheetRequest();
        $addSheetRequest->setProperties($sheetProperties);
        
        $request = new Google_Service_Sheets_Request();
        $request->setAddSheet($addSheetRequest);
        
        $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest();
        $batchUpdateRequest->setRequests([$request]);
        
        try {
            $service->spreadsheets->batchUpdate($spreadsheet_id, $batchUpdateRequest);
            echo "✅ Sheet '$sheet_name' đã được tạo<br>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "ℹ️ Sheet '$sheet_name' đã tồn tại<br>";
            } else {
                throw $e;
            }
        }
    }
    
    echo "<br>";
    
    // 1. Thêm dữ liệu vào sheet hotels
    echo "📊 Thêm dữ liệu vào sheet 'hotels'...<br>";
    $hotels_data = [
        ['id', 'name', 'address', 'city', 'phone', 'email'],
        [1, 'Hotel ABC', '123 Đường ABC', 'Hà Nội', '0123456789', 'info@hotelabc.com'],
        [2, 'Hotel XYZ', '456 Đường XYZ', 'TP.HCM', '0987654321', 'info@hotelxyz.com'],
        [3, 'Hotel DEF', '789 Đường DEF', 'Đà Nẵng', '0369852147', 'info@hoteldef.com']
    ];
    writeSheetData('hotels', $hotels_data, 'A1');
    echo "✅ Dữ liệu 'hotels' đã được thêm với " . count($hotels_data) . " dòng<br>";
    
    // 2. Thêm dữ liệu vào sheet room_types
    echo "📊 Thêm dữ liệu vào sheet 'room_types'...<br>";
    $room_types_data = [
        ['id', 'hotel_id', 'name', 'description', 'price_per_night', 'image_url'],
        [1, 1, 'Phòng Standard', 'Phòng đơn tiêu chuẩn', 500000, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=500'],
        [2, 1, 'Phòng Deluxe', 'Phòng đôi cao cấp', 800000, 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=500'],
        [3, 1, 'Phòng Suite', 'Phòng suite sang trọng', 1200000, 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=500'],
        [4, 2, 'Phòng Standard', 'Phòng đơn tiêu chuẩn', 600000, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=500'],
        [5, 2, 'Phòng Deluxe', 'Phòng đôi cao cấp', 900000, 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=500'],
        [6, 3, 'Phòng Standard', 'Phòng đơn tiêu chuẩn', 400000, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=500'],
        [7, 3, 'Phòng Deluxe', 'Phòng đôi cao cấp', 700000, 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=500']
    ];
    writeSheetData('room_types', $room_types_data, 'A1');
    echo "✅ Dữ liệu 'room_types' đã được thêm với " . count($room_types_data) . " dòng<br>";
    
    // 3. Thêm dữ liệu vào sheet rooms
    echo "📊 Thêm dữ liệu vào sheet 'rooms'...<br>";
    $rooms_data = [
        ['id', 'hotel_id', 'room_type_id', 'room_number', 'status'],
        [1, 1, 1, '101', 'available'],
        [2, 1, 1, '102', 'available'],
        [3, 1, 1, '103', 'available'],
        [4, 1, 2, '201', 'available'],
        [5, 1, 2, '202', 'available'],
        [6, 1, 3, '301', 'available'],
        [7, 2, 4, '101', 'available'],
        [8, 2, 4, '102', 'available'],
        [9, 2, 5, '201', 'available'],
        [10, 2, 5, '202', 'available'],
        [11, 3, 6, '101', 'available'],
        [12, 3, 6, '102', 'available'],
        [13, 3, 7, '201', 'available'],
        [14, 3, 7, '202', 'available']
    ];
    writeSheetData('rooms', $rooms_data, 'A1');
    echo "✅ Dữ liệu 'rooms' đã được thêm với " . count($rooms_data) . " dòng<br>";
    
    // 4. Thêm dữ liệu vào sheet bookings
    echo "📊 Thêm dữ liệu vào sheet 'bookings'...<br>";
    $bookings_data = [
        ['id', 'hotel_id', 'room_type_id', 'customer_name', 'customer_email', 'customer_phone', 'checkin_date', 'checkout_date', 'total_price', 'status', 'payment_status', 'payment_method', 'payment_id', 'notes', 'created_at'],
        [1, 1, 1, 'Nguyễn Văn A', 'nguyenvana@email.com', '0123456789', '2024-10-01', '2024-10-03', 1000000, 'confirmed', 'paid', 'vnpay', 'VNPAY123456', 'Khách VIP', '2024-09-26 20:00:00'],
        [2, 2, 5, 'Trần Thị B', 'tranthib@email.com', '0987654321', '2024-10-05', '2024-10-07', 1800000, 'pending', 'pending', '', '', '', '2024-09-26 20:05:00']
    ];
    writeSheetData('bookings', $bookings_data, 'A1');
    echo "✅ Dữ liệu 'bookings' đã được thêm với " . count($bookings_data) . " dòng<br>";
    
    echo "<br><h3>🎉 Hoàn thành! Database đã được tạo thành công!</h3>";
    echo "<p>📊 <strong>Tổng kết:</strong></p>";
    echo "<ul>";
    echo "<li>🏨 Hotels: " . (count($hotels_data) - 1) . " khách sạn</li>";
    echo "<li>🛏️ Room Types: " . (count($room_types_data) - 1) . " loại phòng</li>";
    echo "<li>🚪 Rooms: " . (count($rooms_data) - 1) . " phòng</li>";
    echo "<li>📋 Bookings: " . (count($bookings_data) - 1) . " đặt phòng</li>";
    echo "</ul>";
    
    echo "<p><a href='test_google_sheets.php'>🧪 Test kết nối</a> | <a href='index.php'>🏠 Về trang chủ</a></p>";
    
} catch (Exception $e) {
    echo "❌ <strong>Lỗi:</strong> " . $e->getMessage() . "<br>";
    echo "<p>🔧 <strong>Kiểm tra:</strong></p>";
    echo "<ul>";
    echo "<li>Service Account đã được cấp quyền Editor chưa?</li>";
    echo "<li>Google Sheets API đã được enable chưa?</li>";
    echo "<li>File credentials có đúng không?</li>";
    echo "</ul>";
}
?>

<?php
echo "<h2>🔧 Test Google Sheets Connection</h2>";

// Test kết nối Google Sheets
try {
    require_once 'config/database.php';
    
    echo "✅ <strong>Google Sheets API đã được load!</strong><br><br>";
    
    // Test đọc dữ liệu
    echo "<h3>📊 Test đọc dữ liệu:</h3>";
    
    $hotels = getAllHotels();
    echo "🏨 Số lượng khách sạn: " . count($hotels) . "<br>";
    
    if (!empty($hotels)) {
        echo "<h4>Danh sách khách sạn:</h4>";
        echo "<ul>";
        foreach ($hotels as $index => $hotel) {
            if ($index == 0) continue; // Bỏ qua header
            echo "<li>" . $hotel[1] . " - " . $hotel[2] . "</li>";
        }
        echo "</ul>";
    }
    
    $room_types = getAllRoomTypes();
    echo "🛏️ Số lượng loại phòng: " . count($room_types) . "<br>";
    
    $rooms = getAllRooms();
    echo "🚪 Số lượng phòng: " . count($rooms) . "<br>";
    
    $bookings = getAllBookings();
    echo "📋 Số lượng đặt phòng: " . count($bookings) . "<br>";
    
} catch (Exception $e) {
    echo "❌ <strong>Lỗi:</strong> " . $e->getMessage() . "<br>";
    echo "<p>🔧 <strong>Giải pháp:</strong></p>";
    echo "<ul>";
    echo "<li>Kiểm tra file <code>config/google-credentials.json</code> có tồn tại không</li>";
    echo "<li>Kiểm tra <code>spreadsheet_id</code> trong <code>config/database.php</code></li>";
    echo "<li>Đảm bảo Service Account có quyền truy cập Google Sheet</li>";
    echo "</ul>";
}
?>

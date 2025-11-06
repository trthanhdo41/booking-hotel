<?php
// Cấu hình Google Sheets API
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Google Sheets API credentials
$credentials_path = dirname(__DIR__) . '/config/google-credentials.json';
$spreadsheet_id = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo'; // ID của Google Sheet

// Khởi tạo Google Sheets client
function getGoogleSheetsClient() {
    global $credentials_path;
    
    $client = new Google_Client();
    $client->setApplicationName('Hotel Booking System');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
    $client->setAuthConfig($credentials_path);
    
    return new Google_Service_Sheets($client);
}

// Hàm lấy dữ liệu từ sheet
function getSheetData($sheet_name, $range = 'A:Z') {
    global $spreadsheet_id;
    
    try {
        $service = getGoogleSheetsClient();
        $response = $service->spreadsheets_values->get($spreadsheet_id, $sheet_name . '!' . $range);
        return $response->getValues();
    } catch (Exception $e) {
        die("Lỗi đọc Google Sheets: " . $e->getMessage());
    }
}

// Hàm ghi dữ liệu vào sheet
function writeSheetData($sheet_name, $values, $range = 'A1') {
    global $spreadsheet_id;
    
    try {
        $service = getGoogleSheetsClient();
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);
        
        $params = [
            'valueInputOption' => 'RAW'
        ];
        
        $service->spreadsheets_values->update(
            $spreadsheet_id,
            $sheet_name . '!' . $range,
            $body,
            $params
        );
        
        return true;
    } catch (Exception $e) {
        die("Lỗi ghi Google Sheets: " . $e->getMessage());
    }
}

// Hàm lấy sheet ID theo tên sheet
function getSheetId($sheet_name) {
    global $spreadsheet_id;
    
    try {
        $service = getGoogleSheetsClient();
        $spreadsheet = $service->spreadsheets->get($spreadsheet_id);
        $sheets = $spreadsheet->getSheets();
        
        foreach ($sheets as $sheet) {
            if ($sheet->getProperties()->getTitle() === $sheet_name) {
                return $sheet->getProperties()->getSheetId();
            }
        }
        return 0; // Mặc định là sheet đầu tiên
    } catch (Exception $e) {
        return 0; // Fallback
    }
}

// Hàm helper cho Google Sheets
function getAllHotels() {
    return getSheetData('hotels');
}

function getAllRoomTypes() {
    return getSheetData('room_types');
}

function getAllRooms() {
    return getSheetData('rooms');
}

function getAllBookings() {
    return getSheetData('bookings');
}

function addBooking($booking_data) {
    // Lấy dữ liệu bookings hiện tại
    $bookings = getAllBookings();
    
    // Tìm ID cao nhất hiện có và dòng trống đầu tiên
    $max_id = 0;
    $first_empty_row = -1;
    
    for ($i = 1; $i < count($bookings); $i++) {
        $booking = $bookings[$i];
        if (count($booking) >= 1 && !empty($booking[0]) && is_numeric($booking[0])) {
            $max_id = max($max_id, (int)$booking[0]);
        } else if ($first_empty_row === -1 && (count($booking) == 0 || (count($booking) == 1 && empty($booking[0])))) {
            $first_empty_row = $i + 1; // +1 vì array index bắt đầu từ 0, Google Sheets từ 1
        }
    }
    
    $next_id = $max_id + 1;
    
    // Tạo room_id ngẫu nhiên từ danh sách room_types hợp lệ
    $room_types = getAllRoomTypes();
    $valid_room_ids = [];
    for ($i = 1; $i < count($room_types); $i++) {
        $room = $room_types[$i];
        if (count($room) >= 1 && is_numeric($room[0])) {
            $valid_room_ids[] = (int)$room[0];
        }
    }
    $room_id = $valid_room_ids[array_rand($valid_room_ids)]; // Chọn ngẫu nhiên từ danh sách hợp lệ
    
    $values = [
        [
            $next_id, // id
            $booking_data['user_id'] ?? 0, // user_id
            $room_id, // room_id
            $booking_data['customer_name'], // guest_name
            $booking_data['customer_email'], // guest_email
            $booking_data['customer_phone'], // guest_phone
            $booking_data['checkin_date'], // checkin_date
            $booking_data['checkout_date'], // checkout_date
            $booking_data['guests'] ?? 2, // guests
            $booking_data['total_price'], // total_price
            $booking_data['status'], // status
            date('Y-m-d H:i:s'), // created_at
            $booking_data['payment_method'] ?? 'qr_code', // payment_method
            'PAY_' . strtoupper(substr(md5($next_id . time()), 0, 8)), // payment_id
            $booking_data['notes'] ?? 'Đặt phòng online' // notes
        ]
    ];
    
    // Nếu có dòng trống, ghi vào dòng trống đó, nếu không thì ghi vào cuối
    $target_row = ($first_empty_row !== -1) ? $first_empty_row : (count($bookings) + 1);
    return writeSheetData('bookings', $values, 'A' . $target_row);
}

// Hàm format giá tiền
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' VNĐ';
}

// Hàm lấy tất cả users
function getAllUsers() {
    return getSheetData('users');
}

// Hàm thêm user mới
function addUser($user_data) {
    // Lấy dữ liệu users hiện tại
    $users = getAllUsers();
    
    // Nếu sheet trống hoặc null, khởi tạo
    if (!$users || count($users) <= 1) {
        $users = [['id', 'username', 'password', 'email', 'full_name', 'phone', 'role', 'status', 'created_at', 'updated_at']];
    }
    
    // Tìm ID cao nhất hiện có
    $max_id = 0;
    for ($i = 1; $i < count($users); $i++) {
        $user = $users[$i];
        if (count($user) >= 1 && !empty($user[0]) && is_numeric($user[0])) {
            $max_id = max($max_id, (int)$user[0]);
        }
    }
    
    $next_id = $max_id + 1;
    
    // Xác định role dựa trên username
    $role = ($user_data['username'] === 'admin') ? 'admin' : 'user';
    
    $values = [
        [
            $next_id, // id
            $user_data['username'], // username
            password_hash($user_data['password'], PASSWORD_DEFAULT), // password (hashed)
            $user_data['email'], // email
            $user_data['full_name'], // full_name
            $user_data['phone'] ?? '', // phone
            $role, // role (user/admin)
            'active', // status
            date('Y-m-d H:i:s'), // created_at
            date('Y-m-d H:i:s') // updated_at
        ]
    ];
    
    return writeSheetData('users', $values, 'A' . (count($users) + 1));
}

// Hàm tìm user theo username hoặc email
function findUser($username_or_email) {
    $users = getAllUsers();
    
    for ($i = 1; $i < count($users); $i++) {
        $user = $users[$i];
        if (count($user) >= 4) {
            $username = $user[1] ?? '';
            $email = $user[3] ?? '';
            
            if ($username === $username_or_email || $email === $username_or_email) {
                return [
                    'id' => $user[0],
                    'username' => $username,
                    'password' => $user[2] ?? '',
                    'email' => $email,
                    'full_name' => $user[4] ?? '',
                    'phone' => $user[5] ?? '',
                    'role' => $user[6] ?? 'user',
                    'status' => $user[7] ?? 'active',
                    'created_at' => $user[8] ?? '',
                    'updated_at' => $user[9] ?? ''
                ];
            }
        }
    }
    
    return null;
}

// Hàm format ngày
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Hàm lấy lịch sử đặt phòng theo user ID
function getBookingsByUserId($user_id) {
    $bookings = getAllBookings();
    $room_types = getAllRoomTypes();
    $hotels = getAllHotels();
    $user_bookings = [];
    
    for ($i = 1; $i < count($bookings); $i++) {
        $booking = $bookings[$i];
        // Chỉ tìm theo user_id (cột 1), không dùng email fallback
        $is_user_booking = false;
        
        if (count($booking) >= 2 && isset($booking[1]) && is_numeric($booking[1]) && $booking[1] == $user_id) {
            $is_user_booking = true;
        }
        
        if ($is_user_booking) {
            // Tìm thông tin phòng và khách sạn
            $room_id = $booking[2]; // room_id ở cột 2 (sau user_id)
            $hotel_name = 'Khách sạn không xác định';
            $room_type = 'Phòng không xác định';
            
            // Tìm thông tin phòng từ room_types
            foreach ($room_types as $rt_index => $rt_row) {
                if ($rt_index === 0) continue;
                if (count($rt_row) >= 5 && $rt_row[0] == $room_id) {
                    $room_type = $rt_row[2] ?? 'Phòng không xác định';
                    $hotel_id = $rt_row[1] ?? 0;
                    
                    // Tìm thông tin khách sạn
                    foreach ($hotels as $h_index => $h_row) {
                        if ($h_index === 0) continue;
                        if (count($h_row) >= 6 && $h_row[0] == $hotel_id) {
                            $hotel_name = $h_row[1] ?? 'Khách sạn không xác định';
                            break;
                        }
                    }
                    break;
                }
            }
            
            // Kiểm tra và sửa dữ liệu bị lệch cột
            $total_price = 0;
            $status = 'pending';
            
            // Tìm total_price (số lớn nhất trong các cột)
            for ($col = 9; $col < count($booking); $col++) {
                if (is_numeric($booking[$col]) && $booking[$col] > $total_price) {
                    $total_price = $booking[$col];
                }
            }
            
            // Tìm status (pending, confirmed, cancelled, completed)
            for ($col = 9; $col < count($booking); $col++) {
                if (in_array($booking[$col], ['pending', 'confirmed', 'cancelled', 'completed'])) {
                    $status = $booking[$col];
                    break;
                }
            }
            
            $user_bookings[] = [
                'id' => $booking[0],
                'user_id' => $booking[1], // user_id ở cột 1
                'room_id' => $booking[2],
                'hotel_name' => $hotel_name,
                'room_type' => $room_type,
                'guest_name' => $booking[3],
                'email' => $booking[4],
                'phone' => $booking[5],
                'checkin_date' => $booking[6],
                'checkout_date' => $booking[7],
                'guests' => $booking[8],
                'total_price' => $total_price,
                'status' => $status,
                'created_at' => $booking[11] ?? date('Y-m-d H:i:s'),
                'payment_method' => $booking[12] ?? 'qr_code',
                'payment_id' => $booking[13] ?? '',
                'notes' => $booking[14] ?? ''
            ];
        }
    }
    
    // Sắp xếp theo ngày tạo mới nhất
    usort($user_bookings, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return $user_bookings;
}

// Hàm lấy thông tin user theo ID
function getUserById($user_id) {
    $users = getAllUsers();
    
    for ($i = 1; $i < count($users); $i++) {
        $user = $users[$i];
        if (count($user) >= 1 && $user[0] == $user_id) {
            return [
                'id' => $user[0],
                'username' => $user[1],
                'email' => $user[3],
                'full_name' => $user[4],
                'phone' => $user[5],
                'role' => $user[6],
                'status' => $user[7],
                'created_at' => $user[8],
                'updated_at' => $user[9]
            ];
        }
    }
    
    return null;
}

// Hàm tính số ngày
function calculateDays($checkin, $checkout) {
    $checkin = new DateTime($checkin);
    $checkout = new DateTime($checkout);
    return $checkout->diff($checkin)->days;
}

// Hàm cập nhật một row trong bookings
function updateBookingRow($row_number, $data) {
    global $service, $spreadsheet_id;
    
    try {
        $range = 'bookings!A' . $row_number . ':O' . $row_number;
        
        $values = [$data];
        
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $values
        ]);
        
        $params = [
            'valueInputOption' => 'RAW'
        ];
        
        $result = $service->spreadsheets_values->update(
            $spreadsheet_id,
            $range,
            $body,
            $params
        );
        
        return true;
    } catch (Exception $e) {
        error_log("Error updating booking row: " . $e->getMessage());
        return false;
    }
}
?>

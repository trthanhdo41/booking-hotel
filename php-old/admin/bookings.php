<?php
session_start();
include dirname(__DIR__) . '/config/database.php';

// Kiểm tra đăng nhập admin
$is_admin = false;

// Kiểm tra session admin (đăng nhập trực tiếp vào admin)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $_SESSION['admin_role'] === 'admin') {
    $is_admin = true;
}
// Kiểm tra session user với role admin (đăng nhập từ trang chủ)
elseif (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $is_admin = true;
    // Đồng bộ session admin
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $_SESSION['user_id'];
    $_SESSION['admin_username'] = $_SESSION['username'] ?? 'admin';
    $_SESSION['admin_role'] = 'admin';
}

if (!$is_admin) {
    header('Location: login.php');
    exit;
}

$page_title = "Quản lý đặt phòng - Admin";

// Xử lý cập nhật trạng thái booking
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $booking_id = (int)($_POST['booking_id'] ?? 0);
        $new_status = trim($_POST['status'] ?? '');
        
        if ($booking_id && $new_status) {
            // Cập nhật trạng thái booking
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của booking
            $bookings = getAllBookings();
            $row_index = -1;
            for ($i = 1; $i < count($bookings); $i++) {
                if (count($bookings[$i]) >= 1 && $bookings[$i][0] == $booking_id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                $range = "bookings!J$row_index"; // Status ở cột J (index 9)
                $values = [[$new_status]];
                $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
                $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);
                $message = 'Cập nhật trạng thái đặt phòng thành công!';
            } else {
                $error = 'Không tìm thấy đặt phòng.';
            }
        } else {
            $error = 'Vui lòng chọn trạng thái mới.';
        }
    }
}

// Lấy dữ liệu
$bookings = getAllBookings();
$room_types = getAllRoomTypes();
$hotels = getAllHotels();
$users = getAllUsers();

// Tạo danh sách booking với thông tin đầy đủ
$bookings_data = [];
for ($i = 1; $i < count($bookings); $i++) {
    $booking = $bookings[$i];
    if (count($booking) >= 10) {
        // Tìm thông tin khách sạn và loại phòng
        $room_id = $booking[2] ?? 0;
        $hotel_name = 'Khách sạn không xác định';
        $room_type_name = 'Loại phòng không xác định';
        
        foreach ($room_types as $rt_index => $rt_row) {
            if ($rt_index === 0) continue;
            if (count($rt_row) >= 5 && $rt_row[0] == $room_id) {
                $room_type_name = $rt_row[2] ?? 'Loại phòng không xác định';
                $hotel_id = $rt_row[1] ?? 0;
                
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
        
        // Tìm thông tin người dùng
        $user_name = 'Khách không xác định';
        $user_id = $booking[1] ?? 0;
        foreach ($users as $u_index => $u_row) {
            if ($u_index === 0) continue;
            if (count($u_row) >= 6 && $u_row[0] == $user_id) {
                $user_name = $u_row[4] ?? 'Khách không xác định';
                break;
            }
        }
        
        // Tìm total_price và status (có thể bị lệch cột)
        $total_price = 0;
        $status = 'pending';
        
        for ($col = 8; $col < count($booking); $col++) {
            if (is_numeric($booking[$col]) && $booking[$col] > $total_price) {
                $total_price = $booking[$col];
            }
        }
        
        for ($col = 8; $col < count($booking); $col++) {
            if (in_array($booking[$col], ['pending', 'confirmed', 'cancelled', 'completed'])) {
                $status = $booking[$col];
                break;
            }
        }
        
        // Tìm created_at
        $created_at = date('Y-m-d H:i:s');
        for ($col = 10; $col < count($booking); $col++) {
            if (isset($booking[$col]) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $booking[$col])) {
                $created_at = $booking[$col];
                break;
            }
        }
        
        $bookings_data[] = [
            'id' => $booking[0],
            'user_id' => $booking[1],
            'user_name' => $user_name,
            'room_id' => $booking[2],
            'hotel_name' => $hotel_name,
            'room_type_name' => $room_type_name,
            'guest_name' => $booking[3],
            'email' => $booking[4],
            'phone' => $booking[5],
            'checkin_date' => $booking[6],
            'checkout_date' => $booking[7],
            'guests' => $booking[8],
            'total_price' => $total_price,
            'status' => $status,
            'created_at' => $created_at
        ];
    }
}

// Sắp xếp theo ngày tạo mới nhất
usort($bookings_data, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Lọc theo trạng thái nếu có
$filter_status = $_GET['status'] ?? '';
if ($filter_status) {
    $bookings_data = array_filter($bookings_data, function($booking) use ($filter_status) {
        return $booking['status'] === $filter_status;
    });
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="flex items-center space-x-2">
                        <i class="fas fa-calendar-check text-2xl text-yellow-600"></i>
                        <span class="text-xl font-bold text-gray-800">Quản lý đặt phòng</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i>Dashboard
                    </a>
                    <a href="logout.php" class="text-red-600 hover:text-red-700 transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i>Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Quản lý đặt phòng</h1>
                <p class="text-gray-600">Xem và quản lý tất cả đặt phòng</p>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i><?php echo $message; ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Bộ lọc</h2>
                <div class="flex flex-wrap gap-4">
                    <a href="bookings.php" class="px-4 py-2 rounded-md <?php echo !$filter_status ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Tất cả (<?php echo count(getAllBookings()) - 1; ?>)
                    </a>
                    <a href="bookings.php?status=pending" class="px-4 py-2 rounded-md <?php echo $filter_status === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Chờ xác nhận
                    </a>
                    <a href="bookings.php?status=confirmed" class="px-4 py-2 rounded-md <?php echo $filter_status === 'confirmed' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Đã xác nhận
                    </a>
                    <a href="bookings.php?status=completed" class="px-4 py-2 rounded-md <?php echo $filter_status === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Hoàn thành
                    </a>
                    <a href="bookings.php?status=cancelled" class="px-4 py-2 rounded-md <?php echo $filter_status === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                        Đã hủy
                    </a>
                </div>
            </div>

            <!-- Bookings List -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">
                    Danh sách đặt phòng 
                    <?php if ($filter_status): ?>
                        - <?php 
                        switch($filter_status) {
                            case 'pending': echo 'Chờ xác nhận'; break;
                            case 'confirmed': echo 'Đã xác nhận'; break;
                            case 'completed': echo 'Hoàn thành'; break;
                            case 'cancelled': echo 'Đã hủy'; break;
                        }
                        ?>
                    <?php endif; ?>
                    (<?php echo count($bookings_data); ?>)
                </h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Khách hàng</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Khách sạn</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Loại phòng</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Ngày</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Trạng thái</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings_data as $booking): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-900">#<?php echo $booking['id']; ?></td>
                                <td class="py-3 px-4 text-gray-700">
                                    <div>
                                        <div class="font-medium"><?php echo htmlspecialchars($booking['guest_name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($booking['email']); ?></div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($booking['hotel_name']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($booking['room_type_name']); ?></td>
                                <td class="py-3 px-4 text-gray-700">
                                    <div class="text-sm">
                                        <div><?php echo date('d/m/Y', strtotime($booking['checkin_date'])); ?></div>
                                        <div class="text-gray-500">đến <?php echo date('d/m/Y', strtotime($booking['checkout_date'])); ?></div>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                        <?php 
                                        switch($booking['status']) {
                                            case 'confirmed': echo 'bg-green-100 text-green-800'; break;
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                            case 'completed': echo 'bg-blue-100 text-blue-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php 
                                        switch($booking['status']) {
                                            case 'confirmed': echo 'Đã xác nhận'; break;
                                            case 'pending': echo 'Chờ xác nhận'; break;
                                            case 'cancelled': echo 'Đã hủy'; break;
                                            case 'completed': echo 'Hoàn thành'; break;
                                            default: echo ucfirst($booking['status']);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <button onclick="updateStatus(<?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>')" 
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Cập nhật
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Update Status Modal -->
    <div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Cập nhật trạng thái</h3>
                <p class="text-gray-600 mb-6">Chọn trạng thái mới cho đặt phòng #<span id="update_booking_id"></span></p>
                
                <form id="updateForm" method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="booking_id" id="update_booking_id_input">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái mới</label>
                        <select name="status" id="update_status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending">Chờ xác nhận</option>
                            <option value="confirmed">Đã xác nhận</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeUpdateModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Hủy
                        </button>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(bookingId, currentStatus) {
            document.getElementById('update_booking_id').textContent = bookingId;
            document.getElementById('update_booking_id_input').value = bookingId;
            document.getElementById('update_status').value = currentStatus;
            document.getElementById('updateModal').classList.remove('hidden');
        }

        function closeUpdateModal() {
            document.getElementById('updateModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const updateModal = document.getElementById('updateModal');
            if (event.target === updateModal) {
                closeUpdateModal();
            }
        }
    </script>
</body>
</html>

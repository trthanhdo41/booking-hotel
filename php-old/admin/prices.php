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

$page_title = "Quản lý giá phòng - Admin";

// Xử lý cập nhật giá phòng
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_price') {
        $room_type_id = (int)($_POST['room_type_id'] ?? 0);
        $new_price = (int)($_POST['new_price'] ?? 0);
        
        if ($room_type_id && $new_price > 0) {
            // Cập nhật giá phòng
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của loại phòng
            $room_types = getAllRoomTypes();
            $row_index = -1;
            for ($i = 1; $i < count($room_types); $i++) {
                if (count($room_types[$i]) >= 1 && $room_types[$i][0] == $room_type_id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                $range = "room_types!E$row_index"; // Price ở cột E (index 4)
                $values = [[$new_price]];
                $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
                $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);
                $message = 'Cập nhật giá phòng thành công!';
            } else {
                $error = 'Không tìm thấy loại phòng.';
            }
        } else {
            $error = 'Vui lòng nhập giá hợp lệ.';
        }
    }
    
    if ($action === 'bulk_update') {
        $hotel_id = (int)($_POST['hotel_id'] ?? 0);
        $percentage = (float)($_POST['percentage'] ?? 0);
        $operation = $_POST['operation'] ?? 'increase';
        
        if ($hotel_id && $percentage > 0) {
            // Cập nhật hàng loạt giá phòng của khách sạn
            $room_types = getAllRoomTypes();
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            $updates = [];
            for ($i = 1; $i < count($room_types); $i++) {
                $room_type = $room_types[$i];
                if (count($room_type) >= 5 && $room_type[1] == $hotel_id) {
                    $current_price = (int)($room_type[4] ?? 0);
                    if ($current_price > 0) {
                        if ($operation === 'increase') {
                            $new_price = $current_price + ($current_price * $percentage / 100);
                        } else {
                            $new_price = $current_price - ($current_price * $percentage / 100);
                        }
                        $new_price = max(0, round($new_price)); // Đảm bảo giá không âm
                        
                        $updates[] = [
                            'range' => "room_types!E" . ($i + 1),
                            'values' => [[$new_price]]
                        ];
                    }
                }
            }
            
            if (!empty($updates)) {
                $body = new Google_Service_Sheets_BatchUpdateValuesRequest([
                    'valueInputOption' => 'RAW',
                    'data' => $updates
                ]);
                $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);
                $message = 'Cập nhật hàng loạt giá phòng thành công! (' . count($updates) . ' loại phòng)';
            } else {
                $error = 'Không tìm thấy loại phòng nào để cập nhật.';
            }
        } else {
            $error = 'Vui lòng chọn khách sạn và nhập phần trăm hợp lệ.';
        }
    }
}

// Lấy dữ liệu
$hotels = getAllHotels();
$room_types = getAllRoomTypes();

// Tạo danh sách khách sạn cho select
$hotels_list = [];
for ($i = 1; $i < count($hotels); $i++) {
    $hotel = $hotels[$i];
    if (count($hotel) >= 4) {
        $hotels_list[] = [
            'id' => $hotel[0],
            'name' => $hotel[1],
            'city' => $hotel[3]
        ];
    }
}

// Tạo danh sách loại phòng với thông tin khách sạn
$room_types_data = [];
for ($i = 1; $i < count($room_types); $i++) {
    $room_type = $room_types[$i];
    if (count($room_type) >= 9) {
        // Tìm tên khách sạn
        $hotel_name = 'Khách sạn không xác định';
        foreach ($hotels_list as $hotel) {
            if ($hotel['id'] == $room_type[1]) {
                $hotel_name = $hotel['name'];
                break;
            }
        }
        
        $room_types_data[] = [
            'id' => $room_type[0],
            'hotel_id' => $room_type[1],
            'hotel_name' => $hotel_name,
            'name' => $room_type[2],
            'price' => $room_type[4],
            'size' => $room_type[5],
            'max_guests' => $room_type[7]
        ];
    }
}

// Sắp xếp theo khách sạn và tên loại phòng
usort($room_types_data, function($a, $b) {
    if ($a['hotel_name'] === $b['hotel_name']) {
        return strcmp($a['name'], $b['name']);
    }
    return strcmp($a['hotel_name'], $b['hotel_name']);
});
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
                        <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
                        <span class="text-xl font-bold text-gray-800">Quản lý giá phòng</span>
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Quản lý giá phòng</h1>
                <p class="text-gray-600">Cập nhật giá phòng và quản lý khuyến mại</p>
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

            <!-- Bulk Update Form -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Cập nhật hàng loạt</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <input type="hidden" name="action" value="bulk_update">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Khách sạn</label>
                        <select name="hotel_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Chọn khách sạn</option>
                            <?php foreach ($hotels_list as $hotel): ?>
                            <option value="<?php echo $hotel['id']; ?>"><?php echo htmlspecialchars($hotel['name'] . ' - ' . $hotel['city']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Thao tác</label>
                        <select name="operation" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="increase">Tăng giá</option>
                            <option value="decrease">Giảm giá</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phần trăm (%)</label>
                        <input type="number" name="percentage" required min="0" max="100" step="0.1" placeholder="VD: 10" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-orange-600 text-white px-6 py-2 rounded-md hover:bg-orange-700 transition-colors">
                            <i class="fas fa-calculator mr-2"></i>Cập nhật hàng loạt
                        </button>
                    </div>
                </form>
            </div>

            <!-- Room Types List -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Danh sách loại phòng và giá (<?php echo count($room_types_data); ?>)</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Khách sạn</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Loại phòng</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Diện tích</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Khách</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Giá hiện tại</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($room_types_data as $room_type): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-900"><?php echo $room_type['id']; ?></td>
                                <td class="py-3 px-4 text-gray-700 font-medium"><?php echo htmlspecialchars($room_type['hotel_name']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($room_type['name']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($room_type['size']); ?></td>
                                <td class="py-3 px-4 text-gray-700">
                                    <i class="fas fa-user mr-1"></i><?php echo $room_type['max_guests']; ?>
                                </td>
                                <td class="py-3 px-4 text-gray-700 font-semibold text-green-600">
                                    <?php echo number_format($room_type['price'], 0, ',', '.'); ?> VNĐ
                                </td>
                                <td class="py-3 px-4">
                                    <button onclick="updatePrice(<?php echo $room_type['id']; ?>, '<?php echo htmlspecialchars($room_type['name']); ?>', <?php echo $room_type['price']; ?>)" 
                                            class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-edit mr-1"></i>Cập nhật giá
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Update Price Modal -->
    <div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Cập nhật giá phòng</h3>
                <p class="text-gray-600 mb-6">Cập nhật giá cho loại phòng: <strong id="update_room_type_name"></strong></p>
                
                <form id="updateForm" method="POST">
                    <input type="hidden" name="action" value="update_price">
                    <input type="hidden" name="room_type_id" id="update_room_type_id">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Giá mới (VNĐ)</label>
                        <input type="number" name="new_price" id="update_new_price" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Giá hiện tại: <span id="current_price_display"></span> VNĐ</p>
                    </div>
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeUpdateModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Hủy
                        </button>
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updatePrice(roomTypeId, roomTypeName, currentPrice) {
            document.getElementById('update_room_type_id').value = roomTypeId;
            document.getElementById('update_room_type_name').textContent = roomTypeName;
            document.getElementById('update_new_price').value = currentPrice;
            document.getElementById('current_price_display').textContent = new Intl.NumberFormat('vi-VN').format(currentPrice);
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

        // Format number input
        document.getElementById('update_new_price').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });
    </script>
</body>
</html>

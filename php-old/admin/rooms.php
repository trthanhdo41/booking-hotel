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

$page_title = "Quản lý phòng - Admin";

// Lấy dữ liệu cần thiết trước khi xử lý form
$room_types = getAllRoomTypes();
$rooms = getAllRooms();
$hotels = getAllHotels();

// Xử lý thêm/sửa/xóa phòng
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $room_type_name = trim($_POST['room_type_name'] ?? '');
        $room_number = trim($_POST['room_number'] ?? '');
        $floor = (int)($_POST['floor'] ?? 1);
        $status = trim($_POST['status'] ?? 'available');
        
        if ($room_type_name && $room_number) {
            // Tìm hoặc tạo room_type_id
            $room_type_id = null;
            
            // Tìm room_type đã tồn tại
            for ($i = 1; $i < count($room_types); $i++) {
                $room_type = $room_types[$i];
                if (count($room_type) >= 3 && strtolower(trim($room_type[2])) === strtolower(trim($room_type_name))) {
                    $room_type_id = (int)$room_type[0];
                    break;
                }
            }
            
            // Nếu không tìm thấy, tạo room_type mới
            if (!$room_type_id) {
                $max_room_type_id = 0;
                for ($i = 1; $i < count($room_types); $i++) {
                    $room_type = $room_types[$i];
                    if (count($room_type) >= 1 && is_numeric($room_type[0])) {
                        $max_room_type_id = max($max_room_type_id, (int)$room_type[0]);
                    }
                }
                $room_type_id = $max_room_type_id + 1;
                
                // Tạo room_type mới (mặc định hotel_id = 1, giá = 1000000)
                $new_room_type = [[
                    $room_type_id,        // id (0)
                    1,                    // hotel_id (1)
                    $room_type_name,      // name (2)
                    'Mô tả ' . $room_type_name, // description (3)
                    1000000,              // price (4)
                    2,                    // max_guests (5)
                    '25m²',               // size (6)
                    ''                    // image_url (7)
                ]];
                
                if (writeSheetData('room_types', $new_room_type, 'A' . (count($room_types) + 1))) {
                    $message = 'Đã tạo loại phòng mới: ' . $room_type_name;
                    // Cập nhật lại dữ liệu room_types
                    $room_types = getAllRoomTypes();
                }
            }
            // Tạo ID mới
            $rooms = getAllRooms();
            $max_id = 0;
            for ($i = 1; $i < count($rooms); $i++) {
                $room = $rooms[$i];
                if (count($room) >= 1 && is_numeric($room[0])) {
                    $max_id = max($max_id, (int)$room[0]);
                }
            }
            $new_id = $max_id + 1;
            
            // Thêm phòng mới
            $values = [[
                $new_id,
                $room_type_id,
                $room_number,
                $floor,
                $status
            ]];
            
            if (writeSheetData('rooms', $values, 'A' . (count($rooms) + 1))) {
                $message = 'Thêm phòng thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi thêm phòng.';
            }
        } else {
            $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
        }
    }
    
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $room_type_name = trim($_POST['room_type_name'] ?? '');
        $room_number = trim($_POST['room_number'] ?? '');
        $floor = (int)($_POST['floor'] ?? 1);
        $status = trim($_POST['status'] ?? 'available');
        
        if ($id && $room_type_name && $room_number) {
            // Tìm hoặc tạo room_type_id
            $room_type_id = null;
            
            // Tìm room_type đã tồn tại
            for ($i = 1; $i < count($room_types); $i++) {
                $room_type = $room_types[$i];
                if (count($room_type) >= 3 && strtolower(trim($room_type[2])) === strtolower(trim($room_type_name))) {
                    $room_type_id = (int)$room_type[0];
                    break;
                }
            }
            
            // Nếu không tìm thấy, tạo room_type mới
            if (!$room_type_id) {
                $max_room_type_id = 0;
                for ($i = 1; $i < count($room_types); $i++) {
                    $room_type = $room_types[$i];
                    if (count($room_type) >= 1 && is_numeric($room_type[0])) {
                        $max_room_type_id = max($max_room_type_id, (int)$room_type[0]);
                    }
                }
                $room_type_id = $max_room_type_id + 1;
                
                // Tạo room_type mới (mặc định hotel_id = 1, giá = 1000000)
                $new_room_type = [[
                    $room_type_id,        // id (0)
                    1,                    // hotel_id (1)
                    $room_type_name,      // name (2)
                    'Mô tả ' . $room_type_name, // description (3)
                    1000000,              // price (4)
                    2,                    // max_guests (5)
                    '25m²',               // size (6)
                    ''                    // image_url (7)
                ]];
                
                if (writeSheetData('room_types', $new_room_type, 'A' . (count($room_types) + 1))) {
                    $message = 'Đã tạo loại phòng mới: ' . $room_type_name;
                    // Cập nhật lại dữ liệu room_types
                    $room_types = getAllRoomTypes();
                }
            }
            // Cập nhật phòng
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của phòng
            $rooms = getAllRooms();
            $row_index = -1;
            for ($i = 1; $i < count($rooms); $i++) {
                if (count($rooms[$i]) >= 1 && $rooms[$i][0] == $id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                $range = "rooms!A$row_index:E$row_index";
                $values = [[
                    $id, $room_type_id, $room_number, $floor, $status
                ]];
                $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
                $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);
                $message = 'Cập nhật phòng thành công!';
            } else {
                $error = 'Không tìm thấy phòng.';
            }
        } else {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        }
    }
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            // Xóa phòng
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của phòng
            $rooms = getAllRooms();
            $row_index = -1;
            for ($i = 1; $i < count($rooms); $i++) {
                if (count($rooms[$i]) >= 1 && $rooms[$i][0] == $id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                // Lấy sheet ID thực tế
                $sheet_id = getSheetId('rooms');
                
                // Xóa dòng
                $requests = [
                    new Google_Service_Sheets_Request([
                        'deleteDimension' => new Google_Service_Sheets_DeleteDimensionRequest([
                            'range' => new Google_Service_Sheets_DimensionRange([
                                'sheetId' => $sheet_id,
                                'dimension' => 'ROWS',
                                'startIndex' => $row_index - 1,
                                'endIndex' => $row_index
                            ])
                        ])
                    ])
                ];
                
                $batchRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(['requests' => $requests]);
                $service->spreadsheets->batchUpdate($spreadsheetId, $batchRequest);
                $message = 'Xóa phòng thành công!';
            } else {
                $error = 'Không tìm thấy phòng.';
            }
        }
    }
}

// Dữ liệu đã được lấy ở trên

// Tạo danh sách loại phòng cho select (cập nhật lại sau khi có thể đã thêm room_type mới)
$room_types_list = [];
for ($i = 1; $i < count($room_types); $i++) {
    $room_type = $room_types[$i];
    if (count($room_type) >= 3) {
        // Tìm tên khách sạn
        $hotel_name = 'Khách sạn không xác định';
        for ($j = 1; $j < count($hotels); $j++) {
            $hotel = $hotels[$j];
            if (count($hotel) >= 1 && $hotel[0] == $room_type[1]) {
                $hotel_name = $hotel[1];
                break;
            }
        }
        
        $room_types_list[] = [
            'id' => $room_type[0],
            'name' => $room_type[2],
            'hotel_name' => $hotel_name,
            'price' => $room_type[4] ?? 0
        ];
    }
}

// Tạo danh sách phòng với thông tin loại phòng
$rooms_data = [];
for ($i = 1; $i < count($rooms); $i++) {
    $room = $rooms[$i];
    if (count($room) >= 5) {
        // Tìm thông tin loại phòng
        $room_type_name = 'Loại phòng không xác định';
        $hotel_name = 'Khách sạn không xác định';
        $price = 0;
        
        foreach ($room_types_list as $room_type) {
            if ($room_type['id'] == $room[1]) {
                $room_type_name = $room_type['name'];
                $hotel_name = $room_type['hotel_name'];
                $price = $room_type['price'];
                break;
            }
        }
        
        $rooms_data[] = [
            'id' => $room[0],
            'room_type_id' => $room[1],
            'room_type_name' => $room_type_name,
            'hotel_name' => $hotel_name,
            'room_number' => $room[2],
            'floor' => $room[3],
            'status' => $room[4],
            'price' => $price
        ];
    }
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
                        <i class="fas fa-door-open text-2xl text-purple-600"></i>
                        <span class="text-xl font-bold text-gray-800">Quản lý phòng</span>
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Quản lý phòng</h1>
                <p class="text-gray-600">Thêm, sửa, xóa thông tin phòng</p>
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

            <!-- Add Room Form -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Thêm phòng mới</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng *</label>
                        <input type="text" name="room_type_name" required placeholder="VD: Phòng Deluxe, Suite, Standard..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Số phòng *</label>
                        <input type="text" name="room_number" required placeholder="VD: 101, A201, VIP01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tầng</label>
                        <input type="number" name="floor" min="1" max="50" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="available">Có sẵn</option>
                            <option value="occupied">Đã thuê</option>
                            <option value="maintenance">Bảo trì</option>
                            <option value="cleaning">Đang dọn dẹp</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Thêm phòng
                        </button>
                    </div>
                </form>
            </div>

            <!-- Rooms List -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Danh sách phòng (<?php echo count($rooms_data); ?>)</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Số phòng</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Loại phòng</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Khách sạn</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Tầng</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Trạng thái</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Giá</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rooms_data as $room): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-900"><?php echo $room['id']; ?></td>
                                <td class="py-3 px-4 text-gray-700 font-medium"><?php echo htmlspecialchars($room['room_number']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($room['room_type_name']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($room['hotel_name']); ?></td>
                                <td class="py-3 px-4 text-gray-700">
                                    <i class="fas fa-layer-group mr-1"></i><?php echo $room['floor']; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                        <?php 
                                        switch($room['status']) {
                                            case 'available': echo 'bg-green-100 text-green-800'; break;
                                            case 'occupied': echo 'bg-red-100 text-red-800'; break;
                                            case 'maintenance': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'cleaning': echo 'bg-blue-100 text-blue-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php 
                                        switch($room['status']) {
                                            case 'available': echo 'Có sẵn'; break;
                                            case 'occupied': echo 'Đã thuê'; break;
                                            case 'maintenance': echo 'Bảo trì'; break;
                                            case 'cleaning': echo 'Đang dọn dẹp'; break;
                                            default: echo ucfirst($room['status']);
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-700 font-semibold text-green-600">
                                    <?php echo number_format($room['price'], 0, ',', '.'); ?> VNĐ
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <button onclick="editRoom(<?php echo htmlspecialchars(json_encode($room)); ?>)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Sửa
                                        </button>
                                        <button onclick="deleteRoom(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['room_number']); ?>')" 
                                                class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash mr-1"></i>Xóa
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

    <!-- Edit Room Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Sửa phòng</h3>
                <form id="editForm" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Loại phòng *</label>
                            <input type="text" name="room_type_name" id="edit_room_type_name" required placeholder="VD: Phòng Deluxe, Suite, Standard..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Số phòng *</label>
                            <input type="text" name="room_number" id="edit_room_number" required placeholder="VD: 101, A201, VIP01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tầng</label>
                            <input type="number" name="floor" id="edit_floor" min="1" max="50" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                            <select name="status" id="edit_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="available">Có sẵn</option>
                                <option value="occupied">Đã thuê</option>
                                <option value="maintenance">Bảo trì</option>
                                <option value="cleaning">Đang dọn dẹp</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Hủy
                        </button>
                        <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa phòng "<span id="delete_room_number"></span>"?</p>
                
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                            Hủy
                        </button>
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Xóa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editRoom(room) {
            document.getElementById('edit_id').value = room.id;
            document.getElementById('edit_room_type_name').value = room.room_type_name;
            document.getElementById('edit_room_number').value = room.room_number;
            document.getElementById('edit_floor').value = room.floor;
            document.getElementById('edit_status').value = room.status;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteRoom(id, roomNumber) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_room_number').textContent = roomNumber;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>

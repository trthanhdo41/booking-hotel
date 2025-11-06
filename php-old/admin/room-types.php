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

$page_title = "Quản lý loại phòng - Admin";

// Xử lý thêm/sửa/xóa loại phòng
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $hotel_id = (int)($_POST['hotel_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (int)($_POST['price'] ?? 0);
        $size = trim($_POST['size'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $max_guests = (int)($_POST['max_guests'] ?? 2);
        $amenities = trim($_POST['amenities'] ?? '');
        
        if ($hotel_id && $name && $price > 0) {
            // Tạo ID mới
            $room_types = getAllRoomTypes();
            $max_id = 0;
            for ($i = 1; $i < count($room_types); $i++) {
                $room_type = $room_types[$i];
                if (count($room_type) >= 1 && is_numeric($room_type[0])) {
                    $max_id = max($max_id, (int)$room_type[0]);
                }
            }
            $new_id = $max_id + 1;
            
            // Thêm loại phòng mới
            $values = [[
                $new_id,
                $hotel_id,
                $name,
                $description,
                $price,
                $size,
                $image_url,
                $max_guests,
                $amenities
            ]];
            
            if (writeSheetData('room_types', $values, 'A' . (count($room_types) + 1))) {
                $message = 'Thêm loại phòng thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi thêm loại phòng.';
            }
        } else {
            $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
        }
    }
    
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $hotel_id = (int)($_POST['hotel_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (int)($_POST['price'] ?? 0);
        $size = trim($_POST['size'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $max_guests = (int)($_POST['max_guests'] ?? 2);
        $amenities = trim($_POST['amenities'] ?? '');
        
        if ($id && $hotel_id && $name && $price > 0) {
            // Cập nhật loại phòng
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của loại phòng
            $room_types = getAllRoomTypes();
            $row_index = -1;
            for ($i = 1; $i < count($room_types); $i++) {
                if (count($room_types[$i]) >= 1 && $room_types[$i][0] == $id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                $range = "room_types!A$row_index:I$row_index";
                $values = [[
                    $id, $hotel_id, $name, $description, $price, $size, $image_url, $max_guests, $amenities
                ]];
                $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
                $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);
                $message = 'Cập nhật loại phòng thành công!';
            } else {
                $error = 'Không tìm thấy loại phòng.';
            }
        } else {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        }
    }
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            // Xóa loại phòng
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của loại phòng
            $room_types = getAllRoomTypes();
            $row_index = -1;
            for ($i = 1; $i < count($room_types); $i++) {
                if (count($room_types[$i]) >= 1 && $room_types[$i][0] == $id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                // Lấy sheet ID thực tế
                $sheet_id = getSheetId('room_types');
                
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
                $message = 'Xóa loại phòng thành công!';
            } else {
                $error = 'Không tìm thấy loại phòng.';
            }
        }
    }
}

// Lấy danh sách khách sạn và loại phòng
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
            'description' => $room_type[3],
            'price' => $room_type[4],
            'size' => $room_type[5],
            'image_url' => $room_type[6],
            'max_guests' => $room_type[7],
            'amenities' => $room_type[8]
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
                        <i class="fas fa-bed text-2xl text-green-600"></i>
                        <span class="text-xl font-bold text-gray-800">Quản lý loại phòng</span>
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Quản lý loại phòng</h1>
                <p class="text-gray-600">Thêm, sửa, xóa thông tin loại phòng</p>
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

            <!-- Add Room Type Form -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Thêm loại phòng mới</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Khách sạn *</label>
                        <select name="hotel_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Chọn khách sạn</option>
                            <?php foreach ($hotels_list as $hotel): ?>
                            <option value="<?php echo $hotel['id']; ?>"><?php echo htmlspecialchars($hotel['name'] . ' - ' . $hotel['city']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên loại phòng *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Giá phòng (VNĐ) *</label>
                        <input type="number" name="price" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Diện tích</label>
                        <input type="text" name="size" placeholder="VD: 25m²" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Số khách tối đa</label>
                        <input type="number" name="max_guests" min="1" max="10" value="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL hình ảnh</label>
                        <input type="url" name="image_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tiện nghi</label>
                        <textarea name="amenities" rows="2" placeholder="VD: WiFi, TV, Điều hòa, Tủ lạnh..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="md:col-span-2">
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Thêm loại phòng
                        </button>
                    </div>
                </form>
            </div>

            <!-- Room Types List -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Danh sách loại phòng (<?php echo count($room_types_data); ?>)</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Tên</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Khách sạn</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Giá</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Diện tích</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Khách</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($room_types_data as $room_type): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-900"><?php echo $room_type['id']; ?></td>
                                <td class="py-3 px-4 text-gray-700 font-medium"><?php echo htmlspecialchars($room_type['name']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($room_type['hotel_name']); ?></td>
                                <td class="py-3 px-4 text-gray-700 font-semibold text-green-600">
                                    <?php echo number_format($room_type['price'], 0, ',', '.'); ?> VNĐ
                                </td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($room_type['size']); ?></td>
                                <td class="py-3 px-4 text-gray-700">
                                    <i class="fas fa-user mr-1"></i><?php echo $room_type['max_guests']; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <button onclick="editRoomType(<?php echo htmlspecialchars(json_encode($room_type)); ?>)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Sửa
                                        </button>
                                        <button onclick="deleteRoomType(<?php echo $room_type['id']; ?>, '<?php echo htmlspecialchars($room_type['name']); ?>')" 
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

    <!-- Edit Room Type Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Sửa loại phòng</h3>
                <form id="editForm" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Khách sạn *</label>
                            <select name="hotel_id" id="edit_hotel_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Chọn khách sạn</option>
                                <?php foreach ($hotels_list as $hotel): ?>
                                <option value="<?php echo $hotel['id']; ?>"><?php echo htmlspecialchars($hotel['name'] . ' - ' . $hotel['city']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tên loại phòng *</label>
                            <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Giá phòng (VNĐ) *</label>
                            <input type="number" name="price" id="edit_price" required min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Diện tích</label>
                            <input type="text" name="size" id="edit_size" placeholder="VD: 25m²" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Số khách tối đa</label>
                            <input type="number" name="max_guests" id="edit_max_guests" min="1" max="10" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL hình ảnh</label>
                            <input type="url" name="image_url" id="edit_image_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                            <textarea name="description" id="edit_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tiện nghi</label>
                            <textarea name="amenities" id="edit_amenities" rows="2" placeholder="VD: WiFi, TV, Điều hòa, Tủ lạnh..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa loại phòng "<span id="delete_room_type_name"></span>"?</p>
                
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
        function editRoomType(roomType) {
            document.getElementById('edit_id').value = roomType.id;
            document.getElementById('edit_hotel_id').value = roomType.hotel_id;
            document.getElementById('edit_name').value = roomType.name;
            document.getElementById('edit_description').value = roomType.description;
            document.getElementById('edit_price').value = roomType.price;
            document.getElementById('edit_size').value = roomType.size;
            document.getElementById('edit_image_url').value = roomType.image_url;
            document.getElementById('edit_max_guests').value = roomType.max_guests;
            document.getElementById('edit_amenities').value = roomType.amenities;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteRoomType(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_room_type_name').textContent = name;
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

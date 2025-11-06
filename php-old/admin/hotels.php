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

$page_title = "Quản lý khách sạn - Admin";

// Xử lý thêm/sửa/xóa khách sạn
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rating = (float)($_POST['rating'] ?? 0);
        $image_url = trim($_POST['image_url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($name && $address && $city) {
            // Tạo ID mới
            $hotels = getAllHotels();
            $max_id = 0;
            for ($i = 1; $i < count($hotels); $i++) {
                $hotel = $hotels[$i];
                if (count($hotel) >= 1 && is_numeric($hotel[0])) {
                    $max_id = max($max_id, (int)$hotel[0]);
                }
            }
            $new_id = $max_id + 1;
            
            // Thêm khách sạn mới
            $values = [[
                $new_id,
                $name,
                $address,
                $city,
                $phone,
                $email,
                $rating,
                $image_url,
                $description
            ]];
            
            if (writeSheetData('hotels', $values, 'A' . (count($hotels) + 1))) {
                $message = 'Thêm khách sạn thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi thêm khách sạn.';
            }
        } else {
            $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
        }
    }
    
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rating = (float)($_POST['rating'] ?? 0);
        $image_url = trim($_POST['image_url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($id && $name && $address && $city) {
            // Cập nhật khách sạn
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của khách sạn
            $hotels = getAllHotels();
            $row_index = -1;
            for ($i = 1; $i < count($hotels); $i++) {
                if (count($hotels[$i]) >= 1 && $hotels[$i][0] == $id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                $range = "hotels!A$row_index:I$row_index";
                $values = [[
                    $id, $name, $address, $city, $phone, $email, $rating, $image_url, $description
                ]];
                $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
                $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);
                $message = 'Cập nhật khách sạn thành công!';
            } else {
                $error = 'Không tìm thấy khách sạn.';
            }
        } else {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        }
    }
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            // Xóa khách sạn (thực tế nên soft delete)
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của khách sạn
            $hotels = getAllHotels();
            $row_index = -1;
            for ($i = 1; $i < count($hotels); $i++) {
                if (count($hotels[$i]) >= 1 && $hotels[$i][0] == $id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                // Lấy sheet ID thực tế
                $sheet_id = getSheetId('hotels');
                
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
                $message = 'Xóa khách sạn thành công!';
            } else {
                $error = 'Không tìm thấy khách sạn.';
            }
        }
    }
}

// Lấy danh sách khách sạn
$hotels = getAllHotels();
$hotels_data = [];
for ($i = 1; $i < count($hotels); $i++) {
    $hotel = $hotels[$i];
    if (count($hotel) >= 9) {
        $hotels_data[] = [
            'id' => $hotel[0],
            'name' => $hotel[1],
            'address' => $hotel[2],
            'city' => $hotel[3],
            'phone' => $hotel[4],
            'email' => $hotel[5],
            'rating' => $hotel[6],
            'image_url' => $hotel[7],
            'description' => $hotel[8]
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
                        <i class="fas fa-hotel text-2xl text-blue-600"></i>
                        <span class="text-xl font-bold text-gray-800">Quản lý khách sạn</span>
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Quản lý khách sạn</h1>
                <p class="text-gray-600">Thêm, sửa, xóa thông tin khách sạn</p>
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

            <!-- Add Hotel Form -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Thêm khách sạn mới</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="action" value="add">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên khách sạn *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Thành phố *</label>
                        <input type="text" name="city" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ *</label>
                        <input type="text" name="address" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                        <input type="tel" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Đánh giá (1-5)</label>
                        <input type="number" name="rating" min="1" max="5" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Thêm khách sạn
                        </button>
                    </div>
                </form>
            </div>

            <!-- Hotels List -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Danh sách khách sạn (<?php echo count($hotels_data); ?>)</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Tên</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Thành phố</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Địa chỉ</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Đánh giá</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hotels_data as $hotel): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-900"><?php echo $hotel['id']; ?></td>
                                <td class="py-3 px-4 text-gray-700 font-medium"><?php echo htmlspecialchars($hotel['name']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($hotel['city']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($hotel['address']); ?></td>
                                <td class="py-3 px-4 text-gray-700">
                                    <div class="flex items-center">
                                        <span class="text-yellow-500 mr-1">★</span>
                                        <?php echo $hotel['rating']; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <button onclick="editHotel(<?php echo htmlspecialchars(json_encode($hotel)); ?>)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Sửa
                                        </button>
                                        <button onclick="deleteHotel(<?php echo $hotel['id']; ?>, '<?php echo htmlspecialchars($hotel['name']); ?>')" 
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

    <!-- Edit Hotel Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Sửa khách sạn</h3>
                <form id="editForm" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tên khách sạn *</label>
                            <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Thành phố *</label>
                            <input type="text" name="city" id="edit_city" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ *</label>
                            <input type="text" name="address" id="edit_address" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Số điện thoại</label>
                            <input type="tel" name="phone" id="edit_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="edit_email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Đánh giá (1-5)</label>
                            <input type="number" name="rating" id="edit_rating" min="1" max="5" step="0.1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL hình ảnh</label>
                            <input type="url" name="image_url" id="edit_image_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                            <textarea name="description" id="edit_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Xác nhận xóa</h3>
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa khách sạn "<span id="delete_hotel_name"></span>"?</p>
                
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
        function editHotel(hotel) {
            document.getElementById('edit_id').value = hotel.id;
            document.getElementById('edit_name').value = hotel.name;
            document.getElementById('edit_city').value = hotel.city;
            document.getElementById('edit_address').value = hotel.address;
            document.getElementById('edit_phone').value = hotel.phone;
            document.getElementById('edit_email').value = hotel.email;
            document.getElementById('edit_rating').value = hotel.rating;
            document.getElementById('edit_image_url').value = hotel.image_url;
            document.getElementById('edit_description').value = hotel.description;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteHotel(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_hotel_name').textContent = name;
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

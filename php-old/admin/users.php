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

$page_title = "Quản lý người dùng - Admin";

// Xử lý thêm/sửa/xóa người dùng
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $status = trim($_POST['status'] ?? 'active');
        
        if ($id && $role && $status) {
            // Cập nhật chỉ vai trò và trạng thái
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của người dùng
            $users = getAllUsers();
            $row_index = -1;
            for ($i = 1; $i < count($users); $i++) {
                if (count($users[$i]) >= 1 && $users[$i][0] == $id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                // Lấy thông tin cũ của người dùng
                $old_user = $users[$row_index - 1];
                
                // Chỉ cập nhật vai trò và trạng thái, giữ nguyên các thông tin khác
                $update_data = [
                    $old_user[0], // id
                    $old_user[1], // username
                    $old_user[2], // email
                    $old_user[3], // password
                    $old_user[4], // full_name
                    $old_user[5], // phone
                    $role,        // role (cập nhật)
                    $status,      // status (cập nhật)
                    $old_user[8] ?? date('Y-m-d H:i:s'), // created_at
                    date('Y-m-d H:i:s') // updated_at
                ];
                
                $range = "users!A$row_index:J$row_index";
                $values = [$update_data];
                $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
                $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);
                $message = 'Cập nhật vai trò và trạng thái thành công!';
            } else {
                $error = 'Không tìm thấy người dùng.';
            }
        } else {
            $error = 'Vui lòng chọn vai trò và trạng thái.';
        }
    }
    
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            // Xóa người dùng
            $service = getGoogleSheetsClient();
            $spreadsheetId = '13XR0UtHao-e-XWU2rJEUexG_QjWzhlHROgCbv7TUUuo';
            
            // Tìm dòng của người dùng
            $users = getAllUsers();
            $row_index = -1;
            for ($i = 1; $i < count($users); $i++) {
                if (count($users[$i]) >= 1 && $users[$i][0] == $id) {
                    $row_index = $i + 1;
                    break;
                }
            }
            
            if ($row_index > 0) {
                // Lấy sheet ID thực tế
                $sheet_id = getSheetId('users');
                
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
                $message = 'Xóa người dùng thành công!';
            } else {
                $error = 'Không tìm thấy người dùng.';
            }
        }
    }
}

// Lấy danh sách người dùng
$users = getAllUsers();
$users_data = [];
for ($i = 1; $i < count($users); $i++) {
    $user = $users[$i];
    if (count($user) >= 10) {
        $users_data[] = [
            'id' => $user[0],
            'username' => $user[1],
            'email' => $user[3], // email ở cột 3 (index 3)
            'full_name' => $user[4], // full_name ở cột 4 (index 4)
            'phone' => $user[5], // phone ở cột 5 (index 5)
            'role' => $user[6], // role ở cột 6 (index 6)
            'status' => $user[7], // status ở cột 7 (index 7)
            'created_at' => $user[8], // created_at ở cột 8 (index 8)
            'updated_at' => $user[9] // updated_at ở cột 9 (index 9)
        ];
    }
}

// Sắp xếp theo ngày tạo mới nhất
usort($users_data, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
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
                        <i class="fas fa-users text-2xl text-purple-600"></i>
                        <span class="text-xl font-bold text-gray-800">Quản lý người dùng</span>
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
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Quản lý người dùng</h1>
                <p class="text-gray-600">Thêm, sửa, xóa thông tin người dùng</p>
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


            <!-- Users List -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Danh sách người dùng (<?php echo count($users_data); ?>)</h2>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Tên đăng nhập</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Email</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Họ tên</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Số điện thoại</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Vai trò</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Trạng thái</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Ngày tạo</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users_data as $user): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 text-gray-900"><?php echo $user['id']; ?></td>
                                <td class="py-3 px-4 text-gray-700 font-medium"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td class="py-3 px-4 text-gray-700"><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td class="py-3 px-4 text-gray-700">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                        <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'Quản trị viên' : 'Người dùng'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-700">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                        <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-700 text-sm">
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <button onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-edit mr-1"></i>Sửa
                                        </button>
                                        <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')" 
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

    <!-- Edit User Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Cập nhật người dùng</h3>
                <form id="editForm" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <!-- Thông tin người dùng (chỉ đọc) -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-800 mb-3">Thông tin người dùng</h4>
                        <div class="space-y-2 text-sm">
                            <div><span class="font-medium">Tên đăng nhập:</span> <span id="display_username" class="text-gray-600"></span></div>
                            <div><span class="font-medium">Email:</span> <span id="display_email" class="text-gray-600"></span></div>
                            <div><span class="font-medium">Họ tên:</span> <span id="display_full_name" class="text-gray-600"></span></div>
                            <div><span class="font-medium">Số điện thoại:</span> <span id="display_phone" class="text-gray-600"></span></div>
                        </div>
                    </div>
                    
                    <!-- Chỉ cho phép cập nhật vai trò và trạng thái -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Vai trò *</label>
                            <select name="role" id="edit_role" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="user">Người dùng</option>
                                <option value="admin">Quản trị viên</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái *</label>
                            <select name="status" id="edit_status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Không hoạt động</option>
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
                <p class="text-gray-600 mb-6">Bạn có chắc chắn muốn xóa người dùng "<span id="delete_username"></span>"?</p>
                
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
        function editUser(user) {
            document.getElementById('edit_id').value = user.id;
            
            // Hiển thị thông tin người dùng (chỉ đọc)
            document.getElementById('display_username').textContent = user.username;
            document.getElementById('display_email').textContent = user.email;
            document.getElementById('display_full_name').textContent = user.full_name;
            document.getElementById('display_phone').textContent = user.phone || 'Chưa cập nhật';
            
            // Chỉ cho phép cập nhật vai trò và trạng thái
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_status').value = user.status;
            
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteUser(id, username) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_username').textContent = username;
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

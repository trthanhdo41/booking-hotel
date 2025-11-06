# 🏨 HỆ THỐNG ĐẶT PHÒNG KHÁCH SẠN - BOOKING HOTEL

## 📋 TỔNG QUAN DỰ ÁN

Đây là hệ thống đặt phòng khách sạn hoàn chỉnh được xây dựng bằng PHP, sử dụng Google Sheets làm database và Tailwind CSS cho giao diện.

### 🎯 CHỨC NĂNG CHÍNH
- **Đăng ký/Đăng nhập** người dùng với phân quyền (User/Admin)
- **Tìm kiếm phòng** theo địa điểm và số khách
- **Đặt phòng** với thông tin chi tiết
- **Thanh toán** qua nhiều phương thức (Thẻ tín dụng, Chuyển khoản, Ví điện tử)
- **Quản lý admin** đầy đủ (Hotels, Rooms, Bookings, Users)
- **Gửi email** xác nhận tự động qua n8n webhook

---

## 🗂️ CẤU TRÚC THƯ MỤC

```
Booking Phòng Hotel/
├── 📁 admin/                    # Trang quản trị
│   ├── index.php               # Dashboard admin
│   ├── login.php               # Đăng nhập admin
│   ├── hotels.php              # Quản lý khách sạn
│   ├── rooms.php               # Quản lý phòng
│   ├── bookings.php            # Quản lý đặt phòng
│   ├── users.php               # Quản lý người dùng
│   └── room-types.php          # Quản lý loại phòng
├── 📁 config/                   # Cấu hình hệ thống
│   ├── database.php            # Kết nối Google Sheets
│   ├── email.php               # Gửi email qua n8n
│   └── google-credentials.json # API key Google
├── 📁 assets/                   # Tài nguyên tĩnh
│   ├── css/                    # Stylesheet
│   ├── js/                     # JavaScript
│   └── images/                 # Hình ảnh
├── 📄 index.php                 # Trang chủ
├── 📄 search.php                # Tìm kiếm phòng
├── 📄 booking.php               # Đặt phòng
├── 📄 payment.php               # Thanh toán
├── 📄 payment-success.php       # Thành công thanh toán
├── 📄 login.php                 # Đăng nhập user
├── 📄 register.php              # Đăng ký user
├── 📄 profile.php               # Thông tin cá nhân
├── 📄 booking-history.php       # Lịch sử đặt phòng
└── 📄 update-payment-status.php # API cập nhật thanh toán
```

---

## 🚀 HƯỚNG DẪN CÀI ĐẶT

### 1. YÊU CẦU HỆ THỐNG
- **PHP 7.4+** với các extension: curl, json, openssl
- **Web server** (Apache/Nginx) hoặc PHP built-in server
- **Google Cloud Console** account để tạo API key
- **n8n** account để gửi email (hoặc có thể dùng SMTP)

### 2. CÀI ĐẶT GOOGLE SHEETS API
1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo project mới hoặc chọn project có sẵn
3. Bật **Google Sheets API**
4. Tạo **Service Account** và download file JSON
5. Đặt file JSON vào `config/google-credentials.json`
6. Tạo Google Sheet với 5 bảng: `hotels`, `room_types`, `rooms`, `bookings`, `users`

### 3. CẤU HÌNH GOOGLE SHEET

#### Bảng `hotels` (Khách sạn):
```
id | name | address | city | phone | email | rating | image_url | description
```

#### Bảng `room_types` (Loại phòng):
```
id | hotel_id | name | description | price | max_guests | size | image_url
```

#### Bảng `rooms` (Phòng):
```
id | room_type_id | room_number | floor | status
```

#### Bảng `bookings` (Đặt phòng):
```
id | user_id | room_id | guest_name | guest_email | guest_phone | checkin_date | checkout_date | guests | total_price | status | created_at | payment_method | payment_id | notes
```

#### Bảng `users` (Người dùng):
```
id | username | email | password | full_name | phone | role | status | created_at
```

### 4. CẤU HÌNH EMAIL (n8n)
1. Đăng ký tài khoản [n8n](https://n8n.io/)
2. Tạo workflow với webhook trigger
3. Cập nhật URL webhook trong `config/email.php`:
```php
'webhook_url' => 'https://your-n8n-instance.com/webhook/booking_email'
```

### 5. CHẠY ỨNG DỤNG
```bash
# Sử dụng PHP built-in server
php -S localhost:8000

# Hoặc cấu hình Apache/Nginx
# Truy cập: http://localhost:8000
```

---

## 👥 HƯỚNG DẪN SỬ DỤNG

### 🔐 ĐĂNG NHẬP HỆ THỐNG

#### Tài khoản Admin (Mặc định):
- **Email**: admin@bookinghotel.com
- **Password**: admin123

#### Tài khoản User (Tạo mới):
- Truy cập `/register.php` để đăng ký
- Xác nhận email và đăng nhập

### 🏠 TRANG CHỦ (`index.php`)

**Chức năng:**
- Hiển thị giao diện trang chủ với các section:
  - **Hero Section**: Form tìm kiếm nhanh
  - **Popular Destinations**: Slider các điểm đến phổ biến
  - **Why Choose Us**: Lý do chọn dịch vụ
  - **Ready for Trip**: Call-to-action
  - **Statistics**: Số liệu thống kê

**Cách hoạt động:**
- Kiểm tra trạng thái đăng nhập
- Hiển thị nút "Admin" nếu là admin
- Form tìm kiếm chuyển hướng đến `search.php`

### 🔍 TÌM KIẾM PHÒNG (`search.php`)

**Chức năng:**
- Tìm kiếm phòng theo địa điểm
- Hiển thị danh sách phòng có sẵn
- Lọc theo giá, loại phòng, tiện ích

**Cách hoạt động:**
1. Kiểm tra đăng nhập (bắt buộc)
2. Nhận tham số tìm kiếm từ URL
3. Query Google Sheets để lấy dữ liệu
4. Hiển thị kết quả với pagination

**URL mẫu:**
```
/search.php?location=Hà Nội&guests=2
```

### 🏨 CHI TIẾT KHÁCH SẠN (`hotel-detail.php`)

**Chức năng:**
- Hiển thị thông tin chi tiết khách sạn
- Danh sách các loại phòng
- Hình ảnh và đánh giá
- Nút đặt phòng

### 📝 ĐẶT PHÒNG (`booking.php`)

**Chức năng:**
- Form đặt phòng với thông tin khách hàng
- Tính toán giá dựa trên số đêm
- Lưu booking vào Google Sheets
- Gửi email xác nhận

**Cách hoạt động:**
1. Nhận thông tin phòng từ URL
2. Validate dữ liệu đầu vào
3. Tính toán giá tiền
4. Lưu vào bảng `bookings`
5. Gửi email xác nhận qua n8n
6. Chuyển hướng đến trang thanh toán

**Tham số URL:**
```
/booking.php?room_id=123&checkin=2025-01-15&checkout=2025-01-17&guests=2
```

### 💳 THANH TOÁN (`payment.php`)

**Chức năng:**
- Hiển thị thông tin đặt phòng
- Chọn phương thức thanh toán
- Countdown timer 15 phút
- Xử lý thanh toán

**Phương thức thanh toán:**
- **Thẻ tín dụng/ghi nợ**: Form nhập thông tin thẻ
- **Chuyển khoản ngân hàng**: QR code
- **Ví điện tử**: QR code

**Cách hoạt động:**
1. Hiển thị thông tin booking
2. Người dùng chọn phương thức thanh toán
3. JavaScript xử lý form/QR code
4. Gọi API `update-payment-status.php`
5. Chuyển hướng đến trang thành công

### ✅ THÀNH CÔNG THANH TOÁN (`payment-success.php`)

**Chức năng:**
- Hiển thị hóa đơn thanh toán
- Gửi email xác nhận
- Thông báo thành công

**Cách hoạt động:**
1. Nhận thông tin từ URL
2. Cập nhật trạng thái booking
3. Lấy thông tin khách sạn/phòng
4. Gửi email xác nhận
5. Hiển thị hóa đơn

### 👤 QUẢN LÝ TÀI KHOẢN

#### Thông tin cá nhân (`profile.php`):
- Xem thông tin tài khoản
- Lịch sử đặt phòng gần đây
- Liên kết đến booking history

#### Lịch sử đặt phòng (`booking-history.php`):
- Danh sách tất cả đặt phòng
- Lọc theo trạng thái
- Chi tiết từng booking

---

## 🛠️ QUẢN TRỊ ADMIN

### 📊 DASHBOARD (`admin/index.php`)

**Chức năng:**
- Tổng quan hệ thống
- Thống kê số liệu
- Quick actions
- Biểu đồ trực quan

**Số liệu hiển thị:**
- Tổng số khách sạn
- Tổng số phòng
- Tổng số đặt phòng
- Tổng số người dùng
- Doanh thu tháng

### 🏨 QUẢN LÝ KHÁCH SẠN (`admin/hotels.php`)

**Chức năng:**
- Xem danh sách khách sạn
- Thêm/sửa/xóa khách sạn
- Upload hình ảnh
- Quản lý thông tin chi tiết

**Các trường thông tin:**
- Tên khách sạn
- Địa chỉ, thành phố
- Số điện thoại, email
- Đánh giá, mô tả
- Hình ảnh

### 🛏️ QUẢN LÝ PHÒNG (`admin/rooms.php`)

**Chức năng:**
- Quản lý loại phòng
- Quản lý phòng cụ thể
- Thiết lập giá cả
- Quản lý trạng thái

**Loại phòng:**
- Tên loại phòng
- Mô tả, giá
- Số khách tối đa
- Kích thước, hình ảnh

**Phòng cụ thể:**
- Số phòng, tầng
- Trạng thái (Available/Occupied)
- Liên kết với loại phòng

### 📋 QUẢN LÝ ĐẶT PHÒNG (`admin/bookings.php`)

**Chức năng:**
- Xem tất cả đặt phòng
- Lọc theo trạng thái
- Cập nhật trạng thái
- Xem chi tiết booking

**Trạng thái booking:**
- **Pending**: Chờ thanh toán
- **Confirmed**: Đã xác nhận
- **Completed**: Hoàn thành
- **Cancelled**: Đã hủy

### 👥 QUẢN LÝ NGƯỜI DÙNG (`admin/users.php`)

**Chức năng:**
- Xem danh sách người dùng
- Cập nhật vai trò (User/Admin)
- Cập nhật trạng thái (Active/Inactive)
- Xóa người dùng

**Vai trò:**
- **User**: Người dùng thường
- **Admin**: Quản trị viên

---

## 🔧 CẤU HÌNH KỸ THUẬT

### 📊 DATABASE (Google Sheets)

**Kết nối:**
- Sử dụng Google Sheets API v4
- Service Account authentication
- Batch operations cho hiệu suất

**Các hàm chính trong `config/database.php`:**
```php
getAllHotels()           // Lấy tất cả khách sạn
getAllRoomTypes()        // Lấy tất cả loại phòng
getAllRooms()            // Lấy tất cả phòng
getAllBookings()         // Lấy tất cả đặt phòng
getAllUsers()            // Lấy tất cả người dùng
addBooking($data)        // Thêm đặt phòng mới
updateBookingRow($row, $data) // Cập nhật đặt phòng
getBookingsByUserId($id) // Lấy đặt phòng theo user
```

### 📧 EMAIL SYSTEM (n8n)

**Cấu hình trong `config/email.php`:**
```php
$n8n_config = [
    'webhook_url' => 'https://your-n8n-instance.com/webhook/booking_email'
];
```

**Các loại email:**
- **Booking Confirmation**: Xác nhận đặt phòng (không gửi)
- **Payment Confirmation**: Xác nhận thanh toán (gửi)

**Format email:**
- Text format (không HTML)
- Thông tin hóa đơn chi tiết
- Gửi qua n8n webhook

### 🔐 AUTHENTICATION

**Session Management:**
- `$_SESSION['user_id']`: ID người dùng
- `$_SESSION['username']`: Tên đăng nhập
- `$_SESSION['role']`: Vai trò (user/admin)

**Password Security:**
- Hash bằng `password_hash()`
- Verify bằng `password_verify()`
- Salt tự động

**Access Control:**
- Kiểm tra đăng nhập cho các trang bảo mật
- Phân quyền admin/user
- Redirect đến login nếu chưa đăng nhập

### 🎨 FRONTEND

**CSS Framework:**
- **Tailwind CSS**: Utility-first CSS
- **Font Awesome**: Icons
- **AOS**: Animate On Scroll

**JavaScript Features:**
- Form validation
- AJAX requests
- Toast notifications
- Counter animations
- Mobile menu toggle

**Responsive Design:**
- Mobile-first approach
- Breakpoints: sm, md, lg, xl
- Touch-friendly interface

---

## 🚨 XỬ LÝ LỖI VÀ DEBUG

### Lỗi thường gặp:

#### 1. **"Khách sạn không xác định"**
**Nguyên nhân:** Logic tìm khách sạn sai
**Giải pháp:** Kiểm tra cấu trúc bảng `room_types` và `hotels`

#### 2. **"Failed to open stream: HTTP request failed"**
**Nguyên nhân:** n8n webhook không hoạt động
**Giải pháp:** Kiểm tra URL webhook và n8n workflow

#### 3. **"Cannot redeclare function"**
**Nguyên nhân:** Include file nhiều lần
**Giải pháp:** Sử dụng `include_once` thay vì `include`

#### 4. **"Column shifting" trong Google Sheets**
**Nguyên nhân:** Thêm/xóa cột không đúng thứ tự
**Giải pháp:** Sử dụng robust column mapping

### Debug Tools:

#### 1. **Error Logging:**
```php
error_log("Debug message: " . $variable);
```

#### 2. **Test Scripts:**
```php
// Test database connection
php test-database.php

// Test email sending
php test-email.php
```

#### 3. **Browser Developer Tools:**
- Console để xem JavaScript errors
- Network tab để kiểm tra AJAX requests
- Application tab để xem session storage

---

## 📱 DEMO VÀ THUYẾT TRÌNH

### 🎯 Điểm nhấn khi demo:

#### 1. **Giao diện đẹp:**
- Responsive design
- Animations mượt mà
- UI/UX hiện đại

#### 2. **Tính năng đầy đủ:**
- Đăng ký/đăng nhập
- Tìm kiếm thông minh
- Đặt phòng dễ dàng
- Thanh toán đa dạng

#### 3. **Quản trị mạnh:**
- Dashboard trực quan
- CRUD operations
- Thống kê chi tiết

#### 4. **Tích hợp tốt:**
- Google Sheets database
- n8n email automation
- Real-time updates

### 🗣️ Câu hỏi thường gặp:

#### **Q: Tại sao dùng Google Sheets thay vì MySQL?**
**A:** 
- Dễ setup và quản lý
- Không cần cài đặt database server
- Có thể xem dữ liệu trực tiếp
- Phù hợp cho demo và prototype

#### **Q: Làm sao đảm bảo bảo mật?**
**A:**
- Password được hash
- Session management
- Input validation
- SQL injection protection (Google Sheets API)

#### **Q: Hệ thống có thể scale không?**
**A:**
- Google Sheets có giới hạn 10M cells
- Có thể migrate sang MySQL/PostgreSQL
- Code được thiết kế modular

#### **Q: Làm sao backup dữ liệu?**
**A:**
- Google Sheets tự động backup
- Export CSV/Excel
- API để export dữ liệu

---

## 🔮 PHÁT TRIỂN TƯƠNG LAI

### Tính năng có thể thêm:

#### 1. **Real-time Features:**
- WebSocket cho thông báo real-time
- Live chat support
- Real-time booking updates

#### 2. **Advanced Search:**
- Filter theo giá, rating, amenities
- Map integration
- AI recommendations

#### 3. **Payment Gateway:**
- Tích hợp VNPay, MoMo
- Stripe, PayPal
- Cryptocurrency

#### 4. **Mobile App:**
- React Native app
- Push notifications
- Offline support

#### 5. **Analytics:**
- Google Analytics
- Custom dashboard
- Business intelligence

---

## 📞 HỖ TRỢ VÀ LIÊN HỆ

### 🆘 Khi gặp vấn đề:

1. **Kiểm tra error logs** trong browser console
2. **Xem PHP error logs** trên server
3. **Test từng component** riêng biệt
4. **Kiểm tra Google Sheets** permissions
5. **Verify n8n workflow** hoạt động

### 📧 Thông tin liên hệ:
- **Email**: info@bookinghotel.com
- **Hotline**: 1900-1234
- **Website**: www.bookinghotel.com

---

## 📄 LICENSE

Dự án này được phát triển cho mục đích học tập và demo. 
Mọi quyền được bảo lưu.

---

**🎉 Chúc bạn demo thành công và đạt điểm cao! 🎉**

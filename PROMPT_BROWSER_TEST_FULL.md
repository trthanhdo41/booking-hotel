# 🧪 PROMPT TEST TỰ ĐỘNG TOÀN BỘ HỆ THỐNG BOOKING HOTEL

## Mục đích
Test tự động 100% chức năng của hệ thống Hotel Booking từ PHP sang React, đảm bảo không bỏ sót tính năng nào và xác minh các tính năng bổ sung hoạt động đúng.

## URL cần test
- Frontend: http://localhost:3000
- Backend API: http://localhost:5000/api

---

## 📋 CHECKLIST TEST TOÀN DIỆN

### PHẦN 1: KIỂM TRA GIAO DIỆN & UI/UX (Visual Testing)

#### 1.1. Header Component
- [ ] **Kiểm tra Header trên Homepage:**
  - Logo "Booking.com" hiển thị đúng, có icon hotel
  - Background màu xanh (#003B95) trên homepage
  - Các button: "Đăng chỗ nghỉ của bạn", "VND", cờ VN, "?", "Đăng ký", "Đăng nhập" hiển thị đúng
  - Hover effects hoạt động mượt mà
  - Click vào logo → navigate về homepage

- [ ] **Kiểm tra Header trên các trang khác:**
  - Background trắng (không phải homepage)
  - Tất cả elements vẫn hiển thị đúng
  - User menu dropdown hoạt động khi đã đăng nhập

- [ ] **Kiểm tra Mobile Menu:**
  - Click vào hamburger menu (mobile)
  - Menu dropdown hiển thị đầy đủ options
  - Các links hoạt động đúng

#### 1.2. Footer Component
- [ ] **Kiểm tra Footer:**
  - 5 cột links hiển thị: Hỗ trợ, Khám phá, Điều khoản, Đối tác, Về chúng tôi
  - Tất cả links có text tiếng Việt
  - Bottom bar có copyright và social media icons
  - Styling giống Booking.com (màu trắng, links xanh)

#### 1.3. Homepage Layout
- [ ] **Kiểm tra Search Form:**
  - Form tìm kiếm hiển thị đúng vị trí
  - Các fields: "Bạn muốn đến đâu?", "Ngày nhận phòng", "Ngày trả phòng", "Khách"
  - Date picker hoạt động
  - Guests dropdown hoạt động
  - Button "Tìm kiếm" có icon và text đúng

- [ ] **Kiểm tra "Tại sao chọn Booking.com?" section:**
  - 4 cards hiển thị với icons từ Booking.com
  - Text tiếng Việt đúng
  - Hover effects hoạt động

- [ ] **Kiểm tra "Ưu đãi" section:**
  - Banner "Ưu đãi Phút chót" hiển thị
  - Button "Tìm ưu đãi" hoạt động
  - Background image hiển thị

- [ ] **Kiểm tra "Điểm đến xu hướng" section:**
  - Large card đầu tiên hiển thị với cờ Việt Nam
  - 4 cards nhỏ phía sau hiển thị
  - Tất cả có cờ Việt Nam (ảnh, không phải emoji)
  - Click vào bất kỳ card nào → navigate đến search page với city filter
  - Số lượng "chỗ nghỉ" hiển thị từ database

- [ ] **Kiểm tra "Tìm kiếm theo loại chỗ nghỉ" section:**
  - 4 cards: Khách sạn, Căn hộ, Resort, Biệt thự
  - Hover effects hoạt động
  - Images hiển thị đúng

- [ ] **Kiểm tra "Khám phá Việt Nam" section:**
  - Grid 6 cột hiển thị các thành phố
  - Mỗi city có ảnh và số lượng chỗ nghỉ
  - Click vào city → navigate đến search page
  - Tất cả cities lấy từ database (không mockup)

- [ ] **Kiểm tra "Ưu đãi cuối tuần" section:**
  - 4 room cards hiển thị
  - Mỗi card có: ảnh, tên phòng, giá, rating
  - Click vào card → navigate đến search page
  - Tất cả data từ database

- [ ] **Kiểm tra "Ở tại các chỗ nghỉ độc đáo nhất" section:**
  - 4 room cards hiển thị
  - Có rating, location, số đánh giá
  - Click vào card → navigate đến search page

- [ ] **Kiểm tra "Du lịch nhiều hơn, chi tiêu ít hơn" section:**
  - Card "Đăng nhập, tiết kiệm tiền" hiển thị
  - 2 buttons: "Đăng nhập" và "Đăng ký"
  - Illustration "Genius" hiển thị

- [ ] **Kiểm tra "Muốn cảm thấy thoải mái như ở nhà" section:**
  - Banner với text và button
  - Illustration hiển thị

- [ ] **Kiểm tra Animations & Hover Effects:**
  - Tất cả cards có hover: scale, shadow
  - Images có hover: scale-110
  - Buttons có hover effects
  - Transitions mượt mà

---

### PHẦN 2: AUTHENTICATION & AUTHORIZATION

#### 2.1. User Registration
- [ ] **Test đăng ký user mới:**
  - Navigate đến /register
  - Form có: username, email, password, full_name, phone
  - Điền đầy đủ thông tin hợp lệ
  - Submit form
  - Kiểm tra success message
  - Tự động redirect đến login hoặc homepage
  - Kiểm tra user đã được tạo trong database

- [ ] **Test validation:**
  - Submit form trống → hiển thị error messages
  - Email không hợp lệ → error
  - Password quá ngắn → error
  - Email đã tồn tại → error

#### 2.2. User Login
- [ ] **Test đăng nhập:**
  - Navigate đến /login
  - Điền email và password đúng
  - Submit form
  - Kiểm tra success message
  - Token được lưu trong localStorage
  - Redirect đến homepage hoặc trang trước đó
  - Header hiển thị user menu với tên user

- [ ] **Test login sai:**
  - Email hoặc password sai → error message
  - Form không submit được

- [ ] **Test "Remember me" (nếu có):**
  - Check remember me
  - Token được lưu lâu hơn

#### 2.3. User Logout
- [ ] **Test đăng xuất:**
  - Click vào user menu
  - Click "Đăng xuất"
  - Token bị xóa khỏi localStorage
  - Redirect về homepage
  - Header hiển thị "Đăng ký" và "Đăng nhập"

#### 2.4. Protected Routes
- [ ] **Test routes yêu cầu authentication:**
  - Chưa đăng nhập → truy cập /my-bookings → redirect đến /login
  - Chưa đăng nhập → truy cập /booking → redirect đến /login
  - Đã đăng nhập → truy cập các routes → OK

- [ ] **Test admin routes:**
  - User thường → truy cập /admin → redirect hoặc 403
  - Admin → truy cập /admin → OK

---

### PHẦN 3: HOTEL & ROOM MANAGEMENT

#### 3.1. Search & Filter Hotels
- [ ] **Test search từ homepage:**
  - Điền city, checkin, checkout, guests
  - Click "Tìm kiếm"
  - Navigate đến /search với params đúng
  - Kết quả hiển thị rooms từ city đó

- [ ] **Test search page:**
  - URL: /search?city=Ha%20Noi&checkin=2024-12-01&checkout=2024-12-03&guests=2
  - Danh sách rooms hiển thị đúng
  - Mỗi room card có: ảnh, tên, giá, rating, location
  - Click vào room card → navigate đến booking page

- [ ] **Test filters:**
  - Filter theo price range → kết quả cập nhật
  - Filter theo max guests → kết quả cập nhật
  - Filter theo city → kết quả cập nhật

- [ ] **Test click từ homepage sections:**
  - Click "Điểm đến xu hướng" → search với city đó
  - Click "Khám phá Việt Nam" → search với city đó
  - Click room cards → search với city của room đó

#### 3.2. Room Details
- [ ] **Test xem chi tiết room:**
  - Click vào room card
  - Navigate đến booking page với room_id
  - Hiển thị: ảnh, tên, mô tả, amenities, giá, hotel info
  - Form booking hiển thị với checkin/checkout đã điền sẵn

#### 3.3. Admin - Hotel Management
- [ ] **Test admin login:**
  - Login với admin account (admin@bookinghotel.com / admin123)
  - Navigate đến /admin
  - Dashboard hiển thị stats

- [ ] **Test CRUD Hotels:**
  - **CREATE:**
    - Click "Thêm khách sạn"
    - Form có: name, address, city, phone, email, rating, description, location, images
    - Điền đầy đủ thông tin
    - Submit → hotel được tạo
    - Kiểm tra hotel xuất hiện trong danh sách
    - Kiểm tra trong database

  - **READ:**
    - Danh sách hotels hiển thị đầy đủ
    - Mỗi hotel có: name, city, rating, actions
    - Pagination hoạt động (nếu có)

  - **UPDATE:**
    - Click "Sửa" trên một hotel
    - Form pre-fill với data hiện tại
    - Sửa thông tin
    - Submit → hotel được cập nhật
    - Kiểm tra trong database

  - **DELETE:**
    - Click "Xóa" trên một hotel
    - Confirm dialog hiển thị
    - Confirm → hotel bị xóa
    - Kiểm tra trong database
    - Room types của hotel cũng bị xóa (CASCADE)

#### 3.4. Admin - Room Types Management
- [ ] **Test CRUD Room Types:**
  - **CREATE:**
    - Click "Thêm loại phòng"
    - Form có: hotel_id, name, description, price, max_guests, size, amenities (JSON), images (JSON)
    - Chọn hotel từ dropdown
    - Điền đầy đủ thông tin
    - Submit → room type được tạo
    - Kiểm tra trong database

  - **READ:**
    - Danh sách room types hiển thị
    - Filter theo hotel → kết quả cập nhật
    - Mỗi room type hiển thị: name, hotel, price, max_guests

  - **UPDATE:**
    - Click "Sửa" trên một room type
    - Form pre-fill
    - Sửa thông tin (ví dụ: tăng giá)
    - Submit → room type được cập nhật
    - Kiểm tra trong database

  - **DELETE:**
    - Click "Xóa" trên một room type
    - Confirm → room type bị xóa
    - Kiểm tra trong database
    - Rooms của room type cũng bị xóa (CASCADE)

---

### PHẦN 4: BOOKING SYSTEM

#### 4.1. Create Booking
- [ ] **Test tạo booking:**
  - Đăng nhập với user account
  - Navigate đến booking page với room_type_id
  - Form hiển thị: guest_name, guest_email, guest_phone, checkin, checkout, guests
  - Pre-fill checkin/checkout từ URL params
  - Điền đầy đủ thông tin
  - Hiển thị total price (tự động tính)
  - Click "Đặt phòng"
  - Success message hiển thị
  - Redirect đến /my-bookings
  - Kiểm tra booking trong database

- [ ] **Test validation:**
  - Submit form trống → errors
  - Checkout < checkin → error
  - Guests > max_guests → error
  - Dates trong quá khứ → error

- [ ] **Test price calculation:**
  - Chọn checkin và checkout
  - Số ngày được tính đúng
  - Total = price_per_night * số ngày
  - Hiển thị đúng format VND

#### 4.2. View My Bookings
- [ ] **Test xem bookings của user:**
  - Navigate đến /my-bookings
  - Danh sách bookings của user hiển thị
  - Mỗi booking có: room name, hotel, dates, total price, status
  - Status hiển thị đúng (pending, confirmed, completed, cancelled)
  - Click vào booking → xem chi tiết (nếu có)

- [ ] **Test filter bookings:**
  - Filter theo status → kết quả cập nhật
  - Filter theo date range → kết quả cập nhật

#### 4.3. Cancel Booking
- [ ] **Test hủy booking:**
  - Trong /my-bookings
  - Click "Hủy" trên một booking (status = pending)
  - Confirm dialog
  - Confirm → booking status = cancelled
  - Kiểm tra trong database

#### 4.4. Admin - Booking Management
- [ ] **Test admin xem tất cả bookings:**
  - Admin login
  - Navigate đến /admin/bookings
  - Danh sách TẤT CẢ bookings hiển thị (không chỉ của admin)
  - Filter theo status, user, date

- [ ] **Test admin update booking status:**
  - Click "Sửa" trên một booking
  - Thay đổi status (pending → confirmed)
  - Submit → booking được cập nhật
  - Kiểm tra trong database
  - User nhận notification (nếu có)

---

### PHẦN 5: USER MANAGEMENT (Admin)

#### 5.1. View All Users
- [ ] **Test admin xem users:**
  - Admin login
  - Navigate đến /admin/users
  - Danh sách tất cả users hiển thị
  - Mỗi user có: username, email, full_name, role, status

#### 5.2. Update User
- [ ] **Test admin sửa user:**
  - Click "Sửa" trên một user
  - Thay đổi role (user → admin) hoặc status (active → inactive)
  - Submit → user được cập nhật
  - Kiểm tra trong database
  - User bị inactive → không thể login

#### 5.3. Delete User
- [ ] **Test admin xóa user:**
  - Click "Xóa" trên một user
  - Confirm dialog
  - Confirm → user bị xóa
  - Kiểm tra trong database
  - Bookings của user cũng bị xóa (CASCADE)

---

### PHẦN 6: DASHBOARD & STATISTICS

#### 6.1. Admin Dashboard
- [ ] **Test admin dashboard:**
  - Admin login
  - Navigate đến /admin
  - Dashboard hiển thị:
    - Total hotels
    - Total room types
    - Total bookings
    - Total users
    - Total revenue
    - Recent bookings
  - Tất cả số liệu lấy từ database (không hardcode)
  - Charts/graphs hiển thị (nếu có)

#### 6.2. Stats API
- [ ] **Test stats endpoint:**
  - GET /api/stats
  - Response có đầy đủ: hotels, room_types, bookings, users, revenue
  - Số liệu chính xác

---

### PHẦN 7: API ENDPOINTS TESTING

#### 7.1. Auth Endpoints
- [ ] POST /api/auth/register → 201 Created
- [ ] POST /api/auth/login → 200 OK, có token
- [ ] POST /api/auth/login (sai credentials) → 401 Unauthorized

#### 7.2. Hotels Endpoints
- [ ] GET /api/hotels → 200 OK, list hotels
- [ ] GET /api/hotels?city=Ha%20Noi → filter by city
- [ ] GET /api/hotels/cities → list cities with hotel counts
- [ ] GET /api/hotels/:id → 200 OK, hotel details
- [ ] POST /api/hotels (admin) → 201 Created
- [ ] PUT /api/hotels/:id (admin) → 200 OK
- [ ] DELETE /api/hotels/:id (admin) → 200 OK

#### 7.3. Room Types Endpoints
- [ ] GET /api/room-types → 200 OK, list room types
- [ ] GET /api/room-types?city=Ha%20Noi → filter by city
- [ ] GET /api/room-types?max_guests=3 → filter by guests
- [ ] GET /api/room-types?min_price=500000&max_price=1000000 → filter by price
- [ ] GET /api/room-types/:id → 200 OK, room type details (có hotel_city)
- [ ] POST /api/room-types (admin) → 201 Created
- [ ] PUT /api/room-types/:id (admin) → 200 OK
- [ ] DELETE /api/room-types/:id (admin) → 200 OK

#### 7.4. Bookings Endpoints
- [ ] GET /api/bookings (user) → chỉ bookings của user
- [ ] GET /api/bookings (admin) → tất cả bookings
- [ ] POST /api/bookings (user) → 201 Created
- [ ] PUT /api/bookings/:id/status (admin) → 200 OK
- [ ] DELETE /api/bookings/:id (user) → 200 OK

#### 7.5. Users Endpoints
- [ ] GET /api/users (admin) → list users
- [ ] GET /api/users/:id (admin) → user details
- [ ] PUT /api/users/:id (admin) → 200 OK
- [ ] DELETE /api/users/:id (admin) → 200 OK

#### 7.6. Stats Endpoint
- [ ] GET /api/stats (admin) → 200 OK, stats data

---

### PHẦN 8: DATABASE INTEGRITY

#### 8.1. Data Consistency
- [ ] **Kiểm tra foreign keys:**
  - Xóa hotel → room_types bị xóa (CASCADE)
  - Xóa room_type → rooms bị xóa (CASCADE)
  - Xóa user → bookings bị xóa (CASCADE)
  - Xóa room_type → bookings bị xóa (CASCADE)

#### 8.2. Data Validation
- [ ] **Kiểm tra constraints:**
  - Email unique → không thể tạo 2 users cùng email
  - Username unique → không thể tạo 2 users cùng username
  - Rating 0-5 → validation
  - Price > 0 → validation
  - Dates hợp lệ → validation

#### 8.3. Seed Data
- [ ] **Kiểm tra seed data:**
  - Chạy npm run seed
  - Database có: 34 hotels, 136 room types, 1055 rooms
  - Tất cả data hợp lệ
  - Images URLs hợp lệ
  - JSON fields (amenities, images) parse được

---

### PHẦN 9: RESPONSIVE DESIGN

#### 9.1. Mobile View (< 768px)
- [ ] **Homepage:**
  - Header có mobile menu
  - Search form responsive
  - Grid layouts chuyển sang 1 cột
  - Images scale đúng
  - Buttons đủ lớn để click

- [ ] **Search page:**
  - Room cards stack vertically
  - Filters collapse thành dropdown
  - Images responsive

- [ ] **Admin pages:**
  - Tables scroll horizontal
  - Forms stack vertically
  - Buttons đủ lớn

#### 9.2. Tablet View (768px - 1024px)
- [ ] Grid layouts: 2 cột
- [ ] Forms: 2 cột
- [ ] Images scale đúng

#### 9.3. Desktop View (> 1024px)
- [ ] Grid layouts: 4-6 cột
- [ ] Forms: multi-column
- [ ] Sidebar hiển thị (nếu có)

---

### PHẦN 10: ERROR HANDLING & EDGE CASES

#### 10.1. Network Errors
- [ ] **API errors:**
  - Disconnect internet → error message hiển thị
  - API timeout → error message
  - 404 error → error message
  - 500 error → error message

#### 10.2. Form Validation
- [ ] **Edge cases:**
  - Submit form khi đang loading → disable button
  - XSS attempts → sanitized
  - SQL injection attempts → prevented
  - Very long text → truncated hoặc error

#### 10.3. Empty States
- [ ] **No data:**
  - No hotels → message "Chưa có khách sạn"
  - No rooms → message "Không tìm thấy phòng"
  - No bookings → message "Bạn chưa có đặt phòng nào"

#### 10.4. Loading States
- [ ] **Loading indicators:**
  - API calls → spinner hiển thị
  - Form submit → button disabled + loading
  - Page load → skeleton screens (nếu có)

---

### PHẦN 11: PERFORMANCE & OPTIMIZATION

#### 11.1. Page Load
- [ ] Homepage load < 3s
- [ ] Search page load < 2s
- [ ] Admin pages load < 2s

#### 11.2. API Response Time
- [ ] GET /api/hotels < 500ms
- [ ] GET /api/room-types < 500ms
- [ ] GET /api/bookings < 500ms

#### 11.3. Image Loading
- [ ] Images lazy load
- [ ] Placeholder hiển thị khi loading
- [ ] Error fallback khi image fail

---

### PHẦN 12: SECURITY

#### 12.1. Authentication
- [ ] JWT token được lưu an toàn
- [ ] Token expire sau 7 days
- [ ] Refresh token (nếu có)

#### 12.2. Authorization
- [ ] User không thể access admin routes
- [ ] User chỉ xem được bookings của mình
- [ ] Admin mới có thể CRUD hotels/rooms

#### 12.3. Input Sanitization
- [ ] XSS prevention
- [ ] SQL injection prevention
- [ ] CSRF protection (nếu có)

---

## 🎯 CÁCH SỬ DỤNG PROMPT NÀY

1. **Mở Cursor trên Mac**
2. **Đảm bảo browser extension đã được cài đặt và kích hoạt**
3. **Copy toàn bộ nội dung file này**
4. **Paste vào Cursor chat với lệnh:**

```
Sử dụng browser extension để test tự động toàn bộ hệ thống theo checklist này. 
Bắt đầu từ PHẦN 1 và test tuần tự từng phần. 
Với mỗi test case:
- Chụp screenshot nếu có lỗi
- Ghi lại kết quả (PASS/FAIL)
- Nếu FAIL, mô tả chi tiết lỗi
- Tiếp tục test các case khác

Đảm bảo:
- Test trên http://localhost:3000
- Backend đang chạy trên http://localhost:5000
- Database đã được seed với dữ liệu mẫu
- Đã có ít nhất 1 admin account và 1 user account để test

Bắt đầu test ngay bây giờ!
```

5. **Cursor sẽ tự động:**
   - Mở browser
   - Navigate đến các pages
   - Click vào các elements
   - Fill forms
   - Verify results
   - Chụp screenshots khi cần
   - Tạo báo cáo test results

---

## 📊 BÁO CÁO TEST RESULTS

Sau khi test xong, yêu cầu Cursor tạo báo cáo với format:

```
# TEST RESULTS REPORT

## Tổng quan
- Tổng số test cases: XXX
- Passed: XXX
- Failed: XXX
- Pass rate: XX%

## Chi tiết Failed Tests
1. [Test case name]
   - Expected: ...
   - Actual: ...
   - Screenshot: [link]
   - Fix needed: ...

## Recommendations
- [List các vấn đề cần fix]
- [List các tính năng còn thiếu]
- [List các improvements]
```

---

## ✅ CRITERIA HOÀN THÀNH 100%

Dự án được coi là hoàn thành 100% khi:

1. ✅ Tất cả test cases PASS
2. ✅ Không có lỗi console
3. ✅ Không có lỗi network
4. ✅ Tất cả CRUD operations hoạt động
5. ✅ Authentication & Authorization đúng
6. ✅ Database integrity đảm bảo
7. ✅ Responsive design hoạt động
8. ✅ Performance đạt yêu cầu
9. ✅ Security đảm bảo
10. ✅ UI/UX giống Booking.com 100%

---

## 🔄 SO SÁNH VỚI PHP VERSION

Sau khi test xong, so sánh với PHP version:

- [ ] Tất cả tính năng từ PHP đã được implement
- [ ] UI/UX giống hoặc tốt hơn PHP version
- [ ] Performance tốt hơn PHP version
- [ ] Code structure tốt hơn (React components)
- [ ] Có thêm tính năng mới (nếu có)

---

**Lưu ý:** Prompt này được thiết kế để test toàn diện. Có thể mất 30-60 phút để chạy hết tất cả test cases. Hãy đảm bảo hệ thống đã sẵn sàng trước khi bắt đầu.


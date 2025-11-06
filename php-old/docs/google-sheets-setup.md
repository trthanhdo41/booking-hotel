# 📊 Hướng dẫn thiết lập Google Sheets Database

## 🔧 **Bước 1: Tạo Google Sheets**

1. **Truy cập**: https://sheets.google.com
2. **Tạo spreadsheet mới** với tên: "Hotel Booking Database"
3. **Copy Spreadsheet ID** từ URL (phần giữa `/d/` và `/edit`)

## 📋 **Bước 2: Tạo các sheet (tab)**

### **Sheet 1: hotels**
| A | B | C | D | E | F |
|---|---|---|---|---|---|
| id | name | address | city | phone | email |
| 1 | Hotel ABC | 123 Đường ABC | Hà Nội | 0123456789 | info@hotelabc.com |
| 2 | Hotel XYZ | 456 Đường XYZ | TP.HCM | 0987654321 | info@hotelxyz.com |

### **Sheet 2: room_types**
| A | B | C | D | E |
|---|---|---|---|---|
| id | hotel_id | name | description | price_per_night |
| 1 | 1 | Phòng Standard | Phòng đơn tiêu chuẩn | 500000 |
| 2 | 1 | Phòng Deluxe | Phòng đôi cao cấp | 800000 |
| 3 | 2 | Phòng Suite | Phòng suite sang trọng | 1200000 |

### **Sheet 3: rooms**
| A | B | C | D | E |
|---|---|---|---|---|
| id | hotel_id | room_type_id | room_number | status |
| 1 | 1 | 1 | 101 | available |
| 2 | 1 | 1 | 102 | available |
| 3 | 1 | 2 | 201 | available |
| 4 | 2 | 3 | 301 | available |

### **Sheet 4: bookings**
| A | B | C | D | E | F | G | H | I | J | K | L | M | N |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| id | hotel_id | room_type_id | customer_name | customer_email | customer_phone | checkin_date | checkout_date | total_price | status | payment_status | payment_method | payment_id | notes | created_at |

## 🔑 **Bước 3: Thiết lập Google API**

1. **Truy cập**: https://console.developers.google.com
2. **Tạo project mới** hoặc chọn project có sẵn
3. **Enable Google Sheets API**
4. **Tạo Service Account**:
   - Vào "Credentials" → "Create Credentials" → "Service Account"
   - Tải file JSON credentials
   - Đổi tên file thành `google-credentials.json`
   - Đặt vào thư mục `config/`

## 🔐 **Bước 4: Cấp quyền cho Google Sheet**

1. **Mở Google Sheet** đã tạo
2. **Nhấn "Share"** (Chia sẻ)
3. **Thêm email của Service Account** (từ file JSON)
4. **Cấp quyền "Editor"**

## ⚙️ **Bước 5: Cập nhật config**

1. **Copy Spreadsheet ID** vào file `config/database.php`
2. **Đặt file `google-credentials.json`** vào thư mục `config/`

## ✅ **Bước 6: Test kết nối**

Truy cập: `http://localhost:8000/test_google_sheets.php`

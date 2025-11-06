@echo off
chcp 65001 >nul
color 0A
title 🏨 Hotel Booking System - Auto Setup & Start

echo ============================================
echo    🏨 HOTEL BOOKING SYSTEM - AUTO START
echo ============================================
echo.

REM ====================================
REM CHECK REQUIREMENTS
REM ====================================
echo [1/7] Checking Node.js...
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERROR: Node.js chưa được cài đặt!
    echo 📥 Download tại: https://nodejs.org/
    pause
    exit /b 1
)
echo ✅ Node.js OK

echo.
echo [2/7] Checking MySQL (XAMPP)...
netstat -ano | findstr ":3306" >nul 2>&1
if %errorlevel% neq 0 (
    echo ⚠️  WARNING: MySQL chưa chạy!
    echo 🔧 Hãy mở XAMPP và Start MySQL
    pause
)
echo ✅ MySQL OK

REM ====================================
REM SETUP BACKEND
REM ====================================
echo.
echo [3/7] Setting up Backend...
cd backend

REM Check if node_modules exists
if not exist "node_modules\" (
    echo 📦 Installing backend dependencies...
    call npm install
    if %errorlevel% neq 0 (
        echo ❌ ERROR: Backend npm install failed!
        pause
        exit /b 1
    )
    echo ✅ Backend dependencies installed
) else (
    echo ✅ Backend dependencies already installed
)

REM Check if .env exists
if not exist ".env" (
    echo 📝 Creating .env file...
    (
        echo PORT=5000
        echo NODE_ENV=development
        echo.
        echo DB_HOST=localhost
        echo DB_USER=root
        echo DB_PASSWORD=
        echo DB_NAME=booking_hotel
        echo.
        echo JWT_SECRET=booking_hotel_secret_key_2024_change_in_production
        echo JWT_EXPIRES_IN=7d
    ) > .env
    echo ✅ .env created
)

REM Run migration if needed
echo 🗄️  Running database migration...
node src/config/migrate.js
if %errorlevel% neq 0 (
    echo ⚠️  Migration might have failed, but continuing...
)

REM Seed sample data if database is empty
echo 🌱 Seeding sample data...
node src/config/seed.js
if %errorlevel% neq 0 (
    echo ⚠️  Seed might have failed, but continuing...
)

cd ..

REM ====================================
REM SETUP FRONTEND
REM ====================================
echo.
echo [4/7] Setting up Frontend...
cd frontend

REM Check if node_modules exists
if not exist "node_modules\" (
    echo 📦 Installing frontend dependencies...
    call npm install
    if %errorlevel% neq 0 (
        echo ❌ ERROR: Frontend npm install failed!
        pause
        exit /b 1
    )
    echo ✅ Frontend dependencies installed
) else (
    echo ✅ Frontend dependencies already installed
)

REM Check if .env exists
if not exist ".env" (
    echo 📝 Creating .env file...
    echo REACT_APP_API_URL=http://localhost:5000/api > .env
    echo ✅ .env created
)

cd ..

REM ====================================
REM START SERVERS
REM ====================================
echo.
echo [5/7] Starting Backend Server...
start "Backend Server (Port 5000)" cmd /k "cd /d %~dp0backend && npm run dev"
timeout /t 3 /nobreak >nul

echo.
echo [6/7] Starting Frontend Server...
start "Frontend Server (Port 3000)" cmd /k "cd /d %~dp0frontend && set HOST=localhost && npm start"
timeout /t 5 /nobreak >nul

echo.
echo [7/7] Opening Browser...
echo ⏳ Waiting for servers to start (30 seconds)...
timeout /t 30 /nobreak

start http://localhost:3000

echo.
echo ============================================
echo    ✅ HỆ THỐNG ĐÃ KHỞI ĐỘNG!
echo ============================================
echo.
echo 🌐 Frontend:  http://localhost:3000
echo 🔧 Backend:   http://localhost:5000
echo.
echo 👤 Admin Login:
echo    Email:    admin@bookinghotel.com
echo    Password: admin123
echo.
echo 📝 Để DỪNG server: Đóng các cửa sổ terminal
echo ============================================
echo.
pause


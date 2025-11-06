const mysql = require('mysql2/promise');
require('dotenv').config();

// Script seed dữ liệu mẫu vào database
const seedData = async () => {
  try {
    console.log('🌱 Starting database seeding...\n');

    const { pool } = require('./database');

    // Kiểm tra xem đã có dữ liệu chưa (có thể force seed bằng --force)
    const forceSeed = process.argv.includes('--force');
    const [hotels] = await pool.query('SELECT COUNT(*) as count FROM hotels');
    if (hotels[0].count > 0 && !forceSeed) {
      console.log('⚠️  Database already has data. Skipping seed.');
      console.log('💡 To force seed again, run: npm run seed -- --force');
      process.exit(0);
    }
    
    if (forceSeed && hotels[0].count > 0) {
      console.log('🔄 Force seeding: Clearing existing data...');
      await pool.query('DELETE FROM bookings');
      await pool.query('DELETE FROM rooms');
      await pool.query('DELETE FROM room_types');
      await pool.query('DELETE FROM hotels');
      console.log('✅ Existing data cleared\n');
    }

    // 1. INSERT HOTELS - Thêm NHIỀU khách sạn ở các thành phố khác nhau
    const hotelsData = [
      // Hà Nội - 5 hotels
      {
        name: 'Grand Hotel Ha Noi',
        address: '123 Pho Hue, Hoan Kiem',
        city: 'Ha Noi',
        phone: '0241234567',
        email: 'info@grandhotel.com',
        rating: 4.8,
        description: 'Khách sạn 5 sao sang trọng tại trung tâm Hà Nội',
        location: 'https://maps.google.com/grand-hotel-hanoi',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
          'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800'
        ])
      },
      {
        name: 'Royal Hotel Ha Noi',
        address: '111 Hang Bai, Hoan Kiem',
        city: 'Ha Noi',
        phone: '0249876543',
        email: 'info@royalhotel.com',
        rating: 4.7,
        description: 'Khách sạn hoàng gia tại Hà Nội',
        location: 'https://maps.google.com/royal-hotel-hanoi',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
          'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800'
        ])
      },
      {
        name: 'Sofitel Legend Metropole Hanoi',
        address: '15 Ngo Quyen, Hoan Kiem',
        city: 'Ha Noi',
        phone: '02438266919',
        email: 'info@sofitel.com',
        rating: 4.9,
        description: 'Khách sạn 5 sao lịch sử tại Hà Nội',
        location: 'https://maps.google.com/sofitel-hanoi',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
          'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800'
        ])
      },
      {
        name: 'Hanoi La Siesta Hotel',
        address: '94 Ma May, Hoan Kiem',
        city: 'Ha Noi',
        phone: '02439260111',
        email: 'info@lasiesta.com',
        rating: 4.6,
        description: 'Boutique hotel tại phố cổ Hà Nội',
        location: 'https://maps.google.com/lasiesta-hanoi',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
          'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800'
        ])
      },
      {
        name: 'InterContinental Hanoi Westlake',
        address: '5 Tu Hoa, Tay Ho',
        city: 'Ha Noi',
        phone: '02462708888',
        email: 'info@intercontinental.com',
        rating: 4.8,
        description: 'Khách sạn 5 sao view hồ Tây',
        location: 'https://maps.google.com/intercontinental-hanoi',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800',
          'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800'
        ])
      },
      // TP.HCM - 5 hotels
      {
        name: 'Luxury Hotel Ho Chi Minh',
        address: '456 Nguyen Hue, District 1',
        city: 'Ho Chi Minh',
        phone: '0287654321',
        email: 'info@luxuryhotel.com',
        rating: 4.9,
        description: 'Khách sạn hiện đại tại trung tâm TP.HCM',
        location: 'https://maps.google.com/luxury-hotel-hcm',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800',
          'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800'
        ])
      },
      {
        name: 'Skyline Hotel Ho Chi Minh',
        address: '222 Le Loi, District 1',
        city: 'Ho Chi Minh',
        phone: '0289876543',
        email: 'info@skylinehotel.com',
        rating: 4.6,
        description: 'Khách sạn cao tầng view thành phố',
        location: 'https://maps.google.com/skyline-hotel-hcm',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=800',
          'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800'
        ])
      },
      {
        name: 'Park Hyatt Saigon',
        address: '2 Lam Son Square, District 1',
        city: 'Ho Chi Minh',
        phone: '02838241234',
        email: 'info@parkhyatt.com',
        rating: 4.9,
        description: 'Khách sạn 5 sao sang trọng tại trung tâm Sài Gòn',
        location: 'https://maps.google.com/parkhyatt-saigon',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=800',
          'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800'
        ])
      },
      {
        name: 'Rex Hotel Saigon',
        address: '141 Nguyen Hue, District 1',
        city: 'Ho Chi Minh',
        phone: '02838292185',
        email: 'info@rexhotel.com',
        rating: 4.5,
        description: 'Khách sạn lịch sử tại trung tâm Sài Gòn',
        location: 'https://maps.google.com/rex-hotel-saigon',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=800',
          'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800'
        ])
      },
      {
        name: 'Caravelle Hotel Saigon',
        address: '19 Lam Son Square, District 1',
        city: 'Ho Chi Minh',
        phone: '02838239999',
        email: 'info@caravelle.com',
        rating: 4.7,
        description: 'Khách sạn 5 sao tại trung tâm quận 1',
        location: 'https://maps.google.com/caravelle-saigon',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=800',
          'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800'
        ])
      },
      // Đà Nẵng - 4 hotels
      {
        name: 'Beach Resort Da Nang',
        address: '789 Vo Nguyen Giap, My Khe',
        city: 'Da Nang',
        phone: '0236123456',
        email: 'info@beachresort.com',
        rating: 4.7,
        description: 'Resort bãi biển tuyệt đẹp tại Đà Nẵng',
        location: 'https://maps.google.com/beach-resort-danang',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800',
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800'
        ])
      },
      {
        name: 'Ocean View Resort Da Nang',
        address: '888 Truong Sa, Da Nang',
        city: 'Da Nang',
        phone: '0236987654',
        email: 'info@oceanview.com',
        rating: 4.9,
        description: 'Resort view biển tuyệt đẹp',
        location: 'https://maps.google.com/ocean-view-danang',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800',
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800'
        ])
      },
      {
        name: 'InterContinental Danang Sun Peninsula Resort',
        address: 'Bai Bac, Son Tra Peninsula',
        city: 'Da Nang',
        phone: '02363938888',
        email: 'info@icdanang.com',
        rating: 4.9,
        description: 'Resort 5 sao trên bán đảo Sơn Trà',
        location: 'https://maps.google.com/ic-danang',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800',
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800'
        ])
      },
      {
        name: 'Fusion Maia Da Nang',
        address: 'Vo Nguyen Giap, Khue My',
        city: 'Da Nang',
        phone: '02363927777',
        email: 'info@fusionmaia.com',
        rating: 4.8,
        description: 'Resort spa all-inclusive tại Đà Nẵng',
        location: 'https://maps.google.com/fusion-maia-danang',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800',
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800'
        ])
      },
      // Đà Lạt - 4 hotels
      {
        name: 'Mountain View Hotel Da Lat',
        address: '321 Tran Phu, Da Lat',
        city: 'Da Lat',
        phone: '0263123456',
        email: 'info@mountainview.com',
        rating: 4.6,
        description: 'Khách sạn view núi tại Đà Lạt',
        location: 'https://maps.google.com/mountain-view-dalat',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800',
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800'
        ])
      },
      {
        name: 'Garden Hotel Da Lat',
        address: '555 Xuan Huong, Da Lat',
        city: 'Da Lat',
        phone: '0263987654',
        email: 'info@gardenhotel.com',
        rating: 4.5,
        description: 'Khách sạn vườn hoa Đà Lạt',
        location: 'https://maps.google.com/garden-hotel-dalat',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800',
          'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800'
        ])
      },
      {
        name: 'Ana Mandara Villas Dalat Resort',
        address: 'Le Lai, Da Lat',
        city: 'Da Lat',
        phone: '02633558888',
        email: 'info@anamandara.com',
        rating: 4.7,
        description: 'Resort biệt thự tại Đà Lạt',
        location: 'https://maps.google.com/ana-mandara-dalat',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800',
          'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800'
        ])
      },
      {
        name: 'Terracotta Hotel & Resort Dalat',
        address: 'Tuyen Lam Lake, Da Lat',
        city: 'Da Lat',
        phone: '02633828888',
        email: 'info@terracotta.com',
        rating: 4.6,
        description: 'Resort view hồ Tuyền Lâm',
        location: 'https://maps.google.com/terracotta-dalat',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800',
          'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800'
        ])
      },
      // Vũng Tàu - 3 hotels
      {
        name: 'Coastal Hotel Vung Tau',
        address: '654 Thuy Van, Vung Tau',
        city: 'Vung Tau',
        phone: '0254123456',
        email: 'info@coastalhotel.com',
        rating: 4.5,
        description: 'Khách sạn ven biển Vũng Tàu',
        location: 'https://maps.google.com/coastal-hotel-vungtau',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=800',
          'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800'
        ])
      },
      {
        name: 'The Grand Ho Tram Strip',
        address: 'Phuoc Thuan, Xuyen Moc',
        city: 'Vung Tau',
        phone: '02543888888',
        email: 'info@grandhotram.com',
        rating: 4.8,
        description: 'Resort casino 5 sao tại Hồ Tràm',
        location: 'https://maps.google.com/grand-hotram',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=800',
          'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800'
        ])
      },
      {
        name: 'Pullman Vung Tau',
        address: '15 Thi Sach, Vung Tau',
        city: 'Vung Tau',
        phone: '02543851111',
        email: 'info@pullman.com',
        rating: 4.7,
        description: 'Khách sạn 5 sao tại Vũng Tàu',
        location: 'https://maps.google.com/pullman-vungtau',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=800',
          'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800'
        ])
      },
      // Nha Trang - 4 hotels
      {
        name: 'Paradise Resort Nha Trang',
        address: '987 Tran Phu, Nha Trang',
        city: 'Nha Trang',
        phone: '0258123456',
        email: 'info@paradiseresort.com',
        rating: 4.8,
        description: 'Resort nghỉ dưỡng tại Nha Trang',
        location: 'https://maps.google.com/paradise-resort-nhatrang',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800',
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800'
        ])
      },
      {
        name: 'Vinpearl Resort Nha Trang',
        address: 'Hon Tre Island, Nha Trang',
        city: 'Nha Trang',
        phone: '02583818888',
        email: 'info@vinpearl.com',
        rating: 4.9,
        description: 'Resort đảo 5 sao tại Nha Trang',
        location: 'https://maps.google.com/vinpearl-nhatrang',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800',
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800'
        ])
      },
      {
        name: 'InterContinental Nha Trang',
        address: '32-34 Tran Phu, Nha Trang',
        city: 'Nha Trang',
        phone: '02583888888',
        email: 'info@icnhatrang.com',
        rating: 4.8,
        description: 'Khách sạn 5 sao view biển Nha Trang',
        location: 'https://maps.google.com/ic-nhatrang',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800',
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800'
        ])
      },
      {
        name: 'Amanoi Resort',
        address: 'Vinh Hy Bay, Ninh Thuan',
        city: 'Nha Trang',
        phone: '02593888888',
        email: 'info@amanoi.com',
        rating: 5.0,
        description: 'Resort 5 sao siêu sang tại vịnh Vĩnh Hy',
        location: 'https://maps.google.com/amanoi',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800',
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800'
        ])
      },
      // Phú Quốc - 3 hotels
      {
        name: 'JW Marriott Phu Quoc Emerald Bay',
        address: 'Bai Khem, Phu Quoc',
        city: 'Phu Quoc',
        phone: '02973999999',
        email: 'info@jwmarriott.com',
        rating: 4.9,
        description: 'Resort 5 sao tại Phú Quốc',
        location: 'https://maps.google.com/jwmarriott-phuquoc',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800',
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800'
        ])
      },
      {
        name: 'InterContinental Phu Quoc Long Beach Resort',
        address: 'Bai Truong, Duong To',
        city: 'Phu Quoc',
        phone: '02973788888',
        email: 'info@icphuquoc.com',
        rating: 4.8,
        description: 'Resort bãi biển dài Phú Quốc',
        location: 'https://maps.google.com/ic-phuquoc',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800',
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800'
        ])
      },
      {
        name: 'Salinda Resort Phu Quoc Island',
        address: 'Cua Lap, Duong To',
        city: 'Phu Quoc',
        phone: '02973991111',
        email: 'info@salinda.com',
        rating: 4.7,
        description: 'Resort nghỉ dưỡng tại Phú Quốc',
        location: 'https://maps.google.com/salinda-phuquoc',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800',
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800'
        ])
      },
      // Hội An - 2 hotels
      {
        name: 'Four Seasons Resort The Nam Hai',
        address: 'Block Ha My, Dien Duong',
        city: 'Hoi An',
        phone: '02353940000',
        email: 'info@fourseasons.com',
        rating: 5.0,
        description: 'Resort 5 sao tại Hội An',
        location: 'https://maps.google.com/fourseasons-hoian',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800',
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800'
        ])
      },
      {
        name: 'Anantara Hoi An Resort',
        address: '1 Pham Hong Thai, Hoi An',
        city: 'Hoi An',
        phone: '02353911111',
        email: 'info@anantara.com',
        rating: 4.8,
        description: 'Resort sông tại Hội An',
        location: 'https://maps.google.com/anantara-hoian',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800',
          'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=800'
        ])
      },
      // Sapa - 2 hotels
      {
        name: 'Topas Ecolodge Sapa',
        address: 'Ban Lech, Sapa',
        city: 'Sapa',
        phone: '02143888888',
        email: 'info@topas.com',
        rating: 4.7,
        description: 'Ecolodge view núi tại Sapa',
        location: 'https://maps.google.com/topas-sapa',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800',
          'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800'
        ])
      },
      {
        name: 'Hotel de la Coupole Sapa',
        address: '1 Hoang Lien, Sapa',
        city: 'Sapa',
        phone: '02143911111',
        email: 'info@delacoupole.com',
        rating: 4.8,
        description: 'Khách sạn 5 sao tại Sapa',
        location: 'https://maps.google.com/delacoupole-sapa',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800',
          'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800'
        ])
      },
      // Huế - 2 hotels
      {
        name: 'La Residence Hue Hotel & Spa',
        address: '5 Le Loi, Hue',
        city: 'Hue',
        phone: '02343837755',
        email: 'info@laresidence.com',
        rating: 4.7,
        description: 'Khách sạn 5 sao tại Huế',
        location: 'https://maps.google.com/laresidence-hue',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800',
          'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800'
        ])
      },
      {
        name: 'Pilgrimage Village Boutique Resort & Spa',
        address: '130 Minh Mang, Hue',
        city: 'Hue',
        phone: '02343618888',
        email: 'info@pilgrimage.com',
        rating: 4.6,
        description: 'Boutique resort tại Huế',
        location: 'https://maps.google.com/pilgrimage-hue',
        images: JSON.stringify([
          'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=800',
          'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800'
        ])
      }
    ];

    console.log('📦 Inserting hotels...');
    for (const hotel of hotelsData) {
      await pool.query(
        `INSERT INTO hotels (name, address, city, phone, email, rating, description, location, images) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [hotel.name, hotel.address, hotel.city, hotel.phone, hotel.email, hotel.rating, hotel.description, hotel.location, hotel.images]
      );
    }
    console.log(`✅ Inserted ${hotelsData.length} hotels\n`);

    // 2. INSERT ROOM TYPES - Thêm nhiều loại phòng cho mỗi khách sạn
    const [insertedHotels] = await pool.query('SELECT id, city FROM hotels ORDER BY id');
    
    console.log('📦 Inserting room types...');
    let roomTypeCount = 0;
    
    for (const hotel of insertedHotels) {
      const roomTypes = [
        {
          name: 'Phòng Standard',
          description: 'Phòng tiêu chuẩn với đầy đủ tiện nghi',
          price: 500000 + Math.floor(Math.random() * 300000),
          max_guests: 2,
          size: '25m²',
          amenities: JSON.stringify(['WiFi', 'TV', 'Điều hòa', 'Tủ lạnh', 'Phòng tắm riêng']),
          images: JSON.stringify(['https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800'])
        },
        {
          name: 'Phòng Deluxe',
          description: 'Phòng cao cấp với view đẹp',
          price: 800000 + Math.floor(Math.random() * 500000),
          max_guests: 3,
          size: '35m²',
          amenities: JSON.stringify(['WiFi', 'TV', 'Điều hòa', 'Tủ lạnh', 'Phòng tắm riêng', 'Ban công', 'Minibar']),
          images: JSON.stringify(['https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=800'])
        },
        {
          name: 'Phòng Suite',
          description: 'Phòng suite sang trọng',
          price: 1200000 + Math.floor(Math.random() * 800000),
          max_guests: 4,
          size: '50m²',
          amenities: JSON.stringify(['WiFi', 'TV', 'Điều hòa', 'Tủ lạnh', 'Phòng tắm riêng', 'Ban công', 'Minibar', 'Bồn tắm', 'Phòng khách']),
          images: JSON.stringify(['https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=800'])
        },
        {
          name: 'Phòng Family',
          description: 'Phòng gia đình rộng rãi',
          price: 1000000 + Math.floor(Math.random() * 600000),
          max_guests: 5,
          size: '45m²',
          amenities: JSON.stringify(['WiFi', 'TV', 'Điều hòa', 'Tủ lạnh', 'Phòng tắm riêng', 'Giường phụ', 'Khu vực chơi']),
          images: JSON.stringify(['https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800'])
        }
      ];

      for (const roomType of roomTypes) {
        await pool.query(
          `INSERT INTO room_types (hotel_id, name, description, price, max_guests, size, amenities, images) 
           VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
          [hotel.id, roomType.name, roomType.description, roomType.price, roomType.max_guests, roomType.size, roomType.amenities, roomType.images]
        );
        roomTypeCount++;
      }
    }
    console.log(`✅ Inserted ${roomTypeCount} room types\n`);

    // 3. INSERT ROOMS - Thêm phòng cho mỗi room type
    const [roomTypes] = await pool.query('SELECT id FROM room_types ORDER BY id');
    
    console.log('📦 Inserting rooms...');
    let roomCount = 0;
    
    for (const roomType of roomTypes) {
      // Mỗi room type có 5-10 phòng
      const numRooms = 5 + Math.floor(Math.random() * 6);
      for (let i = 1; i <= numRooms; i++) {
        await pool.query(
          `INSERT INTO rooms (room_type_id, room_number, floor, status) 
           VALUES (?, ?, ?, ?)`,
          [roomType.id, `${100 + i}`, Math.floor((100 + i) / 100), 'available']
        );
        roomCount++;
      }
    }
    console.log(`✅ Inserted ${roomCount} rooms\n`);

    console.log('🎉 Database seeding completed successfully!');
    console.log(`\n📊 Summary:`);
    console.log(`   🏨 Hotels: ${hotelsData.length}`);
    console.log(`   🛏️  Room Types: ${roomTypeCount}`);
    console.log(`   🚪 Rooms: ${roomCount}`);
    
    process.exit(0);
  } catch (error) {
    console.error('❌ Seeding failed:', error.message);
    process.exit(1);
  }
};

// Run seeding
seedData();


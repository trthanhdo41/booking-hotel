import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { hotelsAPI, roomTypesAPI } from '../services/api';
import { formatPrice } from '../utils/helpers';
import { toast } from 'react-toastify';

const HomePage = () => {
  const navigate = useNavigate();
  const { isAuthenticated } = useAuth();
  const [searchData, setSearchData] = useState({
    city: '',
    checkin: '',
    checkout: '',
    guests: 2,
  });
  const [featuredRooms, setFeaturedRooms] = useState([]);
  const [trendingDestinations, setTrendingDestinations] = useState([]);
  const [showGuestsDropdown, setShowGuestsDropdown] = useState(false);

  useEffect(() => {
    loadFeaturedRooms();
    loadCities();
  }, []);

  const loadFeaturedRooms = async () => {
    try {
      const response = await roomTypesAPI.getAll();
      // Lấy nhiều hơn để hiển thị phong phú
      setFeaturedRooms(response.data.data.slice(0, 12));
    } catch (error) {
      console.error('Error loading rooms:', error);
    }
  };

  const loadCities = async () => {
    try {
      const response = await hotelsAPI.getCities();
      // Lấy tất cả cities từ database
      const cities = response.data.data.map((city, index) => {
        // Map city name với image tương ứng
        const cityImageMap = {
          'Ha Noi': 'https://images.unsplash.com/photo-1557750255-c76072a7aad1?w=600',
          'Ho Chi Minh': 'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=600',
          'Da Nang': 'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=600',
          'Da Lat': 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=600',
          'Vung Tau': 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=600',
          'Nha Trang': 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=600'
        };
        
        return {
          name: city.city,
          hotel_count: city.hotel_count,
          image: cityImageMap[city.city] || `https://images.unsplash.com/photo-1557750255-c76072a7aad1?w=600`
        };
      });
      setTrendingDestinations(cities);
    } catch (error) {
      console.error('Error loading cities:', error);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    
    if (!isAuthenticated()) {
      toast.warning('Vui lòng đăng nhập để tìm kiếm phòng');
      navigate('/login');
      return;
    }

    if (!searchData.city || !searchData.checkin || !searchData.checkout) {
      toast.error('Vui lòng nhập đầy đủ thông tin tìm kiếm');
      return;
    }

    const params = new URLSearchParams(searchData).toString();
    navigate(`/search?${params}`);
  };


  const propertyTypes = [
    { name: 'Khách sạn', icon: '🏨', image: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400' },
    { name: 'Căn hộ', icon: '🏠', image: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400' },
    { name: 'Resort', icon: '🏖️', image: 'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=400' },
    { name: 'Biệt thự', icon: '🏡', image: 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=400' }
  ];

  return (
    <div className="min-h-screen bg-white">
      {/* Hero Section - Booking.com Style */}
      <section className="bg-primary text-white pt-6 pb-24">
        <div className="max-w-6xl mx-auto px-4">
          <div className="mb-8 animate-fadeIn">
            <h1 className="text-5xl font-bold mb-3 hover:scale-105 transition-transform duration-300">
              Tìm chỗ nghỉ tiếp theo của bạn
            </h1>
            <p className="text-xl animate-slideUp">
              Tìm ưu đãi cho khách sạn, nhà và nhiều hơn nữa...
            </p>
          </div>

          {/* Search Form - Booking.com Style */}
          <form onSubmit={handleSearch} className="bg-white rounded shadow-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300 animate-slideUp">
            <div className="flex items-end">
              {/* Where are you going */}
              <div className="flex-1 border-r border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                <div className="p-4">
                  <label className="flex items-center text-xs font-semibold text-gray-700 mb-2">
                    <i className="fas fa-bed text-gray-600 mr-2 animate-pulse"></i>
                    Bạn muốn đến đâu?
                  </label>
                  <input
                    type="text"
                    placeholder="Hà Nội, TP. Hồ Chí Minh..."
                    value={searchData.city}
                    onChange={(e) => setSearchData({ ...searchData, city: e.target.value })}
                    className="w-full text-gray-900 text-sm focus:outline-none placeholder-gray-500 hover:placeholder-gray-700 transition-colors"
                  />
                </div>
              </div>

              {/* Check-in date */}
              <div className="flex-1 border-r border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                <div className="p-4">
                  <label className="flex items-center text-xs font-semibold text-gray-700 mb-2">
                    <i className="fas fa-calendar text-gray-600 mr-2"></i>
                    Ngày nhận phòng
                  </label>
                  <input
                    type="date"
                    value={searchData.checkin}
                    onChange={(e) => setSearchData({ ...searchData, checkin: e.target.value })}
                    min={new Date().toISOString().split('T')[0]}
                    className="w-full text-gray-900 text-sm focus:outline-none hover:cursor-pointer"
                  />
                </div>
              </div>

              {/* Check-out date */}
              <div className="flex-1 border-r border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                <div className="p-4">
                  <label className="flex items-center text-xs font-semibold text-gray-700 mb-2">
                    <i className="fas fa-calendar text-gray-600 mr-2"></i>
                    Ngày trả phòng
                  </label>
                  <input
                    type="date"
                    value={searchData.checkout}
                    onChange={(e) => setSearchData({ ...searchData, checkout: e.target.value })}
                    min={searchData.checkin || new Date().toISOString().split('T')[0]}
                    className="w-full text-gray-900 text-sm focus:outline-none hover:cursor-pointer"
                  />
                </div>
              </div>

              {/* Guests */}
              <div className="flex-1 border-r border-gray-200 relative hover:bg-gray-50 transition-colors duration-200">
                <div className="p-4">
                  <label className="flex items-center text-xs font-semibold text-gray-700 mb-2">
                    <i className="fas fa-user text-gray-600 mr-2"></i>
                    Khách
                  </label>
                  <button
                    type="button"
                    onClick={() => setShowGuestsDropdown(!showGuestsDropdown)}
                    className="w-full text-left text-gray-900 text-sm focus:outline-none hover:text-blue-600 transition-colors"
                  >
                    {searchData.guests} người lớn · 0 trẻ em · 1 phòng
                  </button>
                </div>
                {showGuestsDropdown && (
                  <div className="absolute top-full left-0 bg-white border shadow-lg rounded mt-1 p-4 z-10 w-64 animate-fadeIn">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm text-gray-700">Người lớn</span>
                      <div className="flex items-center">
                        <button
                          type="button"
                          onClick={() => setSearchData({ ...searchData, guests: Math.max(1, searchData.guests - 1) })}
                          className="w-8 h-8 border rounded-full hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 hover:scale-110"
                        >
                          -
                        </button>
                        <span className="mx-3 text-sm font-semibold">{searchData.guests}</span>
                        <button
                          type="button"
                          onClick={() => setSearchData({ ...searchData, guests: Math.min(10, searchData.guests + 1) })}
                          className="w-8 h-8 border rounded-full hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 hover:scale-110"
                        >
                          +
                        </button>
                      </div>
                    </div>
                  </div>
                )}
              </div>

              {/* Search Button */}
              <button
                type="submit"
                className="bg-blue-600 hover:bg-blue-700 text-white px-12 py-[3.3rem] font-semibold text-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
              >
                <i className="fas fa-search mr-2"></i>
                Tìm kiếm
              </button>
            </div>

          </form>
        </div>
      </section>

      {/* Why Booking.com? */}
      <section className="py-12 bg-white">
        <div className="max-w-6xl mx-auto px-4">
          <h2 className="text-2xl font-bold mb-8">Tại sao chọn Booking.com?</h2>
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div className="bg-gray-50 p-6 rounded border border-gray-200 hover:shadow-lg hover:scale-105 hover:bg-white transition-all duration-300 cursor-pointer group">
              <div className="mb-3 group-hover:scale-110 transition-transform duration-300 flex justify-center">
                <img 
                  src="https://t-cf.bstatic.com/design-assets/assets/v3.160.0/illustrations-traveller/FreeCancellation.png" 
                  alt="Free Cancellation"
                  className="h-20 w-auto object-contain"
                />
              </div>
              <h3 className="font-bold mb-2 group-hover:text-primary transition-colors">Đặt ngay, thanh toán tại khách sạn</h3>
              <p className="text-sm text-gray-600">Miễn phí hủy phòng với hầu hết các khách sạn</p>
            </div>
            <div className="bg-gray-50 p-6 rounded border border-gray-200 hover:shadow-lg hover:scale-105 hover:bg-white transition-all duration-300 cursor-pointer group">
              <div className="mb-3 group-hover:scale-110 transition-transform duration-300 flex justify-center">
                <img 
                  src="https://t-cf.bstatic.com/design-assets/assets/v3.160.0/illustrations-traveller/Reviews.png" 
                  alt="Reviews"
                  className="h-20 w-auto object-contain"
                />
              </div>
              <h3 className="font-bold mb-2 group-hover:text-primary transition-colors">Hơn 300 triệu đánh giá</h3>
              <p className="text-sm text-gray-600">Từ khách du lịch đã trải nghiệm</p>
            </div>
            <div className="bg-gray-50 p-6 rounded border border-gray-200 hover:shadow-lg hover:scale-105 hover:bg-white transition-all duration-300 cursor-pointer group">
              <div className="mb-3 group-hover:scale-110 transition-transform duration-300 flex justify-center">
                <img 
                  src="https://t-cf.bstatic.com/design-assets/assets/v3.160.0/illustrations-traveller/TripsGlobe.png" 
                  alt="Worldwide Properties"
                  className="h-20 w-auto object-contain"
                />
              </div>
              <h3 className="font-bold mb-2 group-hover:text-primary transition-colors">Hơn 2 triệu khách sạn</h3>
              <p className="text-sm text-gray-600">Khách sạn, nhà nghỉ, căn hộ và hơn thế nữa...</p>
            </div>
            <div className="bg-gray-50 p-6 rounded border border-gray-200 hover:shadow-lg hover:scale-105 hover:bg-white transition-all duration-300 cursor-pointer group">
              <div className="mb-3 group-hover:scale-110 transition-transform duration-300 flex justify-center">
                <img 
                  src="https://t-cf.bstatic.com/design-assets/assets/v3.160.0/illustrations-traveller/CustomerSupport.png" 
                  alt="Customer Support"
                  className="h-20 w-auto object-contain"
                />
              </div>
              <h3 className="font-bold mb-2 group-hover:text-primary transition-colors">Hỗ trợ 24/7 đáng tin cậy</h3>
              <p className="text-sm text-gray-600">Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
            </div>
          </div>
        </div>
      </section>

      {/* Offers */}
      <section className="py-8 bg-white">
        <div className="max-w-6xl mx-auto px-4">
          <h2 className="text-2xl font-bold mb-1">Ưu đãi</h2>
          <p className="text-gray-600 mb-6">Khuyến mãi, giảm giá và ưu đãi đặc biệt dành cho bạn</p>
          
          <div className="bg-gradient-to-r from-yellow-800 to-orange-900 rounded-lg overflow-hidden relative hover:shadow-2xl transition-all duration-300 hover:scale-[1.02] cursor-pointer group">
            <div className="flex items-center justify-between p-8">
              <div className="text-white max-w-md z-10 relative">
                <div className="text-sm font-semibold mb-2 group-hover:tracking-wide transition-all duration-300">Ưu Đãi Phút Chót</div>
                <h3 className="text-3xl font-bold mb-3 group-hover:scale-105 transition-transform duration-300">Tận hưởng kỳ nghỉ ngắn ngày tuyệt vời</h3>
                <p className="mb-6">Tận dụng những ngày nắng cuối cùng với ưu đãi giảm tối thiểu 15%</p>
                <button className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded font-semibold transition-all duration-300 hover:scale-110 hover:shadow-lg">
                  <i className="fas fa-search mr-2"></i>
                  Tìm ưu đãi
                </button>
              </div>
              <div className="absolute right-0 top-0 bottom-0 w-1/2 opacity-30 group-hover:opacity-40 transition-opacity duration-300">
                <img src="https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=600" alt="Beach" className="h-full w-full object-cover group-hover:scale-110 transition-transform duration-700" />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Trending destinations */}
      {trendingDestinations.length > 0 && (
        <section className="py-12 bg-white">
          <div className="max-w-6xl mx-auto px-4">
            <h2 className="text-2xl font-bold mb-2">Điểm đến xu hướng</h2>
            <p className="text-gray-600 mb-6">Lựa chọn phổ biến nhất cho du khách từ Việt Nam</p>
            
            <div className="grid grid-cols-2 gap-4">
              {/* Large item */}
              {trendingDestinations[0] && (
                <div 
                  className="row-span-2 relative rounded-lg overflow-hidden group cursor-pointer shadow-md hover:shadow-2xl transition-all duration-300"
                  onClick={() => navigate(`/search?city=${encodeURIComponent(trendingDestinations[0].name)}`)}
                >
                  <img 
                    src={trendingDestinations[0].image} 
                    alt={trendingDestinations[0].name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent group-hover:from-black/70 transition-all duration-300"></div>
                  <div className="absolute bottom-4 left-4 text-white transform group-hover:translate-x-2 transition-transform duration-300">
                    <h3 className="text-3xl font-bold flex items-center group-hover:scale-105 transition-transform duration-300">
                      {trendingDestinations[0].name}
                      <img 
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Vietnam.svg/1280px-Flag_of_Vietnam.svg.png" 
                        alt="Vietnam Flag"
                        className="ml-2 w-8 h-5 object-cover rounded border border-white/20 shadow-sm"
                      />
                    </h3>
                    <p className="text-lg mt-2">{trendingDestinations[0].hotel_count} chỗ nghỉ</p>
                  </div>
                </div>
              )}

              {/* Grid items */}
              {trendingDestinations.slice(1, 5).map((destination, index) => (
                <div 
                  key={index} 
                  className="relative rounded-lg overflow-hidden group cursor-pointer h-48 shadow-md hover:shadow-2xl transition-all duration-300"
                  onClick={() => navigate(`/search?city=${encodeURIComponent(destination.name)}`)}
                >
                  <img 
                    src={destination.image} 
                    alt={destination.name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent group-hover:from-black/70 transition-all duration-300"></div>
                  <div className="absolute bottom-4 left-4 text-white transform group-hover:translate-x-2 transition-transform duration-300">
                    <h3 className="text-xl font-bold flex items-center group-hover:scale-105 transition-transform duration-300">
                      {destination.name}
                      <img 
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/21/Flag_of_Vietnam.svg/1280px-Flag_of_Vietnam.svg.png" 
                        alt="Vietnam Flag"
                        className="ml-2 w-6 h-4 object-cover rounded border border-white/20 shadow-sm"
                      />
                    </h3>
                    <p className="text-sm mt-1">{destination.hotel_count} chỗ nghỉ</p>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Browse by property type */}
      <section className="py-12 bg-white">
        <div className="max-w-6xl mx-auto px-4">
          <h2 className="text-2xl font-bold mb-6">Tìm kiếm theo loại chỗ nghỉ</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
            {propertyTypes.map((type, index) => (
              <div key={index} className="cursor-pointer group">
                <div className="relative rounded-lg overflow-hidden mb-3 h-48 shadow-md hover:shadow-2xl transition-all duration-300">
                  <img 
                    src={type.image} 
                    alt={type.name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                  <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
                </div>
                <h3 className="font-bold text-lg group-hover:text-primary group-hover:translate-x-2 transition-all duration-300">{type.name}</h3>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Explore Vietnam */}
      {trendingDestinations.length > 0 && (
        <section className="py-12 bg-white">
          <div className="max-w-6xl mx-auto px-4">
            <h2 className="text-2xl font-bold mb-2">Khám phá Việt Nam</h2>
            <p className="text-gray-600 mb-6">Những điểm đến phổ biến này có rất nhiều điều để trải nghiệm</p>
            
            <div className="grid grid-cols-2 md:grid-cols-6 gap-4">
              {trendingDestinations.map((city, index) => (
                <div 
                  key={index} 
                  className="cursor-pointer group"
                  onClick={() => navigate(`/search?city=${encodeURIComponent(city.name)}`)}
                >
                  <div className="relative rounded-lg overflow-hidden mb-2 h-32 shadow hover:shadow-lg transition-all duration-300">
                    <img 
                      src={city.image} 
                      alt={city.name}
                      className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    />
                    <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
                  </div>
                  <h3 className="font-bold group-hover:text-primary transition-colors">{city.name}</h3>
                  <p className="text-sm text-gray-600">{city.hotel_count || 0} chỗ nghỉ</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Deals for the weekend */}
      {featuredRooms.length > 0 && (
        <section className="py-12 bg-white">
          <div className="max-w-6xl mx-auto px-4">
            <h2 className="text-2xl font-bold mb-2">Ưu đãi cuối tuần</h2>
            <p className="text-gray-600 mb-6">Tiết kiệm cho kỳ nghỉ từ 7 - 9 tháng 11</p>
            
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              {featuredRooms.slice(0, 4).map((room) => (
                <div 
                  key={room.id} 
                  className="border rounded-lg overflow-hidden hover:shadow-2xl transition-all duration-300 cursor-pointer group hover:scale-105"
                  onClick={() => navigate(`/search?city=${encodeURIComponent(room.hotel_city || '')}`)}
                >
                  <div className="relative h-48">
                    {(() => {
                      // Lấy image từ database hoặc dùng default
                      let roomImage = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400';
                      if (room.images) {
                        try {
                          const images = typeof room.images === 'string' ? JSON.parse(room.images) : room.images;
                          if (Array.isArray(images) && images.length > 0) {
                            roomImage = images[0];
                          }
                        } catch (e) {
                          // Nếu parse lỗi thì dùng default
                        }
                      }
                      return (
                        <img
                          src={roomImage}
                          alt={room.name}
                          className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        />
                      );
                    })()}
                    <button className="absolute top-3 right-3 bg-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-50 hover:scale-110 transition-all duration-300">
                      <i className="far fa-heart text-gray-600 hover:text-red-500 transition-colors"></i>
                    </button>
                    {room.hotel_rating >= 8 && (
                      <span className="absolute top-3 left-3 bg-blue-600 text-white px-2 py-1 text-xs font-semibold rounded animate-pulse">
                        Genius
                      </span>
                    )}
                  </div>
                  <div className="p-4">
                    <h3 className="font-bold mb-1 text-sm group-hover:text-primary transition-colors">{room.name}</h3>
                    <p className="text-xs text-gray-600 mb-2">{room.hotel_city}, Việt Nam</p>
                    <div className="flex items-center mb-2">
                      <span className="bg-primary text-white px-2 py-1 rounded text-xs font-bold mr-2 group-hover:bg-primary-dark transition-colors">
                        {parseFloat(room.hotel_rating || 7.9).toFixed(1)}
                      </span>
                      <span className="text-xs font-semibold">Rất tốt</span>
                      <span className="text-xs text-gray-500 ml-1">430 đánh giá</span>
                    </div>
                    <div className="border-t pt-3">
                      <div className="text-xs text-gray-500 mb-1">2 đêm</div>
                      <div className="flex items-baseline">
                        <span className="text-red-600 line-through text-sm mr-2">VND {formatPrice(room.price * 2.5)}</span>
                        <span className="text-xl font-bold text-primary">VND {formatPrice(room.price * 2)}</span>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Stay at our top unique properties */}
      {featuredRooms.length > 4 && (
        <section className="py-12 bg-white">
          <div className="max-w-6xl mx-auto px-4">
            <h2 className="text-2xl font-bold mb-2">Ở tại các chỗ nghỉ độc đáo nhất của chúng tôi</h2>
            <p className="text-gray-600 mb-6">Từ lâu đài và biệt thự đến thuyền và lều tuyết, chúng tôi có tất cả</p>
            
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              {featuredRooms.slice(4, 8).map((room) => {
                // Lấy image từ database hoặc dùng default
                let roomImage = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400';
                if (room.images) {
                  try {
                    const images = typeof room.images === 'string' ? JSON.parse(room.images) : room.images;
                    if (Array.isArray(images) && images.length > 0) {
                      roomImage = images[0];
                    }
                  } catch (e) {
                    // Nếu parse lỗi thì dùng default
                  }
                }
                
                return (
              <div 
                key={room.id} 
                className="border rounded-lg overflow-hidden hover:shadow-2xl transition-all duration-300 cursor-pointer group hover:scale-105 hover:-translate-y-2"
                onClick={() => navigate(`/search?city=${encodeURIComponent(room.hotel_city || '')}`)}
              >
                <div className="relative h-48">
                  <img
                    src={roomImage}
                    alt={room.name}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                  />
                  <button className="absolute top-3 right-3 bg-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-50 hover:scale-110 transition-all duration-300">
                    <i className="far fa-heart text-gray-600 hover:text-red-500 transition-colors"></i>
                  </button>
                </div>
                <div className="p-4">
                  <h3 className="font-bold mb-1 group-hover:text-primary transition-colors">{room.name}</h3>
                  <p className="text-sm text-gray-600 mb-2">{room.hotel_city || 'Đà Lạt'}, Việt Nam</p>
                  <div className="flex items-center mb-2">
                    <span className="bg-primary text-white px-2 py-1 rounded text-xs font-bold mr-2 group-hover:bg-primary-dark transition-colors">
                      {parseFloat(room.hotel_rating || 8.5).toFixed(1)}
                    </span>
                    <span className="text-xs font-semibold">Xuất sắc</span>
                  </div>
                  <div className="text-sm text-gray-500 mb-1">131 đánh giá</div>
                </div>
              </div>
              );
              })}
            </div>
          </div>
        </section>
      )}

      {/* Travel more, spend less */}
      <section className="py-12 bg-white">
        <div className="max-w-6xl mx-auto px-4">
          {/* Main Heading */}
          <h2 className="text-3xl font-bold text-gray-900 mb-8">Du lịch nhiều hơn, chi tiêu ít hơn</h2>
          
          {/* Sign in, save money Card */}
          <div className="bg-white border border-gray-200 rounded-lg p-8 mb-8 hover:shadow-xl transition-all duration-300 relative overflow-hidden">
            <div className="flex items-center justify-between">
              <div className="flex-1">
                <h3 className="text-xl font-bold text-gray-900 mb-2">Đăng nhập, tiết kiệm tiền</h3>
                <p className="text-sm text-gray-600 mb-6">Tiết kiệm 10% hoặc hơn tại các chỗ nghỉ tham gia – chỉ cần tìm nhãn Genius màu xanh</p>
                <div className="flex gap-3">
                  <button 
                    onClick={() => navigate('/login')}
                    className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded font-semibold transition-all duration-300 hover:scale-105 hover:shadow-lg"
                  >
                    Đăng nhập
                  </button>
                  <button 
                    onClick={() => navigate('/register')}
                    className="border-2 border-blue-600 text-blue-600 hover:bg-blue-50 px-6 py-2 rounded font-semibold transition-all duration-300 hover:scale-105"
                  >
                    Đăng ký
                  </button>
                </div>
              </div>
              
              {/* Genius Box Illustration */}
              <div className="hidden md:block ml-8 relative">
                <div className="relative w-32 h-32">
                  {/* Blue Gift Box */}
                  <div className="absolute inset-0 bg-blue-600 rounded-lg shadow-lg transform rotate-6 group-hover:rotate-12 transition-transform duration-300">
                    <div className="absolute top-2 left-2 right-2 h-8 bg-blue-700 rounded-t-lg"></div>
                    {/* Yellow Ribbon */}
                    <div className="absolute top-0 left-1/2 transform -translate-x-1/2 w-1 h-full bg-yellow-400"></div>
                    <div className="absolute top-1/2 left-0 right-0 h-1 bg-yellow-400 transform -translate-y-1/2">
                      <div className="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 w-8 h-8 bg-yellow-400 rounded-full border-4 border-blue-600"></div>
                    </div>
                    {/* Genius Text */}
                    <div className="absolute inset-0 flex items-center justify-center">
                      <span className="text-white font-bold text-sm">Genius</span>
                    </div>
                  </div>
                  {/* Confetti */}
                  <div className="absolute -top-2 -right-2 w-3 h-3 bg-yellow-400 rounded-full animate-pulse"></div>
                  <div className="absolute top-4 -left-2 w-2 h-2 bg-orange-400 rounded-full animate-pulse delay-75"></div>
                  <div className="absolute -bottom-2 left-4 w-2.5 h-2.5 bg-yellow-300 rounded-full animate-pulse delay-150"></div>
                </div>
              </div>
            </div>
          </div>

          {/* Want to feel at home - Vacation Rentals Banner */}
          <div className="relative bg-gradient-to-r from-blue-600 via-blue-600 to-blue-700 rounded-lg overflow-hidden border border-blue-700 hover:shadow-2xl transition-all duration-300 cursor-pointer group">
            {/* Abstract Background Shapes */}
            <div className="absolute inset-0 overflow-hidden">
              <div className="absolute -top-20 -left-20 w-64 h-64 bg-blue-500 rounded-full opacity-30 blur-3xl"></div>
              <div className="absolute top-10 right-10 w-48 h-48 bg-yellow-400 rounded-full opacity-20 blur-2xl"></div>
              <div className="absolute bottom-0 left-1/4 w-32 h-32 bg-blue-400 rounded-full opacity-25 blur-xl"></div>
            </div>
            
            <div className="relative flex items-center justify-between p-12">
              <div className="text-white max-w-md z-10">
                <h3 className="text-3xl font-bold mb-6 group-hover:scale-105 transition-transform duration-300">
                  Muốn cảm thấy thoải mái như ở nhà trong chuyến phiêu lưu tiếp theo?
                </h3>
                <button className="bg-white hover:bg-gray-100 text-blue-600 px-6 py-3 rounded font-semibold transition-all duration-300 hover:scale-110 hover:shadow-lg">
                  Khám phá nhà cho thuê nghỉ dưỡng
                </button>
              </div>
              
              {/* Cozy Illustration - Armchair with Cat */}
              <div className="hidden lg:block relative z-10 w-64 h-64">
                {/* Large Blue Circle Background */}
                <div className="absolute top-0 right-0 w-56 h-56 bg-blue-500 rounded-full"></div>
                
                {/* Yellow Armchair */}
                <div className="absolute bottom-8 right-8 w-40 h-32">
                  <div className="absolute inset-0 bg-yellow-400 rounded-lg transform rotate-3">
                    {/* Chair back */}
                    <div className="absolute top-0 left-0 right-0 h-16 bg-yellow-500 rounded-t-lg"></div>
                    {/* Chair seat */}
                    <div className="absolute bottom-0 left-0 right-0 h-16 bg-yellow-400 rounded-b-lg"></div>
                    {/* Cat sleeping */}
                    <div className="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-12 h-8 bg-white rounded-full">
                      <div className="absolute top-1 left-2 w-1 h-1 bg-gray-800 rounded-full"></div>
                      <div className="absolute top-1 right-2 w-1 h-1 bg-gray-800 rounded-full"></div>
                      <div className="absolute bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-1 bg-gray-800 rounded-full"></div>
                    </div>
                  </div>
                </div>
                
                {/* Plant */}
                <div className="absolute bottom-16 left-4 w-8 h-16">
                  <div className="absolute bottom-0 w-8 h-6 bg-white rounded-t-lg"></div>
                  <div className="absolute bottom-6 left-1/2 transform -translate-x-1/2 w-6 h-10 bg-blue-800 rounded-t-full"></div>
                </div>
                
                {/* Side Table with Cup */}
                <div className="absolute bottom-12 right-20 w-6 h-8">
                  <div className="absolute bottom-0 w-6 h-6 bg-blue-800 rounded-t-lg"></div>
                  <div className="absolute bottom-6 left-1/2 transform -translate-x-1/2 w-4 h-4 bg-yellow-400 rounded-full border-2 border-blue-800"></div>
                </div>
                
                {/* Wall Art */}
                <div className="absolute top-4 left-4 w-16 h-12 bg-white rounded border-2 border-blue-300">
                  <div className="absolute bottom-0 left-0 right-0 h-4 bg-blue-200 rounded-b"></div>
                  <div className="absolute bottom-4 left-0 right-0 h-2 bg-blue-100"></div>
                  <div className="absolute top-2 right-2 w-2 h-2 bg-yellow-400 rounded-full"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Popular with travelers from Vietnam */}
      <section className="py-12 bg-white">
        <div className="max-w-6xl mx-auto px-4">
          <h2 className="text-2xl font-bold mb-6">Phổ biến với du khách từ Việt Nam</h2>
          
          <div className="border-b mb-6">
            <button className="px-4 py-3 border-b-2 border-blue-600 text-blue-600 font-semibold hover:bg-blue-50 transition-colors">Thành phố trong nước</button>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-5 gap-x-8 gap-y-4">
            {['Khách sạn Hà Nội', 'Khách sạn Nha Trang', 'Khách sạn Sapa', 'Khách sạn Hải Phòng', 'Khách sạn Côn Đảo',
              'Khách sạn Đà Lạt', 'Khách sạn Huế', 'Khách sạn Phú Quốc', 'Khách sạn Ninh Bình', 'Khách sạn Hạ Long'].map((hotel, index) => (
              <a key={index} href="#" className="text-blue-600 hover:underline text-sm hover:translate-x-1 transition-all duration-200 inline-block hover:text-blue-800">{hotel}</a>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
};

export default HomePage;


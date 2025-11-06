import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { roomTypesAPI } from '../services/api';
import RoomCard from '../components/RoomCard';
import LoadingSpinner from '../components/LoadingSpinner';
import { toast } from 'react-toastify';

const SearchPage = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const [rooms, setRooms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    city: searchParams.get('city') || '',
    checkin: searchParams.get('checkin') || '',
    checkout: searchParams.get('checkout') || '',
    guests: searchParams.get('guests') || 2,
    min_price: '',
    max_price: '',
  });

  useEffect(() => {
    loadRooms();
  }, [searchParams]);

  const loadRooms = async () => {
    setLoading(true);
    try {
      const params = {
        city: filters.city,
        max_guests: filters.guests,
        min_price: filters.min_price,
        max_price: filters.max_price,
      };

      const response = await roomTypesAPI.getAll(params);
      setRooms(response.data.data);
    } catch (error) {
      console.error('Error loading rooms:', error);
      toast.error('Lỗi khi tải danh sách phòng');
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (e) => {
    setFilters({
      ...filters,
      [e.target.name]: e.target.value,
    });
  };

  const handleSearch = (e) => {
    e.preventDefault();
    const params = new URLSearchParams();
    Object.keys(filters).forEach((key) => {
      if (filters[key]) {
        params.set(key, filters[key]);
      }
    });
    setSearchParams(params);
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Search Header */}
        <div className="bg-white rounded-lg shadow-sm p-6 mb-6">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">Tìm kiếm phòng</h1>
          
          <form onSubmit={handleSearch} className="grid grid-cols-1 md:grid-cols-6 gap-4">
            <input
              type="text"
              name="city"
              placeholder="Thành phố"
              value={filters.city}
              onChange={handleFilterChange}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            />
            <input
              type="date"
              name="checkin"
              value={filters.checkin}
              onChange={handleFilterChange}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            />
            <input
              type="date"
              name="checkout"
              value={filters.checkout}
              onChange={handleFilterChange}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            />
            <input
              type="number"
              name="guests"
              placeholder="Số khách"
              value={filters.guests}
              onChange={handleFilterChange}
              min="1"
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            />
            <input
              type="number"
              name="min_price"
              placeholder="Giá tối thiểu"
              value={filters.min_price}
              onChange={handleFilterChange}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            />
            <input
              type="number"
              name="max_price"
              placeholder="Giá tối đa"
              value={filters.max_price}
              onChange={handleFilterChange}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
            />
            <button
              type="submit"
              className="md:col-span-6 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition"
            >
              <i className="fas fa-search mr-2"></i>
              Tìm kiếm
            </button>
          </form>
        </div>

        {/* Results */}
        {loading ? (
          <div className="py-12">
            <LoadingSpinner size="lg" />
          </div>
        ) : rooms.length === 0 ? (
          <div className="bg-white rounded-lg shadow-sm p-12 text-center">
            <i className="fas fa-search text-6xl text-gray-400 mb-4"></i>
            <h3 className="text-xl font-semibold text-gray-900 mb-2">
              Không tìm thấy phòng
            </h3>
            <p className="text-gray-600">Vui lòng thử lại với các điều kiện khác</p>
          </div>
        ) : (
          <>
            <div className="mb-4">
              <p className="text-gray-700">
                Tìm thấy <span className="font-semibold">{rooms.length}</span> kết quả
              </p>
            </div>
            <div className="space-y-6">
              {rooms.map((room) => (
                <RoomCard
                  key={room.id}
                  room={room}
                  checkin={filters.checkin}
                  checkout={filters.checkout}
                />
              ))}
            </div>
          </>
        )}
      </div>
    </div>
  );
};

export default SearchPage;


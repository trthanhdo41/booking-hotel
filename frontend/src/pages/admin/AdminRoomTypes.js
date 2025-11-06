import React, { useState, useEffect } from 'react';
import { roomTypesAPI, hotelsAPI } from '../../services/api';
import { formatPrice } from '../../utils/helpers';
import LoadingSpinner from '../../components/LoadingSpinner';
import { toast } from 'react-toastify';

const AMENITY_OPTIONS = [
  'Wi-Fi miễn phí',
  'Điều hòa',
  'Tivi',
  'Tủ lạnh',
  'Bồn tắm',
  'Vòi sen',
  'Ban công',
  'View đẹp',
  'Minibar',
  'Két sắt',
  'Bàn làm việc',
  'Máy sấy tóc',
  'Dép đi trong phòng',
  'Đồ vệ sinh miễn phí',
];

const AdminRoomTypes = () => {
  const [roomTypes, setRoomTypes] = useState([]);
  const [hotels, setHotels] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingRoom, setEditingRoom] = useState(null);
  const [formData, setFormData] = useState({
    hotel_id: '',
    name: '',
    description: '',
    price: '',
    max_guests: 2,
    size: '',
    amenities: [],
    images: '',
  });

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    setLoading(true);
    try {
      const [roomsRes, hotelsRes] = await Promise.all([
        roomTypesAPI.getAll(),
        hotelsAPI.getAll(),
      ]);
      setRoomTypes(roomsRes.data.data);
      setHotels(hotelsRes.data.data);
    } catch (error) {
      console.error('Error loading data:', error);
      toast.error('Lỗi khi tải dữ liệu');
    } finally {
      setLoading(false);
    }
  };

  const handleOpenModal = (room = null) => {
    if (room) {
      setEditingRoom(room);
      const amenitiesArray = room.amenities ? 
        (typeof room.amenities === 'string' ? JSON.parse(room.amenities) : room.amenities) : [];
      const imagesString = room.images ? 
        (Array.isArray(room.images) ? room.images.join(', ') : room.images) : '';
      
      setFormData({
        hotel_id: room.hotel_id,
        name: room.name,
        description: room.description || '',
        price: room.price,
        max_guests: room.max_guests || 2,
        size: room.size || '',
        amenities: amenitiesArray,
        images: imagesString,
      });
    } else {
      setEditingRoom(null);
      setFormData({
        hotel_id: '',
        name: '',
        description: '',
        price: '',
        max_guests: 2,
        size: '',
        amenities: [],
        images: '',
      });
    }
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setEditingRoom(null);
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleAmenityToggle = (amenity) => {
    setFormData({
      ...formData,
      amenities: formData.amenities.includes(amenity)
        ? formData.amenities.filter(a => a !== amenity)
        : [...formData.amenities, amenity],
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!formData.hotel_id || !formData.name || !formData.price || !formData.max_guests) {
      toast.error('Vui lòng nhập đầy đủ thông tin bắt buộc');
      return;
    }

    const payload = {
      ...formData,
      images: formData.images ? formData.images.split(',').map(url => url.trim()).filter(url => url) : [],
    };

    try {
      if (editingRoom) {
        await roomTypesAPI.update(editingRoom.id, payload);
        toast.success('Cập nhật loại phòng thành công');
      } else {
        await roomTypesAPI.create(payload);
        toast.success('Thêm loại phòng thành công');
      }
      handleCloseModal();
      loadData();
    } catch (error) {
      console.error('Save room type error:', error);
      toast.error(error.response?.data?.message || 'Lỗi khi lưu loại phòng');
    }
  };

  const handleDelete = async (id) => {
    if (!window.confirm('Bạn có chắc muốn xóa loại phòng này?')) {
      return;
    }

    try {
      await roomTypesAPI.delete(id);
      toast.success('Xóa loại phòng thành công');
      loadData();
    } catch (error) {
      console.error('Delete room type error:', error);
      toast.error('Không thể xóa loại phòng');
    }
  };

  if (loading) {
    return <LoadingSpinner fullPage />;
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-gray-900">Quản lý loại phòng</h1>
        <button
          onClick={() => handleOpenModal()}
          className="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition"
        >
          <i className="fas fa-plus mr-2"></i>
          Thêm loại phòng
        </button>
      </div>

      {/* Room Types Table */}
      <div className="bg-white rounded-lg shadow-sm overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên phòng</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Khách sạn</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Giá</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số khách</th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiện nghi</th>
              <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hành động</th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {roomTypes.map((room) => {
              const amenities = room.amenities ? 
                (typeof room.amenities === 'string' ? JSON.parse(room.amenities) : room.amenities) : [];
              
              return (
                <tr key={room.id}>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{room.id}</td>
                  <td className="px-6 py-4">
                    <div className="font-semibold text-gray-900">{room.name}</div>
                    {room.size && <div className="text-sm text-gray-500">{room.size}</div>}
                  </td>
                  <td className="px-6 py-4">
                    <div className="text-sm text-gray-900">{room.hotel_name}</div>
                    <div className="text-sm text-gray-500">{room.hotel_city}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-primary">
                    {formatPrice(room.price)}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <i className="fas fa-users text-primary mr-2"></i>
                    {room.max_guests}
                  </td>
                  <td className="px-6 py-4 text-sm text-gray-700">
                    {amenities.slice(0, 3).join(', ')}
                    {amenities.length > 3 && ` +${amenities.length - 3}`}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-center text-sm">
                    <button
                      onClick={() => handleOpenModal(room)}
                      className="text-primary hover:text-primary-dark mr-3"
                    >
                      <i className="fas fa-edit"></i>
                    </button>
                    <button
                      onClick={() => handleDelete(room.id)}
                      className="text-danger hover:text-red-700"
                    >
                      <i className="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div className="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
              <h3 className="text-xl font-semibold">
                {editingRoom ? 'Sửa loại phòng' : 'Thêm loại phòng mới'}
              </h3>
              <button onClick={handleCloseModal} className="text-gray-500 hover:text-gray-700">
                <i className="fas fa-times text-xl"></i>
              </button>
            </div>

            <form onSubmit={handleSubmit} className="p-6 space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Khách sạn <span className="text-danger">*</span>
                  </label>
                  <select
                    name="hotel_id"
                    value={formData.hotel_id}
                    onChange={handleChange}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    required
                  >
                    <option value="">-- Chọn khách sạn --</option>
                    {hotels.map(hotel => (
                      <option key={hotel.id} value={hotel.id}>
                        {hotel.name} - {hotel.city}
                      </option>
                    ))}
                  </select>
                </div>

                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Tên loại phòng <span className="text-danger">*</span>
                  </label>
                  <input
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    placeholder="VD: Deluxe Twin Room"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Giá (VNĐ/đêm) <span className="text-danger">*</span>
                  </label>
                  <input
                    type="number"
                    name="price"
                    value={formData.price}
                    onChange={handleChange}
                    min="0"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    <i className="fas fa-users text-primary mr-2"></i>
                    Số lượng khách tối đa <span className="text-danger">*</span>
                  </label>
                  <input
                    type="number"
                    name="max_guests"
                    value={formData.max_guests}
                    onChange={handleChange}
                    min="1"
                    max="10"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    required
                  />
                </div>

                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Diện tích (VD: 25m²)
                  </label>
                  <input
                    type="text"
                    name="size"
                    value={formData.size}
                    onChange={handleChange}
                    placeholder="25m²"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  />
                </div>

                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Mô tả
                  </label>
                  <textarea
                    name="description"
                    value={formData.description}
                    onChange={handleChange}
                    rows="3"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  ></textarea>
                </div>

                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-3">
                    <i className="fas fa-concierge-bell text-primary mr-2"></i>
                    Tiện nghi
                  </label>
                  <div className="grid grid-cols-2 md:grid-cols-3 gap-2">
                    {AMENITY_OPTIONS.map((amenity) => (
                      <label
                        key={amenity}
                        className="flex items-center space-x-2 p-2 border rounded hover:bg-gray-50 cursor-pointer"
                      >
                        <input
                          type="checkbox"
                          checked={formData.amenities.includes(amenity)}
                          onChange={() => handleAmenityToggle(amenity)}
                          className="rounded text-primary focus:ring-primary"
                        />
                        <span className="text-sm">{amenity}</span>
                      </label>
                    ))}
                  </div>
                </div>

                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    <i className="fas fa-images text-primary mr-2"></i>
                    Ảnh phòng (URLs, phân cách bằng dấu phẩy)
                  </label>
                  <textarea
                    name="images"
                    value={formData.images}
                    onChange={handleChange}
                    rows="2"
                    placeholder="https://example.com/room1.jpg, https://example.com/room2.jpg"
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  ></textarea>
                  <p className="text-xs text-gray-500 mt-1">Nhập các URL ảnh, phân cách bằng dấu phẩy</p>
                </div>
              </div>

              <div className="flex justify-end space-x-3 pt-4 border-t">
                <button
                  type="button"
                  onClick={handleCloseModal}
                  className="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                >
                  Hủy
                </button>
                <button
                  type="submit"
                  className="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition"
                >
                  {editingRoom ? 'Cập nhật' : 'Thêm mới'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default AdminRoomTypes;


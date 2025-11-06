import React from 'react';
import { Link } from 'react-router-dom';
import { formatPrice, safeJSONParse } from '../utils/helpers';

const RoomCard = ({ room, checkin, checkout }) => {
  const images = safeJSONParse(room.images, []);
  const amenities = safeJSONParse(room.amenities, []);
  const mainImage = images[0] || 'https://via.placeholder.com/400x250?text=Room+Image';

  return (
    <div className="bg-white rounded-lg shadow-sm hover:shadow-md transition border border-gray-200 overflow-hidden">
      <div className="flex flex-col md:flex-row">
        {/* Image */}
        <div className="md:w-1/3">
          <img
            src={mainImage}
            alt={room.name}
            className="w-full h-48 md:h-full object-cover"
          />
        </div>

        {/* Content */}
        <div className="md:w-2/3 p-4">
          <div className="flex justify-between items-start mb-2">
            <div>
              <h3 className="text-xl font-semibold text-gray-900">{room.name}</h3>
              <p className="text-sm text-gray-600 flex items-center mt-1">
                <i className="fas fa-hotel text-primary mr-2"></i>
                {room.hotel_name}
              </p>
              <p className="text-sm text-gray-600 flex items-center mt-1">
                <i className="fas fa-map-marker-alt text-primary mr-2"></i>
                {room.hotel_address}, {room.hotel_city}
              </p>
            </div>
            <div className="flex items-center space-x-1">
              <i className="fas fa-star text-secondary"></i>
              <span className="font-semibold">{room.hotel_rating || 5.0}</span>
            </div>
          </div>

          {/* Description */}
          {room.description && (
            <p className="text-sm text-gray-600 mb-3 line-clamp-2">{room.description}</p>
          )}

          {/* Amenities */}
          {amenities.length > 0 && (
            <div className="flex flex-wrap gap-2 mb-3">
              {amenities.slice(0, 5).map((amenity, index) => (
                <span
                  key={index}
                  className="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"
                >
                  <i className={`fas fa-${getAmenityIcon(amenity)} mr-1`}></i>
                  {amenity}
                </span>
              ))}
              {amenities.length > 5 && (
                <span className="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">
                  +{amenities.length - 5} tiện nghi
                </span>
              )}
            </div>
          )}

          {/* Room Info */}
          <div className="flex items-center space-x-4 text-sm text-gray-600 mb-3">
            <span>
              <i className="fas fa-users text-primary mr-1"></i>
              {room.max_guests} khách
            </span>
            {room.size && (
              <span>
                <i className="fas fa-expand text-primary mr-1"></i>
                {room.size}
              </span>
            )}
          </div>

          {/* Price & Booking */}
          <div className="flex justify-between items-center pt-3 border-t">
            <div>
              <p className="text-2xl font-bold text-primary">{formatPrice(room.price)}</p>
              <p className="text-xs text-gray-500">/ đêm</p>
            </div>
            <Link
              to={`/booking?room_type_id=${room.id}&checkin=${checkin || ''}&checkout=${checkout || ''}`}
              className="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition"
            >
              Đặt ngay
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

// Helper function để map amenity với icon
const getAmenityIcon = (amenity) => {
  const iconMap = {
    'Wi-Fi': 'wifi',
    'Điều hòa': 'snowflake',
    'Tivi': 'tv',
    'Tủ lạnh': 'refrigerator',
    'Bồn tắm': 'bath',
    'Ban công': 'home',
  };
  return iconMap[amenity] || 'check';
};

export default RoomCard;


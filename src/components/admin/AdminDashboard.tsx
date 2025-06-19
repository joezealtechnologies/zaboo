import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
  Car, 
  Plus, 
  Edit, 
  Trash2, 
  Image as ImageIcon, 
  Star,
  Eye,
  EyeOff,
  LogOut,
  Upload,
  X,
  Check,
  ChevronLeft,
  ChevronRight,
  Camera,
  Trash
} from 'lucide-react';
import { adminAPI, Vehicle } from '../../services/api';

interface AdminDashboardProps {
  user: any;
  onLogout: () => void;
}

const AdminDashboard: React.FC<AdminDashboardProps> = ({ user, onLogout }) => {
  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingVehicle, setEditingVehicle] = useState<Vehicle | null>(null);
  const [formData, setFormData] = useState({
    name: '',
    price: '',
    range_km: '',
    description: '',
    features: '',
    badge: '',
    badge_color: '',
    rating: 5,
    is_active: true
  });
  const [selectedImages, setSelectedImages] = useState<FileList | null>(null);
  const [previewImages, setPreviewImages] = useState<string[]>([]);
  const [currentImageIndex, setCurrentImageIndex] = useState<{ [key: number]: number }>({});

  useEffect(() => {
    fetchVehicles();
  }, []);

  const fetchVehicles = async () => {
    try {
      const response = await adminAPI.vehicles.getAll();
      setVehicles(response.data);
      
      // Initialize image indexes
      const indexes: { [key: number]: number } = {};
      response.data.forEach(vehicle => {
        indexes[vehicle.id] = 0;
      });
      setCurrentImageIndex(indexes);
    } catch (error) {
      console.error('Error fetching vehicles:', error);
    } finally {
      setLoading(false);
    }
  };

  const nextImage = (vehicleId: number, totalImages: number) => {
    setCurrentImageIndex(prev => ({
      ...prev,
      [vehicleId]: (prev[vehicleId] + 1) % totalImages
    }));
  };

  const prevImage = (vehicleId: number, totalImages: number) => {
    setCurrentImageIndex(prev => ({
      ...prev,
      [vehicleId]: prev[vehicleId] === 0 ? totalImages - 1 : prev[vehicleId] - 1
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      const submitData = new FormData();
      Object.entries(formData).forEach(([key, value]) => {
        submitData.append(key, value.toString());
      });

      if (selectedImages) {
        Array.from(selectedImages).forEach(file => {
          submitData.append('images', file);
        });
      }

      if (editingVehicle) {
        await adminAPI.vehicles.update(editingVehicle.id, submitData);
      } else {
        await adminAPI.vehicles.create(submitData);
      }

      await fetchVehicles();
      resetForm();
      setShowModal(false);
    } catch (error) {
      console.error('Error saving vehicle:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (window.confirm('Are you sure you want to delete this vehicle?')) {
      try {
        await adminAPI.vehicles.delete(id);
        await fetchVehicles();
      } catch (error) {
        console.error('Error deleting vehicle:', error);
      }
    }
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = e.target.files;
    if (files) {
      setSelectedImages(files);
      
      // Create preview URLs
      const previews: string[] = [];
      const fileArray = Array.from(files);
      
      fileArray.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
          previews[index] = e.target?.result as string;
          if (previews.filter(p => p).length === fileArray.length) {
            setPreviewImages(previews);
          }
        };
        reader.readAsDataURL(file);
      });
    }
  };

  const removePreviewImage = (index: number) => {
    if (selectedImages) {
      const dt = new DataTransfer();
      const files = Array.from(selectedImages);
      
      files.forEach((file, i) => {
        if (i !== index) {
          dt.items.add(file);
        }
      });
      
      setSelectedImages(dt.files);
      setPreviewImages(prev => prev.filter((_, i) => i !== index));
    }
  };

  const resetForm = () => {
    setFormData({
      name: '',
      price: '',
      range_km: '',
      description: '',
      features: '',
      badge: '',
      badge_color: '',
      rating: 5,
      is_active: true
    });
    setSelectedImages(null);
    setPreviewImages([]);
    setEditingVehicle(null);
  };

  const openEditModal = (vehicle: Vehicle) => {
    setEditingVehicle(vehicle);
    setFormData({
      name: vehicle.name,
      price: vehicle.price,
      range_km: vehicle.range_km,
      description: vehicle.description || '',
      features: vehicle.features.join(', '),
      badge: vehicle.badge || '',
      badge_color: vehicle.badge_color || '',
      rating: vehicle.rating,
      is_active: vehicle.is_active
    });
    setShowModal(true);
  };

  const deleteVehicleImage = async (vehicleId: number, imageId: number) => {
    if (window.confirm('Are you sure you want to delete this image?')) {
      try {
        await adminAPI.vehicles.deleteImage(vehicleId, imageId);
        await fetchVehicles();
      } catch (error) {
        console.error('Error deleting image:', error);
      }
    }
  };

  const badgeColors = [
    { name: 'Red', value: 'bg-red-500' },
    { name: 'Green', value: 'bg-green-500' },
    { name: 'Blue', value: 'bg-blue-500' },
    { name: 'Yellow', value: 'bg-yellow-500' },
    { name: 'Purple', value: 'bg-purple-500' },
    { name: 'Brand Red', value: 'bg-brand-red' }
  ];

  if (loading && vehicles.length === 0) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <motion.div
          animate={{ rotate: 360 }}
          transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
          className="w-12 h-12 border-4 border-brand-red border-t-transparent rounded-full"
        />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center h-16">
            <div className="flex items-center space-x-4">
              <Car className="w-8 h-8 text-brand-red" />
              <h1 className="text-2xl font-montserrat font-bold text-brand-black">
                FaZona EV Admin
              </h1>
            </div>
            <div className="flex items-center space-x-4">
              <span className="text-gray-600">Welcome, {user.username}</span>
              <motion.button
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                onClick={onLogout}
                className="flex items-center space-x-2 text-gray-600 hover:text-brand-red transition-colors"
              >
                <LogOut className="w-5 h-5" />
                <span>Logout</span>
              </motion.button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Actions Bar */}
        <div className="flex justify-between items-center mb-8">
          <h2 className="text-3xl font-montserrat font-bold text-brand-black">
            Vehicle Management
          </h2>
          <motion.button
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            onClick={() => {
              resetForm();
              setShowModal(true);
            }}
            className="btn-primary flex items-center space-x-2"
          >
            <Plus className="w-5 h-5" />
            <span>Add Vehicle</span>
          </motion.button>
        </div>

        {/* Vehicles Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          {vehicles.map((vehicle, index) => (
            <motion.div
              key={vehicle.id}
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.1 }}
              className="bg-white rounded-2xl shadow-lg overflow-hidden"
            >
              {/* Vehicle Image Carousel */}
              <div className="relative h-48">
                {vehicle.images.length > 0 ? (
                  <>
                    <img
                      src={`http://localhost:5000${vehicle.images[currentImageIndex[vehicle.id] || 0]}`}
                      alt={vehicle.name}
                      className="w-full h-full object-cover"
                    />
                    
                    {/* Navigation Arrows */}
                    {vehicle.images.length > 1 && (
                      <>
                        <button
                          onClick={() => prevImage(vehicle.id, vehicle.images.length)}
                          className="absolute left-2 top-1/2 transform -translate-y-1/2 w-8 h-8 bg-black/50 text-white rounded-full flex items-center justify-center hover:bg-black/70 transition-colors"
                        >
                          <ChevronLeft className="w-4 h-4" />
                        </button>
                        <button
                          onClick={() => nextImage(vehicle.id, vehicle.images.length)}
                          className="absolute right-2 top-1/2 transform -translate-y-1/2 w-8 h-8 bg-black/50 text-white rounded-full flex items-center justify-center hover:bg-black/70 transition-colors"
                        >
                          <ChevronRight className="w-4 h-4" />
                        </button>
                      </>
                    )}

                    {/* Image Counter */}
                    <div className="absolute bottom-2 right-2 bg-black/50 text-white px-2 py-1 rounded-full text-xs flex items-center space-x-1">
                      <Camera className="w-3 h-3" />
                      <span>{(currentImageIndex[vehicle.id] || 0) + 1}/{vehicle.images.length}</span>
                    </div>

                    {/* Image Indicators */}
                    {vehicle.images.length > 1 && (
                      <div className="absolute bottom-2 left-1/2 transform -translate-x-1/2 flex space-x-1">
                        {vehicle.images.map((_, imgIndex) => (
                          <button
                            key={imgIndex}
                            onClick={() => setCurrentImageIndex(prev => ({ ...prev, [vehicle.id]: imgIndex }))}
                            className={`w-2 h-2 rounded-full transition-all ${
                              imgIndex === (currentImageIndex[vehicle.id] || 0)
                                ? 'bg-white scale-125'
                                : 'bg-white/50'
                            }`}
                          />
                        ))}
                      </div>
                    )}
                  </>
                ) : (
                  <div className="w-full h-full bg-gray-200 flex items-center justify-center">
                    <ImageIcon className="w-12 h-12 text-gray-400" />
                  </div>
                )}
                
                {/* Status Badge */}
                <div className="absolute top-3 left-3">
                  <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                    vehicle.is_active 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-red-100 text-red-800'
                  }`}>
                    {vehicle.is_active ? 'Active' : 'Inactive'}
                  </span>
                </div>

                {/* Badge */}
                {vehicle.badge && (
                  <div className="absolute top-3 right-3">
                    <span className={`px-3 py-1 rounded-full text-xs font-semibold text-white ${vehicle.badge_color}`}>
                      {vehicle.badge}
                    </span>
                  </div>
                )}
              </div>

              {/* Vehicle Info */}
              <div className="p-6">
                <div className="flex justify-between items-start mb-2">
                  <h3 className="text-xl font-montserrat font-bold text-brand-black">
                    {vehicle.name}
                  </h3>
                  <div className="flex items-center space-x-1">
                    {[...Array(5)].map((_, i) => (
                      <Star
                        key={i}
                        className={`w-4 h-4 ${
                          i < vehicle.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'
                        }`}
                      />
                    ))}
                  </div>
                </div>

                <p className="text-2xl font-bold text-brand-red mb-2">{vehicle.price}</p>
                <p className="text-gray-600 mb-4">{vehicle.range_km}</p>

                {/* Features */}
                <div className="flex flex-wrap gap-2 mb-4">
                  {vehicle.features.slice(0, 2).map((feature, idx) => (
                    <span
                      key={idx}
                      className="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full"
                    >
                      {feature}
                    </span>
                  ))}
                  {vehicle.features.length > 2 && (
                    <span className="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">
                      +{vehicle.features.length - 2} more
                    </span>
                  )}
                </div>

                {/* Actions */}
                <div className="flex space-x-2">
                  <motion.button
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    onClick={() => openEditModal(vehicle)}
                    className="flex-1 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors flex items-center justify-center space-x-2"
                  >
                    <Edit className="w-4 h-4" />
                    <span>Edit</span>
                  </motion.button>
                  <motion.button
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    onClick={() => handleDelete(vehicle.id)}
                    className="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors flex items-center justify-center space-x-2"
                  >
                    <Trash2 className="w-4 h-4" />
                    <span>Delete</span>
                  </motion.button>
                </div>
              </div>
            </motion.div>
          ))}
        </div>
      </main>

      {/* Add/Edit Modal */}
      <AnimatePresence>
        {showModal && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            onClick={() => setShowModal(false)}
          >
            <motion.div
              initial={{ scale: 0.8, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.8, opacity: 0 }}
              className="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              {/* Modal Header */}
              <div className="bg-gradient-to-r from-brand-red to-red-600 text-white p-6 rounded-t-3xl">
                <div className="flex justify-between items-center">
                  <h2 className="text-2xl font-montserrat font-bold">
                    {editingVehicle ? 'Edit Vehicle' : 'Add New Vehicle'}
                  </h2>
                  <button
                    onClick={() => setShowModal(false)}
                    className="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors"
                  >
                    <X className="w-5 h-5" />
                  </button>
                </div>
              </div>

              {/* Modal Form */}
              <form onSubmit={handleSubmit} className="p-6 space-y-6">
                <div className="grid md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-sm font-semibold text-brand-black mb-2">
                      Vehicle Name *
                    </label>
                    <input
                      type="text"
                      value={formData.name}
                      onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                      required
                      className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent"
                      placeholder="e.g., Premium Long Range"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-semibold text-brand-black mb-2">
                      Price *
                    </label>
                    <input
                      type="text"
                      value={formData.price}
                      onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                      required
                      className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent"
                      placeholder="e.g., ₦20 million"
                    />
                  </div>
                </div>

                <div className="grid md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-sm font-semibold text-brand-black mb-2">
                      Range *
                    </label>
                    <input
                      type="text"
                      value={formData.range_km}
                      onChange={(e) => setFormData({ ...formData, range_km: e.target.value })}
                      required
                      className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent"
                      placeholder="e.g., 200km per charge"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-semibold text-brand-black mb-2">
                      Rating
                    </label>
                    <select
                      value={formData.rating}
                      onChange={(e) => setFormData({ ...formData, rating: parseInt(e.target.value) })}
                      className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent"
                    >
                      {[1, 2, 3, 4, 5].map(rating => (
                        <option key={rating} value={rating}>{rating} Star{rating > 1 ? 's' : ''}</option>
                      ))}
                    </select>
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-semibold text-brand-black mb-2">
                    Description
                  </label>
                  <textarea
                    value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    rows={3}
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent resize-none"
                    placeholder="Vehicle description..."
                  />
                </div>

                <div>
                  <label className="block text-sm font-semibold text-brand-black mb-2">
                    Features (comma separated)
                  </label>
                  <input
                    type="text"
                    value={formData.features}
                    onChange={(e) => setFormData({ ...formData, features: e.target.value })}
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent"
                    placeholder="e.g., Fast Charging, Premium Interior, Advanced Safety"
                  />
                </div>

                <div className="grid md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-sm font-semibold text-brand-black mb-2">
                      Badge Text
                    </label>
                    <input
                      type="text"
                      value={formData.badge}
                      onChange={(e) => setFormData({ ...formData, badge: e.target.value })}
                      className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent"
                      placeholder="e.g., Most Popular, Best Value"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-semibold text-brand-black mb-2">
                      Badge Color
                    </label>
                    <select
                      value={formData.badge_color}
                      onChange={(e) => setFormData({ ...formData, badge_color: e.target.value })}
                      className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent"
                    >
                      <option value="">Select Color</option>
                      {badgeColors.map(color => (
                        <option key={color.value} value={color.value}>{color.name}</option>
                      ))}
                    </select>
                  </div>
                </div>

                <div className="flex items-center space-x-3">
                  <input
                    type="checkbox"
                    id="is_active"
                    checked={formData.is_active}
                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                    className="w-5 h-5 text-brand-red border-gray-300 rounded focus:ring-brand-red"
                  />
                  <label htmlFor="is_active" className="text-sm font-semibold text-brand-black">
                    Active (visible on website)
                  </label>
                </div>

                {/* Enhanced Image Upload */}
                <div>
                  <label className="block text-sm font-semibold text-brand-black mb-2">
                    Vehicle Images
                  </label>
                  
                  {/* Existing Images (for edit mode) */}
                  {editingVehicle && editingVehicle.images.length > 0 && (
                    <div className="mb-4">
                      <h4 className="text-sm font-medium text-gray-700 mb-2">Current Images:</h4>
                      <div className="grid grid-cols-4 gap-3">
                        {editingVehicle.images.map((image, index) => (
                          <div key={index} className="relative group">
                            <img
                              src={`http://localhost:5000${image}`}
                              alt={`${editingVehicle.name} ${index + 1}`}
                              className="w-full h-20 object-cover rounded-lg"
                            />
                            <button
                              type="button"
                              onClick={() => deleteVehicleImage(editingVehicle.id, index + 1)}
                              className="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                              <Trash className="w-3 h-3" />
                            </button>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* New Image Upload */}
                  <div className="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center">
                    <input
                      type="file"
                      multiple
                      accept="image/*"
                      onChange={handleImageChange}
                      className="hidden"
                      id="image-upload"
                    />
                    <label
                      htmlFor="image-upload"
                      className="cursor-pointer flex flex-col items-center space-y-2"
                    >
                      <Upload className="w-12 h-12 text-gray-400" />
                      <span className="text-gray-600">Click to upload new images</span>
                      <span className="text-sm text-gray-500">PNG, JPG up to 5MB each • Multiple files supported</span>
                    </label>
                  </div>

                  {/* New Image Previews */}
                  {previewImages.length > 0 && (
                    <div className="mt-4">
                      <h4 className="text-sm font-medium text-gray-700 mb-2">New Images to Upload:</h4>
                      <div className="grid grid-cols-4 gap-3">
                        {previewImages.map((preview, index) => (
                          <div key={index} className="relative group">
                            <img
                              src={preview}
                              alt={`Preview ${index + 1}`}
                              className="w-full h-20 object-cover rounded-lg"
                            />
                            <button
                              type="button"
                              onClick={() => removePreviewImage(index)}
                              className="absolute top-1 right-1 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >
                              <X className="w-3 h-3" />
                            </button>
                            {index === 0 && (
                              <div className="absolute bottom-1 left-1 bg-green-500 text-white text-xs px-1 rounded">
                                Primary
                              </div>
                            )}
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>

                {/* Submit Button */}
                <motion.button
                  type="submit"
                  disabled={loading}
                  whileHover={{ scale: loading ? 1 : 1.02 }}
                  whileTap={{ scale: loading ? 1 : 0.98 }}
                  className="w-full btn-primary flex items-center justify-center space-x-2 disabled:opacity-50"
                >
                  {loading ? (
                    <motion.div
                      animate={{ rotate: 360 }}
                      transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
                      className="w-5 h-5 border-2 border-white border-t-transparent rounded-full"
                    />
                  ) : (
                    <>
                      <Check className="w-5 h-5" />
                      <span>{editingVehicle ? 'Update Vehicle' : 'Create Vehicle'}</span>
                    </>
                  )}
                </motion.button>
              </form>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default AdminDashboard;
import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'react-intersection-observer';
import { Battery, Car, Truck, Mail, Star, ChevronLeft, ChevronRight, Eye, Camera } from 'lucide-react';
import { vehicleAPI, Vehicle } from '../services/api';
import QuoteModal from './QuoteModal';
import ImageLightbox from './ImageLightbox';

const Vehicles = () => {
  const [ref, inView] = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });

  const [vehicles, setVehicles] = useState<Vehicle[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedVehicle, setSelectedVehicle] = useState<{
    name: string;
    price: string;
    image?: string;
  } | null>(null);
  const [imageIndexes, setImageIndexes] = useState<{ [key: number]: number }>({});
  
  // Lightbox state
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxImages, setLightboxImages] = useState<string[]>([]);
  const [lightboxInitialIndex, setLightboxInitialIndex] = useState(0);
  const [lightboxVehicleName, setLightboxVehicleName] = useState('');

  useEffect(() => {
    fetchVehicles();
  }, []);

  const fetchVehicles = async () => {
    try {
      const response = await vehicleAPI.getAll();
      setVehicles(response.data);
      
      // Initialize image indexes
      const indexes: { [key: number]: number } = {};
      response.data.forEach(vehicle => {
        indexes[vehicle.id] = 0;
      });
      setImageIndexes(indexes);
    } catch (error) {
      console.error('Error fetching vehicles:', error);
    } finally {
      setLoading(false);
    }
  };

  const nextImage = (vehicleId: number, totalImages: number) => {
    setImageIndexes(prev => ({
      ...prev,
      [vehicleId]: (prev[vehicleId] + 1) % totalImages
    }));
  };

  const prevImage = (vehicleId: number, totalImages: number) => {
    setImageIndexes(prev => ({
      ...prev,
      [vehicleId]: prev[vehicleId] === 0 ? totalImages - 1 : prev[vehicleId] - 1
    }));
  };

  const openLightbox = (vehicle: Vehicle, initialIndex: number = 0) => {
    // Convert relative paths to full URLs for lightbox
    const fullImageUrls = vehicle.images.map(img => `http://localhost:5000${img}`);
    setLightboxImages(fullImageUrls);
    setLightboxInitialIndex(initialIndex);
    setLightboxVehicleName(vehicle.name);
    setLightboxOpen(true);
  };

  const closeLightbox = () => {
    setLightboxOpen(false);
    setLightboxImages([]);
    setLightboxInitialIndex(0);
    setLightboxVehicleName('');
  };

  const handleLightboxIndexChange = (newIndex: number) => {
    setLightboxInitialIndex(newIndex);
  };

  const tricycle = {
    name: 'Electric Tricycle (EV Keke)',
    description: 'Built for short-distance commutes and intra-city delivery',
    image: '/fazona/tricicle.jpg',
    features: ['Commercial Use', 'Cargo Capacity', 'Low Operating Cost', 'Durable Build'],
    price: 'Contact for Pricing'
  };

  const handleGetQuote = (vehicleName: string, price: string, image?: string) => {
    setSelectedVehicle({ name: vehicleName, price, image });
  };

  const getBadgeColorClass = (badgeColor: string) => {
    const colorMap: { [key: string]: string } = {
      'bg-red-500': 'bg-red-500',
      'bg-green-500': 'bg-green-500',
      'bg-blue-500': 'bg-blue-500',
      'bg-yellow-500': 'bg-yellow-500',
      'bg-purple-500': 'bg-purple-500',
      'bg-brand-red': 'bg-brand-red'
    };
    return colorMap[badgeColor] || 'bg-brand-red';
  };

  const openTricycleLightbox = () => {
    setLightboxImages([tricycle.image]);
    setLightboxInitialIndex(0);
    setLightboxVehicleName(tricycle.name);
    setLightboxOpen(true);
  };

  if (loading) {
    return (
      <section id="vehicles" className="py-20 bg-gradient-to-br from-gray-50 to-white">
        <div className="container-max section-padding">
          <div className="flex items-center justify-center h-64">
            <motion.div
              animate={{ rotate: 360 }}
              transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
              className="w-12 h-12 border-4 border-brand-red border-t-transparent rounded-full"
            />
          </div>
        </div>
      </section>
    );
  }

  return (
    <section id="vehicles" className="py-20 bg-gradient-to-br from-gray-50 to-white">
      <div className="container-max section-padding">
        <motion.div
          ref={ref}
          initial={{ opacity: 0, y: 50 }}
          animate={inView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.8 }}
          className="text-center mb-16"
        >
          <motion.div
            animate={{ 
              backgroundPosition: ['0% 50%', '100% 50%', '0% 50%']
            }}
            transition={{ duration: 5, repeat: Infinity }}
            className="inline-block"
          >
            <h2 className="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
              Our <span className="gradient-text">Vehicle Lineup</span>
            </h2>
          </motion.div>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Choose from our range of premium electric vehicles designed to meet every need and budget.
          </p>
        </motion.div>

        {/* Electric Cars Grid */}
        <div className="grid md:grid-cols-2 lg:grid-cols-2 gap-8 mb-16">
          {vehicles.map((vehicle, index) => (
            <motion.div
              key={vehicle.id}
              initial={{ opacity: 0, y: 50 }}
              animate={inView ? { opacity: 1, y: 0 } : {}}
              transition={{ duration: 0.8, delay: index * 0.1 }}
              className="group relative bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500"
              whileHover={{ y: -10, scale: 1.02 }}
            >
              {/* Badge */}
              {vehicle.badge && (
                <motion.div 
                  className={`absolute top-6 left-6 ${getBadgeColorClass(vehicle.badge_color || 'bg-brand-red')} text-white px-4 py-2 rounded-full text-sm font-semibold z-20`}
                  animate={{ 
                    scale: [1, 1.05, 1],
                    rotate: [0, 2, -2, 0]
                  }}
                  transition={{ 
                    duration: 3, 
                    repeat: Infinity, 
                    ease: "easeInOut" 
                  }}
                >
                  {vehicle.badge}
                </motion.div>
              )}

              {/* Rating */}
              <div className="absolute top-6 right-6 flex items-center space-x-1 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full z-20">
                {[...Array(5)].map((_, i) => (
                  <Star
                    key={i}
                    className={`w-4 h-4 ${
                      i < vehicle.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'
                    }`}
                  />
                ))}
              </div>

              {/* Image Carousel */}
              <div className="relative h-64 overflow-hidden group">
                {vehicle.images.length > 0 ? (
                  <>
                    <motion.img
                      src={`http://localhost:5000${vehicle.images[imageIndexes[vehicle.id] || 0]}`}
                      alt={vehicle.name}
                      className="w-full h-full object-cover cursor-pointer transition-transform duration-500 group-hover:scale-110"
                      onClick={() => openLightbox(vehicle, imageIndexes[vehicle.id] || 0)}
                    />
                    
                    {/* View Gallery Overlay */}
                    <motion.div
                      initial={{ opacity: 0 }}
                      whileHover={{ opacity: 1 }}
                      className="absolute inset-0 bg-black/40 flex items-center justify-center cursor-pointer z-10"
                      onClick={() => openLightbox(vehicle, imageIndexes[vehicle.id] || 0)}
                    >
                      <motion.div
                        initial={{ scale: 0 }}
                        whileHover={{ scale: 1 }}
                        className="bg-white/90 backdrop-blur-sm rounded-full p-6 flex flex-col items-center space-y-2"
                      >
                        <Camera className="w-8 h-8 text-brand-red" />
                        <span className="text-brand-red font-semibold text-sm">View Gallery</span>
                        {vehicle.images.length > 1 && (
                          <span className="text-gray-600 text-xs">{vehicle.images.length} photos</span>
                        )}
                      </motion.div>
                    </motion.div>
                    
                    {/* Navigation Arrows */}
                    {vehicle.images.length > 1 && (
                      <>
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            prevImage(vehicle.id, vehicle.images.length);
                          }}
                          className="absolute left-3 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-colors z-20 opacity-0 group-hover:opacity-100"
                        >
                          <ChevronLeft className="w-5 h-5 text-brand-black" />
                        </button>
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            nextImage(vehicle.id, vehicle.images.length);
                          }}
                          className="absolute right-3 top-1/2 transform -translate-y-1/2 w-10 h-10 bg-white/80 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-colors z-20 opacity-0 group-hover:opacity-100"
                        >
                          <ChevronRight className="w-5 h-5 text-brand-black" />
                        </button>
                      </>
                    )}

                    {/* Image Counter */}
                    <div className="absolute bottom-3 right-3 bg-black/50 text-white px-3 py-1 rounded-full text-sm flex items-center space-x-1 z-20">
                      <Eye className="w-3 h-3" />
                      <span>{(imageIndexes[vehicle.id] || 0) + 1}/{vehicle.images.length}</span>
                    </div>

                    {/* Image Indicators */}
                    {vehicle.images.length > 1 && (
                      <div className="absolute bottom-3 left-1/2 transform -translate-x-1/2 flex space-x-2 z-20">
                        {vehicle.images.map((_, imgIndex) => (
                          <button
                            key={imgIndex}
                            onClick={(e) => {
                              e.stopPropagation();
                              setImageIndexes(prev => ({ ...prev, [vehicle.id]: imgIndex }));
                            }}
                            className={`w-2 h-2 rounded-full transition-all ${
                              imgIndex === (imageIndexes[vehicle.id] || 0)
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
                    <Car className="w-12 h-12 text-gray-400" />
                  </div>
                )}
                
                <div className="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent" />
              </div>

              {/* Content */}
              <div className="p-8">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-2xl font-montserrat font-bold text-brand-black">
                    {vehicle.name}
                  </h3>
                  <div className="flex items-center space-x-2 text-brand-red">
                    <Battery className="w-5 h-5" />
                    <span className="font-semibold">{vehicle.range_km}</span>
                  </div>
                </div>

                <div className="text-3xl font-montserrat font-bold text-brand-red mb-6">
                  {vehicle.price}
                </div>

                {/* Description */}
                {vehicle.description && (
                  <p className="text-gray-600 mb-4 line-clamp-2">{vehicle.description}</p>
                )}

                {/* Features */}
                <div className="grid grid-cols-2 gap-3 mb-6">
                  {vehicle.features.slice(0, 4).map((feature, idx) => (
                    <motion.div 
                      key={idx} 
                      className="flex items-center space-x-2 text-sm text-gray-600"
                      initial={{ opacity: 0, x: -10 }}
                      animate={inView ? { opacity: 1, x: 0 } : {}}
                      transition={{ delay: 0.5 + idx * 0.1 }}
                    >
                      <div className="w-2 h-2 bg-brand-red rounded-full" />
                      <span>{feature}</span>
                    </motion.div>
                  ))}
                </div>

                {/* CTA */}
                <motion.button
                  whileHover={{ 
                    scale: 1.05,
                    boxShadow: "0 15px 35px rgba(214, 0, 28, 0.3)"
                  }}
                  whileTap={{ scale: 0.95 }}
                  onClick={() => handleGetQuote(
                    vehicle.name, 
                    vehicle.price, 
                    vehicle.images.length > 0 ? `http://localhost:5000${vehicle.images[0]}` : undefined
                  )}
                  className="w-full btn-primary flex items-center justify-center space-x-2"
                >
                  <Mail className="w-5 h-5" />
                  <span>Get Quote</span>
                </motion.button>
              </div>
            </motion.div>
          ))}
        </div>

        {/* Electric Tricycle Section */}
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          animate={inView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.8, delay: 0.4 }}
          className="bg-gradient-to-r from-white to-gray-50 rounded-3xl overflow-hidden shadow-xl"
        >
          <div className="grid lg:grid-cols-2 gap-0">
            {/* Image */}
            <div className="relative h-80 lg:h-auto overflow-hidden group">
              <motion.img
                src={tricycle.image}
                alt={tricycle.name}
                className="w-full h-full object-cover cursor-pointer transition-transform duration-500 group-hover:scale-105"
                onClick={openTricycleLightbox}
              />
              
              {/* View Image Overlay */}
              <motion.div
                initial={{ opacity: 0 }}
                whileHover={{ opacity: 1 }}
                className="absolute inset-0 bg-black/40 flex items-center justify-center cursor-pointer z-10"
                onClick={openTricycleLightbox}
              >
                <motion.div
                  initial={{ scale: 0 }}
                  whileHover={{ scale: 1 }}
                  className="bg-white/90 backdrop-blur-sm rounded-full p-6 flex flex-col items-center space-y-2"
                >
                  <Camera className="w-8 h-8 text-brand-red" />
                  <span className="text-brand-red font-semibold text-sm">View Image</span>
                </motion.div>
              </motion.div>
              
              <div className="absolute inset-0 bg-gradient-to-r from-brand-red/20 to-transparent" />
              
              {/* Floating Badge */}
              <motion.div
                animate={{ 
                  y: [0, -10, 0],
                  rotate: [0, 5, -5, 0]
                }}
                transition={{ 
                  duration: 4, 
                  repeat: Infinity, 
                  ease: "easeInOut" 
                }}
                className="absolute top-6 left-6 bg-yellow-400 text-black px-4 py-2 rounded-full text-sm font-bold z-20"
              >
                Commercial Grade
              </motion.div>
            </div>

            {/* Content */}
            <div className="p-12 flex flex-col justify-center">
              <div className="flex items-center space-x-3 mb-6">
                <motion.div 
                  className="w-12 h-12 bg-brand-red rounded-full flex items-center justify-center"
                  animate={{ rotate: [0, 360] }}
                  transition={{ duration: 4, repeat: Infinity, ease: "linear" }}
                >
                  <Truck className="w-6 h-6 text-white" />
                </motion.div>
                <h3 className="text-3xl font-montserrat font-bold text-brand-black">
                  {tricycle.name}
                </h3>
              </div>

              <p className="text-xl text-gray-600 mb-8 leading-relaxed">
                {tricycle.description}
              </p>

              {/* Features */}
              <div className="grid grid-cols-2 gap-4 mb-8">
                {tricycle.features.map((feature, idx) => (
                  <motion.div 
                    key={idx} 
                    className="flex items-center space-x-3"
                    initial={{ opacity: 0, x: -20 }}
                    animate={inView ? { opacity: 1, x: 0 } : {}}
                    transition={{ delay: 0.6 + idx * 0.1 }}
                  >
                    <div className="w-3 h-3 bg-brand-red rounded-full" />
                    <span className="text-gray-700 font-medium">{feature}</span>
                  </motion.div>
                ))}
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <motion.button
                  whileHover={{ 
                    scale: 1.05,
                    boxShadow: "0 15px 35px rgba(214, 0, 28, 0.3)"
                  }}
                  whileTap={{ scale: 0.95 }}
                  onClick={() => handleGetQuote(tricycle.name, tricycle.price, tricycle.image)}
                  className="btn-primary flex-1 flex items-center justify-center space-x-2"
                >
                  <Mail className="w-5 h-5" />
                  <span>Request Pricing</span>
                </motion.button>
                <motion.button
                  whileHover={{ 
                    scale: 1.05,
                    backgroundColor: "rgba(214, 0, 28, 0.1)"
                  }}
                  whileTap={{ scale: 0.95 }}
                  className="btn-secondary flex-1"
                >
                  Learn More
                </motion.button>
              </div>
            </div>
          </div>
        </motion.div>
      </div>

      {/* Quote Modal */}
      <QuoteModal
        isOpen={!!selectedVehicle}
        onClose={() => setSelectedVehicle(null)}
        vehicleName={selectedVehicle?.name || ''}
        vehiclePrice={selectedVehicle?.price || ''}
        vehicleImage={selectedVehicle?.image}
      />

      {/* Image Lightbox */}
      <ImageLightbox
        isOpen={lightboxOpen}
        onClose={closeLightbox}
        images={lightboxImages}
        currentIndex={lightboxInitialIndex}
        vehicleName={lightboxVehicleName}
        onIndexChange={handleLightboxIndexChange}
      />
    </section>
  );
};

export default Vehicles;
import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { ChevronDown, Zap, Leaf, Shield } from 'lucide-react';

const Hero = () => {
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  
  const carImages = [
    '/fazona/20millionnairacar.jpg',
    '/fazona/20millionnaira.jpg',
    '/fazona/9.5millionnaira.jpg',
    '/fazona/4.5millionnaira.jpg'
  ];

  // Auto-rotate images every 4 seconds
  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentImageIndex((prevIndex) =>
        prevIndex === carImages.length - 1 ? 0 : prevIndex + 1
      );
    }, 4000);

    return () => clearInterval(interval);
  }, [carImages.length]);

  return (
    <section id="home" className="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
      {/* Dynamic Background Elements */}
      <div className="absolute inset-0 bg-gradient-to-br from-brand-white via-gray-50 to-red-50"></div>
      
      {/* Crazy Animated Background Shapes - Contained within viewport */}
      <motion.div
        animate={{
          rotate: 360,
          scale: [1, 1.2, 1],
          x: [0, 30, -30, 0],
          y: [0, -20, 20, 0]
        }}
        transition={{
          rotate: { duration: 20, repeat: Infinity, ease: "linear" },
          scale: { duration: 4, repeat: Infinity, ease: "easeInOut" },
          x: { duration: 8, repeat: Infinity, ease: "easeInOut" },
          y: { duration: 6, repeat: Infinity, ease: "easeInOut" }
        }}
        className="absolute top-20 right-10 w-20 h-20 border-4 border-brand-red/30 rounded-full"
      />
      
      <motion.div
        animate={{
          y: [-20, 40, -20],
          x: [-10, 15, -10],
          rotate: [0, 180, 360],
          scale: [1, 1.3, 1]
        }}
        transition={{
          duration: 5,
          repeat: Infinity,
          ease: "easeInOut",
          times: [0, 0.5, 1]
        }}
        className="absolute bottom-20 left-10 w-16 h-16 bg-gradient-to-r from-brand-red/20 to-red-600/30 rounded-full"
      />

      {/* Floating Sparkles - Reduced movement range */}
      {[...Array(6)].map((_, i) => (
        <motion.div
          key={i}
          animate={{
            y: [-15, -40, -15],
            x: [-5, 5, -5],
            opacity: [0.3, 1, 0.3],
            scale: [0.5, 1, 0.5]
          }}
          transition={{
            duration: 3 + i * 0.5,
            repeat: Infinity,
            ease: "easeInOut",
            delay: i * 0.3
          }}
          className={`absolute w-3 h-3 bg-brand-red/40 rounded-full`}
          style={{
            top: `${20 + i * 10}%`,
            left: `${15 + i * 10}%`
          }}
        />
      ))}

      {/* Pulsing Rings - Reduced size */}
      <motion.div
        animate={{
          scale: [1, 1.5, 1],
          opacity: [0.5, 0, 0.5]
        }}
        transition={{
          duration: 3,
          repeat: Infinity,
          ease: "easeOut"
        }}
        className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 border-2 border-brand-red/20 rounded-full"
      />

      <div className="container-max section-padding relative z-10 w-full">
        <div className="grid lg:grid-cols-2 gap-8 items-center w-full">
          {/* Left Content */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8 }}
            className="space-y-6 w-full"
          >
            {/* Main Heading with Crazy Animation */}
            <motion.h1
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.3 }}
              className="text-4xl lg:text-6xl font-montserrat font-bold text-brand-black leading-tight"
            >
              <motion.span
                animate={{
                  textShadow: [
                    "0 0 0px rgba(214, 0, 28, 0)",
                    "0 0 20px rgba(214, 0, 28, 0.5)",
                    "0 0 0px rgba(214, 0, 28, 0)"
                  ]
                }}
                transition={{ duration: 2, repeat: Infinity }}
              >
                Drive the
              </motion.span>
              <motion.span 
                className="gradient-text block"
                animate={{
                  scale: [1, 1.05, 1],
                  rotate: [0, 1, -1, 0]
                }}
                transition={{
                  duration: 3,
                  repeat: Infinity,
                  ease: "easeInOut"
                }}
              >
                Future
              </motion.span>
              <motion.span
                animate={{ y: [0, -5, 0] }}
                transition={{ duration: 2, repeat: Infinity, ease: "easeInOut" }}
              >
                Today
              </motion.span>
            </motion.h1>

            {/* Premier EV Brand Text */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.35 }}
              className="text-lg font-semibold text-brand-red"
            >
              Nigeria's Premier EV Brand
            </motion.div>

            {/* Subheading */}
            <motion.p
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.4 }}
              className="text-lg text-gray-600 leading-relaxed max-w-lg"
            >
              Experience premium electric mobility with FaZona EV. Clean, affordable, and smart transportation solutions designed for Nigeria's future.
            </motion.p>

            {/* Animated Feature Pills */}
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.5 }}
              className="flex flex-wrap gap-3"
            >
              {[
                { icon: Leaf, text: 'Zero Emissions', color: 'from-green-400 to-emerald-500' },
                { icon: Zap, text: 'Fast Charging', color: 'from-yellow-400 to-orange-500' },
                { icon: Shield, text: 'Minimal Maintenance', color: 'from-blue-400 to-cyan-500' }
              ].map((feature, index) => (
                <motion.div
                  key={index}
                  className="flex items-center space-x-2 bg-white px-3 py-2 rounded-full shadow-md border border-gray-100"
                  whileHover={{
                    scale: 1.05,
                    boxShadow: "0 10px 25px rgba(0,0,0,0.1)",
                    y: -2
                  }}
                  animate={{
                    y: [0, -2, 0],
                  }}
                  transition={{
                    y: { duration: 2 + index * 0.5, repeat: Infinity, ease: "easeInOut", delay: index * 0.2 }
                  }}
                >
                  <motion.div
                    className={`w-5 h-5 rounded-full bg-gradient-to-r ${feature.color} flex items-center justify-center`}
                    animate={{ rotate: [0, 360] }}
                    transition={{ duration: 4 + index, repeat: Infinity, ease: "linear" }}
                  >
                    <feature.icon className="w-3 h-3 text-white" />
                  </motion.div>
                  <span className="text-brand-black font-medium text-sm">{feature.text}</span>
                </motion.div>
              ))}
            </motion.div>

            {/* CTA Button - Single Button */}
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.6 }}
              className="flex justify-start"
            >
              <motion.button
                whileHover={{
                  scale: 1.05,
                  boxShadow: "0 20px 40px rgba(214, 0, 28, 0.3)",
                  y: -3
                }}
                whileTap={{ scale: 0.95 }}
                animate={{
                  boxShadow: [
                    "0 5px 15px rgba(214, 0, 28, 0.2)",
                    "0 10px 25px rgba(214, 0, 28, 0.3)",
                    "0 5px 15px rgba(214, 0, 28, 0.2)"
                  ]
                }}
                transition={{
                  boxShadow: { duration: 2, repeat: Infinity, ease: "easeInOut" }
                }}
                onClick={() => document.getElementById('vehicles')?.scrollIntoView({ behavior: 'smooth' })}
                className="btn-primary"
              >
                Explore Vehicles
              </motion.button>
            </motion.div>
          </motion.div>

          {/* Right Content - Rotating Car Images */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="relative w-full"
          >
            {/* Main Car Image Carousel with Insane Animation */}
            <motion.div
              animate={{
                y: [-10, 10, -10],
                rotate: [0, 1, -1, 0],
                scale: [1, 1.01, 1]
              }}
              transition={{
                y: { duration: 4, repeat: Infinity, ease: "easeInOut" },
                rotate: { duration: 6, repeat: Infinity, ease: "easeInOut" },
                scale: { duration: 3, repeat: Infinity, ease: "easeInOut" }
              }}
              className="relative z-10 w-full"
            >
              <div className="relative overflow-hidden rounded-3xl shadow-2xl w-full">
                {carImages.map((image, index) => (
                  <motion.img
                    key={index}
                    src={image}
                    alt={`FaZona EV Car ${index + 1}`}
                    className="w-full h-auto max-w-full"
                    initial={{ opacity: 0, scale: 1.1 }}
                    animate={{
                      opacity: index === currentImageIndex ? 1 : 0,
                      scale: index === currentImageIndex ? 1 : 1.1
                    }}
                    transition={{ duration: 0.8, ease: "easeInOut" }}
                    style={{
                      position: index === 0 ? 'relative' : 'absolute',
                      top: index === 0 ? 'auto' : 0,
                      left: index === 0 ? 'auto' : 0,
                      width: '100%',
                      height: '100%',
                      objectFit: 'cover'
                    }}
                    whileHover={{
                      scale: index === currentImageIndex ? 1.02 : 1.1,
                      rotateY: 2,
                      boxShadow: "0 25px 50px rgba(0,0,0,0.2)"
                    }}
                  />
                ))}
              </div>
              
              {/* Glowing Effect */}
              <motion.div
                animate={{
                  opacity: [0.5, 1, 0.5],
                  scale: [1, 1.05, 1]
                }}
                transition={{
                  duration: 2,
                  repeat: Infinity,
                  ease: "easeInOut"
                }}
                className="absolute inset-0 bg-gradient-to-r from-brand-red/20 to-transparent rounded-3xl blur-xl"
              />
            </motion.div>

            {/* Image Indicators */}
            <div className="absolute -bottom-8 left-1/2 transform -translate-x-1/2 flex space-x-2">
              {carImages.map((_, index) => (
                <motion.button
                  key={index}
                  onClick={() => setCurrentImageIndex(index)}
                  className={`w-3 h-3 rounded-full transition-all duration-300 ${
                    index === currentImageIndex
                      ? 'bg-brand-red scale-125'
                      : 'bg-gray-300 hover:bg-gray-400'
                  }`}
                  whileHover={{ scale: 1.2 }}
                  whileTap={{ scale: 0.9 }}
                />
              ))}
            </div>
            
            {/* Crazy Floating Elements - Reduced movement */}
            <motion.div
              animate={{
                rotate: [0, 360],
                scale: [1, 1.2, 1],
                x: [0, 15, -15, 0],
                y: [0, -10, 10, 0]
              }}
              transition={{
                rotate: { duration: 8, repeat: Infinity, ease: "linear" },
                scale: { duration: 3, repeat: Infinity, ease: "easeInOut" },
                x: { duration: 5, repeat: Infinity, ease: "easeInOut" },
                y: { duration: 4, repeat: Infinity, ease: "easeInOut" }
              }}
              className="absolute -top-8 -right-8 w-16 h-16 border-4 border-brand-red/40 rounded-full"
            />
            
            <motion.div
              animate={{
                scale: [1, 1.3, 1],
                rotate: [0, -180, -360],
                opacity: [0.6, 1, 0.6]
              }}
              transition={{
                duration: 4,
                repeat: Infinity,
                ease: "easeInOut",
                times: [0, 0.5, 1]
              }}
              className="absolute -bottom-8 -left-8 w-12 h-12 bg-gradient-to-r from-brand-red/30 to-red-600/30 rounded-full"
            />

            {/* Electric Sparks - Contained */}
            {[...Array(3)].map((_, i) => (
              <motion.div
                key={i}
                animate={{
                  scale: [0, 1, 0],
                  opacity: [0, 1, 0],
                  rotate: [0, 180, 360]
                }}
                transition={{
                  duration: 1.5,
                  repeat: Infinity,
                  delay: i * 0.3,
                  ease: "easeOut"
                }}
                className="absolute w-2 h-2 bg-yellow-400 rounded-full"
                style={{
                  top: `${30 + i * 20}%`,
                  right: `${15 + i * 5}%`
                }}
              />
            ))}
          </motion.div>
        </div>
      </div>

      {/* Animated Scroll Indicator */}
      <motion.div
        animate={{
          y: [0, 15, 0],
          opacity: [0.5, 1, 0.5]
        }}
        transition={{
          duration: 2,
          repeat: Infinity,
          ease: "easeInOut"
        }}
        className="absolute bottom-8 left-1/2 transform -translate-x-1/2"
      >
        <motion.div
          whileHover={{ scale: 1.2 }}
          className="flex flex-col items-center space-y-2"
        >
          <ChevronDown className="w-8 h-8 text-brand-red" />
          <motion.div
            animate={{ scaleX: [0, 1, 0] }}
            transition={{ duration: 2, repeat: Infinity }}
            className="w-8 h-0.5 bg-brand-red"
          />
        </motion.div>
      </motion.div>
    </section>
  );
};

export default Hero;
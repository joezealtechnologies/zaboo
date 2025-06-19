import { motion } from 'framer-motion';
import { useInView } from 'react-intersection-observer';
import { Target, Globe, Users, Award, Zap, Shield, Leaf, TrendingUp } from 'lucide-react';

const About = () => {
  const [ref, inView] = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });

  const stats = [
    {
      icon: Zap,
      number: '200km',
      label: 'Maximum Range',
      color: 'from-yellow-400 to-orange-500'
    },
    {
      icon: Leaf,
      number: '100%',
      label: 'Zero Emissions',
      color: 'from-green-400 to-emerald-500'
    },
    {
      icon: Shield,
      number: '24/7',
      label: 'Support Available',
      color: 'from-blue-400 to-cyan-500'
    },
    {
      icon: TrendingUp,
      number: '5+',
      label: 'Vehicle Models',
      color: 'from-purple-400 to-pink-500'
    }
  ];

  const values = [
    {
      icon: Target,
      title: 'Our Mission',
      description: 'To redefine transportation across Africa by delivering eco-friendly, cost-effective, and future-forward electric mobility options.'
    },
    {
      icon: Globe,
      title: 'Global Impact',
      description: 'Starting from Nigeria, we aim to transform the African automotive landscape with sustainable transportation solutions.'
    },
    {
      icon: Users,
      title: 'Customer First',
      description: 'Every vehicle is designed with our customers in mind, ensuring reliability, affordability, and exceptional performance.'
    },
    {
      icon: Award,
      title: 'Quality Promise',
      description: 'We maintain the highest standards in manufacturing and service, delivering premium electric vehicles you can trust.'
    }
  ];

  return (
    <section id="about" className="py-20 bg-white">
      <div className="container-max section-padding">
        {/* Hero Stats Section */}
        <motion.div
          ref={ref}
          initial={{ opacity: 0, y: 50 }}
          animate={inView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.8 }}
          className="text-center mb-20"
        >
          <motion.div
            initial={{ opacity: 0, scale: 0.8 }}
            animate={inView ? { opacity: 1, scale: 1 } : {}}
            transition={{ delay: 0.2, type: "spring", stiffness: 200 }}
            className="inline-flex items-center space-x-3 bg-gradient-to-r from-brand-red/10 to-red-600/10 px-8 py-4 rounded-full border border-brand-red/20 mb-8"
          >
            <motion.div
              animate={{ rotate: 360 }}
              transition={{ duration: 3, repeat: Infinity, ease: "linear" }}
              className="w-8 h-8 bg-brand-red rounded-full flex items-center justify-center"
            >
              <Zap className="w-4 h-4 text-white" />
            </motion.div>
            <span className="text-brand-red font-bold text-xl">Revolutionizing Nigerian Transportation</span>
          </motion.div>

          <motion.h2
            initial={{ opacity: 0, y: 30 }}
            animate={inView ? { opacity: 1, y: 0 } : {}}
            transition={{ delay: 0.3 }}
            className="text-3xl lg:text-4xl font-montserrat font-bold text-brand-black mb-6"
          >
            Leading the <span className="gradient-text">Electric Revolution</span> in West Africa
          </motion.h2>

          <motion.p
            initial={{ opacity: 0, y: 30 }}
            animate={inView ? { opacity: 1, y: 0 } : {}}
            transition={{ delay: 0.4 }}
            className="text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed mb-12"
          >
            From Lagos to Abuja, from Kano to Port Harcourt - FaZona EV is transforming how Nigeria moves. 
            We're not just selling cars; we're building the future of sustainable transportation across Africa.
          </motion.p>

          {/* Impressive Stats Grid */}
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {stats.map((stat, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 50, scale: 0.8 }}
                animate={inView ? { opacity: 1, y: 0, scale: 1 } : {}}
                transition={{ duration: 0.8, delay: 0.5 + index * 0.1 }}
                className="bg-gradient-to-br from-white to-gray-50 rounded-3xl p-8 shadow-lg border border-gray-100 group hover:shadow-2xl transition-all duration-500"
                whileHover={{ y: -10, scale: 1.02 }}
              >
                <motion.div
                  className={`w-16 h-16 rounded-2xl bg-gradient-to-r ${stat.color} flex items-center justify-center mx-auto mb-6`}
                  animate={{ 
                    rotate: [0, 10, -10, 0],
                    scale: [1, 1.1, 1]
                  }}
                  transition={{ 
                    duration: 4, 
                    repeat: Infinity, 
                    ease: "easeInOut",
                    delay: index * 0.5
                  }}
                >
                  <stat.icon className="w-8 h-8 text-white" />
                </motion.div>
                
                <motion.h3
                  initial={{ scale: 0 }}
                  animate={inView ? { scale: 1 } : {}}
                  transition={{ duration: 0.8, delay: 0.7 + index * 0.1, type: "spring", stiffness: 200 }}
                  className="text-4xl lg:text-5xl font-montserrat font-bold text-brand-red mb-2"
                >
                  {stat.number}
                </motion.h3>
                
                <p className="text-gray-600 font-semibold">{stat.label}</p>
              </motion.div>
            ))}
          </div>
        </motion.div>

        <div className="grid lg:grid-cols-2 gap-16 items-center">
          {/* Left Content */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            animate={inView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.8, delay: 0.8 }}
          >
            <h2 className="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-8">
              Driving Africa's <span className="gradient-text">Electric Future</span>
            </h2>
            
            <p className="text-xl text-gray-600 leading-relaxed mb-8">
              FaZona EV is a forward-thinking electric vehicle brand focused on clean, 
              affordable, and smart mobility in Nigeria. Our product lineup is tailored 
              to meet the needs of both individuals and businesses seeking energy-efficient 
              transportation solutions.
            </p>

            <div className="space-y-6">
              <div className="flex items-start space-x-4">
                <div className="w-6 h-6 bg-brand-red rounded-full flex items-center justify-center mt-1">
                  <div className="w-2 h-2 bg-white rounded-full" />
                </div>
                <div>
                  <h4 className="font-montserrat font-semibold text-brand-black mb-2">
                    Sustainable Innovation
                  </h4>
                  <p className="text-gray-600">
                    Leading the charge in electric vehicle technology with cutting-edge solutions 
                    designed for African roads and conditions.
                  </p>
                </div>
              </div>

              <div className="flex items-start space-x-4">
                <div className="w-6 h-6 bg-brand-red rounded-full flex items-center justify-center mt-1">
                  <div className="w-2 h-2 bg-white rounded-full" />
                </div>
                <div>
                  <h4 className="font-montserrat font-semibold text-brand-black mb-2">
                    Local Understanding
                  </h4>
                  <p className="text-gray-600">
                    Built with deep understanding of Nigerian transportation needs, 
                    infrastructure, and economic considerations.
                  </p>
                </div>
              </div>

              <div className="flex items-start space-x-4">
                <div className="w-6 h-6 bg-brand-red rounded-full flex items-center justify-center mt-1">
                  <div className="w-2 h-2 bg-white rounded-full" />
                </div>
                <div>
                  <h4 className="font-montserrat font-semibold text-brand-black mb-2">
                    Future Ready
                  </h4>
                  <p className="text-gray-600">
                    Preparing Nigeria for the global shift to electric mobility with 
                    reliable, efficient, and affordable electric vehicles.
                  </p>
                </div>
              </div>
            </div>
          </motion.div>

          {/* Right Content - Logo */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={inView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.8, delay: 1.0 }}
            className="relative"
          >
            <div className="relative bg-gradient-to-br from-gray-50 to-white rounded-3xl p-12 shadow-lg">
              <img
                src="/fazona/LogoFaZona.png"
                alt="FaZona EV Logo"
                className="w-full h-auto max-w-md mx-auto"
              />
              
              {/* Decorative Elements */}
              <motion.div
                animate={{ rotate: 360 }}
                transition={{ duration: 20, repeat: Infinity, ease: "linear" }}
                className="absolute -top-6 -right-6 w-12 h-12 border-4 border-brand-red/20 rounded-full"
              />
              <motion.div
                animate={{ scale: [1, 1.1, 1] }}
                transition={{ duration: 3, repeat: Infinity, ease: "easeInOut" }}
                className="absolute -bottom-6 -left-6 w-8 h-8 bg-brand-red/20 rounded-full"
              />
            </div>
          </motion.div>
        </div>

        {/* Values Grid */}
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          animate={inView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.8, delay: 1.2 }}
          className="mt-20"
        >
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {values.map((value, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 30 }}
                animate={inView ? { opacity: 1, y: 0 } : {}}
                transition={{ duration: 0.8, delay: 1.3 + index * 0.1 }}
                className="text-center group"
              >
                <div className="w-16 h-16 bg-gradient-to-r from-brand-red to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                  <value.icon className="w-8 h-8 text-white" />
                </div>
                <h3 className="text-xl font-montserrat font-bold text-brand-black mb-4">
                  {value.title}
                </h3>
                <p className="text-gray-600 leading-relaxed">
                  {value.description}
                </p>
              </motion.div>
            ))}
          </div>
        </motion.div>
      </div>
    </section>
  );
};

export default About;
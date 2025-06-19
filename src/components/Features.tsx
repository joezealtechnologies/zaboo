import { motion } from 'framer-motion';
import { useInView } from 'react-intersection-observer';
import { Zap, Leaf, Shield, Smartphone, Wrench, DollarSign } from 'lucide-react';

const Features = () => {
  const [ref, inView] = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });

  const features = [
    {
      icon: Zap,
      title: 'Fast Charging Support',
      description: 'Advanced charging technology that gets you back on the road quickly with minimal downtime.',
      color: 'from-yellow-400 to-orange-500'
    },
    {
      icon: Leaf,
      title: 'Zero Emissions',
      description: 'Completely clean energy transportation contributing to a healthier environment for Nigeria.',
      color: 'from-green-400 to-emerald-500'
    },
    {
      icon: Shield,
      title: 'Regenerative Braking',
      description: 'Intelligent braking system that recovers energy while driving, extending your vehicle range.',
      color: 'from-blue-400 to-cyan-500'
    },
    {
      icon: Smartphone,
      title: 'Smart Digital Dashboard',
      description: 'Intuitive digital interface providing real-time vehicle data and connectivity features.',
      color: 'from-purple-400 to-pink-500'
    },
    {
      icon: Wrench,
      title: 'Minimal Maintenance',
      description: 'Electric motors require significantly less maintenance compared to traditional combustion engines.',
      color: 'from-red-400 to-rose-500'
    },
    {
      icon: DollarSign,
      title: 'Government Duty Inclusive',
      description: 'Select models include all government duties and taxes, providing transparent pricing.',
      color: 'from-indigo-400 to-blue-500'
    }
  ];

  return (
    <section id="features" className="py-20 bg-white">
      <div className="container-max section-padding">
        <motion.div
          ref={ref}
          initial={{ opacity: 0, y: 50 }}
          animate={inView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.8 }}
          className="text-center mb-16"
        >
          <h2 className="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
            Why Choose <span className="gradient-text">FaZona EV</span>
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Experience the future of transportation with cutting-edge technology, 
            environmental consciousness, and unmatched reliability.
          </p>
        </motion.div>

        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          {features.map((feature, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, y: 50 }}
              animate={inView ? { opacity: 1, y: 0 } : {}}
              transition={{ duration: 0.8, delay: index * 0.1 }}
              className="group relative bg-white rounded-3xl p-8 shadow-lg card-hover border border-gray-100"
            >
              {/* Icon Background */}
              <div className={`w-16 h-16 rounded-2xl bg-gradient-to-r ${feature.color} p-4 mb-6 group-hover:scale-110 transition-transform duration-300`}>
                <feature.icon className="w-full h-full text-white" />
              </div>

              {/* Content */}
              <h3 className="text-xl font-montserrat font-bold text-brand-black mb-4">
                {feature.title}
              </h3>
              <p className="text-gray-600 leading-relaxed">
                {feature.description}
              </p>

              {/* Hover Effect */}
              <div className="absolute inset-0 bg-gradient-to-r from-brand-red/5 to-red-600/5 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none" />
            </motion.div>
          ))}
        </div>

        {/* Stats Section */}
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          animate={inView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.8, delay: 0.6 }}
          className="mt-20 bg-gradient-to-r from-brand-red to-red-600 rounded-3xl p-12 text-white"
        >
          <div className="grid md:grid-cols-3 gap-8 text-center">
            <div>
              <motion.h3
                initial={{ scale: 0 }}
                animate={inView ? { scale: 1 } : {}}
                transition={{ duration: 0.8, delay: 0.8 }}
                className="text-4xl lg:text-5xl font-montserrat font-bold mb-2"
              >
                200km
              </motion.h3>
              <p className="text-red-100 text-lg">Maximum Range</p>
            </div>
            <div>
              <motion.h3
                initial={{ scale: 0 }}
                animate={inView ? { scale: 1 } : {}}
                transition={{ duration: 0.8, delay: 0.9 }}
                className="text-4xl lg:text-5xl font-montserrat font-bold mb-2"
              >
                100%
              </motion.h3>
              <p className="text-red-100 text-lg">Electric Powered</p>
            </div>
            <div>
              <motion.h3
                initial={{ scale: 0 }}
                animate={inView ? { scale: 1 } : {}}
                transition={{ duration: 0.8, delay: 1.0 }}
                className="text-4xl lg:text-5xl font-montserrat font-bold mb-2"
              >
                24/7
              </motion.h3>
              <p className="text-red-100 text-lg">Support Available</p>
            </div>
          </div>
        </motion.div>
      </div>
    </section>
  );
};

export default Features;
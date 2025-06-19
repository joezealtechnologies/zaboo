import { motion } from 'framer-motion';
import { Mail, Phone, Facebook, Twitter, Instagram, Linkedin } from 'lucide-react';

const Footer = () => {
  const quickLinks = [
    { name: 'Home', href: '#home' },
    { name: 'Features', href: '#features' },
    { name: 'Vehicles', href: '#vehicles' },
    { name: 'About', href: '#about' },
    { name: 'Contact', href: '#contact' }
  ];

  const vehicles = [
    { name: 'Premium Long Range', href: '#vehicles' },
    { name: 'Mid-Range Model', href: '#vehicles' },
    { name: 'Standard Range', href: '#vehicles' },
    { name: 'Compact Entry', href: '#vehicles' },
    { name: 'Electric Tricycle', href: '#vehicles' }
  ];

  const socialLinks = [
    { icon: Facebook, href: '#', name: 'Facebook' },
    { icon: Twitter, href: '#', name: 'Twitter' },
    { icon: Instagram, href: 'https://www.instagram.com/fazona_ev?igsh=MTdqeTZvMno4d294eQ%3D%3D&utm_source=qr', name: 'Instagram' },
    { icon: Linkedin, href: '#', name: 'LinkedIn' }
  ];

  const handleNewsletterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const email = (e.target as HTMLFormElement).email.value;
    
    const subject = `Newsletter Subscription Request`;
    const body = `Hello FaZona EV Team,

I would like to subscribe to your newsletter to stay updated on:
- New vehicle launches
- Special offers and promotions
- Company news and updates
- Electric vehicle industry insights

Email: ${email}

Thank you!`;

    const mailtoLink = `mailto:evfazona@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = mailtoLink;
  };

  return (
    <footer className="bg-gradient-to-br from-brand-black to-gray-900 text-white relative overflow-hidden">
      {/* Background Pattern */}
      <div className="absolute inset-0 opacity-5">
        <div className="absolute inset-0" style={{
          backgroundImage: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`,
        }} />
      </div>

      {/* Main Footer */}
      <div className="container-max section-padding py-16 relative z-10">
        <div className="grid lg:grid-cols-4 gap-12">
          {/* Brand Section */}
          <div className="lg:col-span-1">
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
              className="flex items-center space-x-3 mb-6"
            >
              <motion.img
                src="/fazona/FaZona.png"
                alt="FaZona EV Logo"
                className="h-16 w-auto"
                whileHover={{ scale: 1.05, rotate: [0, -2, 2, 0] }}
                transition={{ duration: 0.5 }}
              />
            </motion.div>
            
            <motion.p
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.1 }}
              className="text-gray-300 leading-relaxed mb-6"
            >
              Redefining transportation across Africa with eco-friendly, cost-effective, 
              and future-forward electric mobility solutions.
            </motion.p>

            {/* Social Links */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="flex space-x-4"
            >
              {socialLinks.map((social, index) => (
                <motion.a
                  key={index}
                  href={social.href}
                  whileHover={{ scale: 1.2, y: -3 }}
                  whileTap={{ scale: 0.95 }}
                  className="w-12 h-12 bg-gradient-to-r from-gray-800 to-gray-700 rounded-full flex items-center justify-center hover:from-brand-red hover:to-red-600 transition-all duration-300 shadow-lg"
                  aria-label={social.name}
                >
                  <social.icon className="w-5 h-5" />
                </motion.a>
              ))}
            </motion.div>
          </div>

          {/* Quick Links */}
          <div>
            <motion.h4
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
              className="text-xl font-montserrat font-bold mb-6"
            >
              Quick Links
            </motion.h4>
            <motion.ul
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.1 }}
              className="space-y-3"
            >
              {quickLinks.map((link, index) => (
                <li key={index}>
                  <motion.a
                    href={link.href}
                    whileHover={{ x: 5, color: "#D6001C" }}
                    className="text-gray-300 hover:text-brand-red transition-colors duration-300 flex items-center space-x-2"
                  >
                    <div className="w-1 h-1 bg-brand-red rounded-full opacity-0 group-hover:opacity-100 transition-opacity" />
                    <span>{link.name}</span>
                  </motion.a>
                </li>
              ))}
            </motion.ul>
          </div>

          {/* Vehicles */}
          <div>
            <motion.h4
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
              className="text-xl font-montserrat font-bold mb-6"
            >
              Our Vehicles
            </motion.h4>
            <motion.ul
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.1 }}
              className="space-y-3"
            >
              {vehicles.map((vehicle, index) => (
                <li key={index}>
                  <motion.a
                    href={vehicle.href}
                    whileHover={{ x: 5, color: "#D6001C" }}
                    className="text-gray-300 hover:text-brand-red transition-colors duration-300"
                  >
                    {vehicle.name}
                  </motion.a>
                </li>
              ))}
            </motion.ul>
          </div>

          {/* Contact Info */}
          <div>
            <motion.h4
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
              className="text-xl font-montserrat font-bold mb-6"
            >
              Contact Info
            </motion.h4>
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.1 }}
              className="space-y-4"
            >
              <motion.div 
                className="flex items-start space-x-3 cursor-pointer group"
                whileHover={{ x: 5 }}
                onClick={() => window.location.href = 'mailto:evfazona@gmail.com'}
              >
                <Mail className="w-5 h-5 text-brand-red mt-1" />
                <div>
                  <p className="text-gray-300 group-hover:text-brand-red transition-colors">evfazona@gmail.com</p>
                </div>
              </motion.div>
              <motion.div 
                className="flex items-start space-x-3 cursor-pointer group"
                whileHover={{ x: 5 }}
                onClick={() => window.open('https://wa.me/2349135859888', '_blank')}
              >
                <Phone className="w-5 h-5 text-brand-red mt-1" />
                <div>
                  <p className="text-gray-300 group-hover:text-brand-red transition-colors">+234 913 585 9888</p>
                </div>
              </motion.div>
              <motion.div 
                className="flex items-start space-x-3 cursor-pointer group"
                whileHover={{ x: 5 }}
                onClick={() => window.open('https://www.instagram.com/fazona_ev?igsh=MTdqeTZvMno4d294eQ%3D%3D&utm_source=qr', '_blank')}
              >
                <Instagram className="w-5 h-5 text-brand-red mt-1" />
                <div>
                  <p className="text-gray-300 group-hover:text-brand-red transition-colors">@fazona_ev</p>
                </div>
              </motion.div>
            </motion.div>

            {/* Newsletter */}
            <motion.div
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              className="mt-8"
            >
              <h5 className="font-montserrat font-semibold mb-3">Stay Updated</h5>
              <form onSubmit={handleNewsletterSubmit} className="flex">
                <input
                  type="email"
                  name="email"
                  placeholder="Your email"
                  required
                  className="flex-1 px-4 py-3 bg-gray-800 border border-gray-700 rounded-l-xl focus:outline-none focus:border-brand-red text-white placeholder-gray-400"
                />
                <motion.button
                  type="submit"
                  whileHover={{ scale: 1.05, backgroundColor: "#b8001a" }}
                  whileTap={{ scale: 0.95 }}
                  className="bg-brand-red px-6 py-3 rounded-r-xl hover:bg-red-700 transition-colors duration-300"
                >
                  <Mail className="w-5 h-5" />
                </motion.button>
              </form>
            </motion.div>
          </div>
        </div>
      </div>

      {/* Bottom Footer */}
      <motion.div
        initial={{ opacity: 0 }}
        whileInView={{ opacity: 1 }}
        transition={{ duration: 0.8 }}
        className="border-t border-gray-800 relative z-10"
      >
        <div className="container-max section-padding py-6">
          <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <p className="text-gray-400 text-center md:text-left">
              © 2025 FaZona EV. All rights reserved.
            </p>
            <div className="flex space-x-6 text-sm">
              <motion.a
                href="#"
                whileHover={{ y: -2, color: "#D6001C" }}
                className="text-gray-400 hover:text-brand-red transition-colors duration-300"
              >
                Privacy Policy
              </motion.a>
              <motion.a
                href="#"
                whileHover={{ y: -2, color: "#D6001C" }}
                className="text-gray-400 hover:text-brand-red transition-colors duration-300"
              >
                Terms of Service
              </motion.a>
              <motion.a
                href="#"
                whileHover={{ y: -2, color: "#D6001C" }}
                className="text-gray-400 hover:text-brand-red transition-colors duration-300"
              >
                Cookie Policy
              </motion.a>
            </div>
          </div>
        </div>
      </motion.div>
    </footer>
  );
};

export default Footer;
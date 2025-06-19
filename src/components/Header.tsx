import { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Menu, X, Wrench } from 'lucide-react';
import ReportIssueModal from './ReportIssueModal';

const Header = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [showReportModal, setShowReportModal] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 50);
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const navItems = [
    { name: 'Home', href: '#home' },
    { name: 'Features', href: '#features' },
    { name: 'Vehicles', href: '#vehicles' },
    { name: 'About', href: '#about' },
    { name: 'Contact', href: '#contact' },
  ];

  const handleReportIssue = () => {
    setShowReportModal(true);
    setIsOpen(false); // Close mobile menu if open
  };

  return (
    <>
      <motion.header
        initial={{ y: -100 }}
        animate={{ y: 0 }}
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
          scrolled ? 'bg-white/95 backdrop-blur-md shadow-lg' : 'bg-transparent'
        }`}
      >
        <div className="container-max header-padding">
          <div className="flex items-center justify-between h-20">
            {/* Logo - Far left on mobile, balanced on desktop */}
            <motion.div
              whileHover={{ scale: 1.05 }}
              className="flex items-center flex-shrink-0 -ml-4 sm:-ml-2 lg:ml-0"
            >
              <motion.img
                src="/fazona/FaZona.png"
                alt="FaZona EV Logo"
                className="h-20 w-auto"
                whileHover={{ rotate: [0, -3, 3, 0] }}
                transition={{ duration: 0.6 }}
              />
            </motion.div>

            {/* Desktop Navigation */}
            <nav className="hidden lg:flex items-center space-x-8">
              {navItems.map((item) => (
                <motion.a
                  key={item.name}
                  href={item.href}
                  whileHover={{ scale: 1.1, y: -2 }}
                  whileTap={{ scale: 0.95 }}
                  className="text-brand-black hover:text-brand-red transition-colors duration-300 font-medium relative group"
                >
                  {item.name}
                  <motion.div
                    className="absolute -bottom-1 left-0 right-0 h-0.5 bg-brand-red"
                    initial={{ scaleX: 0 }}
                    whileHover={{ scaleX: 1 }}
                    transition={{ duration: 0.3 }}
                  />
                </motion.a>
              ))}
            </nav>

            {/* Report Issue Button */}
            <motion.button
              whileHover={{
                scale: 1.05,
                boxShadow: "0 10px 30px rgba(214, 0, 28, 0.3)",
                y: -2
              }}
              whileTap={{ scale: 0.95 }}
              onClick={handleReportIssue}
              className="hidden lg:flex items-center space-x-2 bg-gradient-to-r from-brand-red to-red-600 text-white px-6 py-3 rounded-full font-montserrat font-semibold text-base transition-all duration-300 hover:from-red-700 hover:to-red-800 hover:shadow-2xl border-0 outline-none focus:ring-4 focus:ring-brand-red/30"
            >
              <motion.div
                animate={{ rotate: [0, 15, -15, 0] }}
                transition={{ duration: 2, repeat: Infinity, ease: "easeInOut" }}
              >
                <Wrench className="w-5 h-5" />
              </motion.div>
              <span>Report Issue</span>
            </motion.button>

            {/* Mobile Menu Button */}
            <motion.button
              onClick={() => setIsOpen(!isOpen)}
              className="lg:hidden relative w-12 h-12 rounded-full bg-gradient-to-r from-brand-red to-red-600 flex items-center justify-center shadow-lg flex-shrink-0"
              whileHover={{ scale: 1.1 }}
              whileTap={{ scale: 0.9 }}
              animate={{ rotate: isOpen ? 180 : 0 }}
              transition={{ duration: 0.3 }}
            >
              <AnimatePresence mode="wait">
                {isOpen ? (
                  <motion.div
                    key="close"
                    initial={{ rotate: -90, opacity: 0 }}
                    animate={{ rotate: 0, opacity: 1 }}
                    exit={{ rotate: 90, opacity: 0 }}
                    transition={{ duration: 0.2 }}
                  >
                    <X size={24} className="text-white" />
                  </motion.div>
                ) : (
                  <motion.div
                    key="menu"
                    initial={{ rotate: 90, opacity: 0 }}
                    animate={{ rotate: 0, opacity: 1 }}
                    exit={{ rotate: -90, opacity: 0 }}
                    transition={{ duration: 0.2 }}
                    className="relative"
                  >
                    <motion.div
                      animate={{
                        scaleY: [1, 0.8, 1],
                        scaleX: [1, 1.2, 1]
                      }}
                      transition={{
                        duration: 1.5,
                        repeat: Infinity,
                        ease: "easeInOut"
                      }}
                    >
                      <Menu size={24} className="text-white" />
                    </motion.div>
                  </motion.div>
                )}
              </AnimatePresence>
            </motion.button>
          </div>

          {/* Enhanced Mobile Menu */}
          <AnimatePresence>
            {isOpen && (
              <motion.div
                initial={{ opacity: 0, height: 0, y: -20 }}
                animate={{ opacity: 1, height: 'auto', y: 0 }}
                exit={{ opacity: 0, height: 0, y: -20 }}
                transition={{ duration: 0.3, ease: "easeOut" }}
                className="lg:hidden overflow-hidden bg-gradient-to-br from-white to-gray-50 rounded-3xl mt-4 shadow-2xl border border-gray-100"
              >
                <div className="p-8 space-y-6">
                  {navItems.map((item, index) => (
                    <motion.a
                      key={item.name}
                      href={item.href}
                      initial={{ opacity: 0, x: -20 }}
                      animate={{ opacity: 1, x: 0 }}
                      transition={{ delay: index * 0.1 }}
                      whileHover={{ x: 10, color: "#D6001C" }}
                      onClick={() => setIsOpen(false)}
                      className="block text-brand-black hover:text-brand-red transition-colors duration-300 font-medium py-3 text-lg border-b border-gray-100 last:border-b-0"
                    >
                      {item.name}
                    </motion.a>
                  ))}
                  <motion.button
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.5 }}
                    whileHover={{ scale: 1.05 }}
                    whileTap={{ scale: 0.95 }}
                    onClick={handleReportIssue}
                    className="w-full bg-gradient-to-r from-brand-red to-red-600 text-white px-6 py-4 rounded-full font-montserrat font-semibold text-lg transition-all duration-300 hover:from-red-700 hover:to-red-800 hover:shadow-2xl border-0 outline-none focus:ring-4 focus:ring-brand-red/30 flex items-center justify-center space-x-2 mt-6"
                  >
                    <motion.div
                      animate={{ rotate: [0, 15, -15, 0] }}
                      transition={{ duration: 2, repeat: Infinity, ease: "easeInOut" }}
                    >
                      <Wrench className="w-5 h-5" />
                    </motion.div>
                    <span>Report Issue</span>
                  </motion.button>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </motion.header>

      {/* Report Issue Modal */}
      <ReportIssueModal
        isOpen={showReportModal}
        onClose={() => setShowReportModal(false)}
      />
    </>
  );
};

export default Header;
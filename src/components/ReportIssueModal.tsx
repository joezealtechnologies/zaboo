import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Wrench, AlertTriangle, User, Phone, Mail, Car, MapPin, Calendar, Send, CheckCircle } from 'lucide-react';

interface ReportIssueModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const ReportIssueModal: React.FC<ReportIssueModalProps> = ({ isOpen, onClose }) => {
  const [formData, setFormData] = useState({
    customerName: '',
    email: '',
    phone: '',
    vehicleModel: '',
    vehicleYear: '',
    licensePlate: '',
    location: '',
    issueType: '',
    urgencyLevel: '',
    description: '',
    preferredContactTime: ''
  });

  const [isSubmitted, setIsSubmitted] = useState(false);

  const issueTypes = [
    'Engine/Motor Issues',
    'Battery Problems',
    'Charging Issues',
    'Brake System',
    'Electrical Problems',
    'Air Conditioning/Heating',
    'Transmission',
    'Suspension',
    'Lights/Indicators',
    'Dashboard/Electronics',
    'Tire Issues',
    'Other'
  ];

  const urgencyLevels = [
    { value: 'low', label: 'Low - Can wait a few days', color: 'text-green-600' },
    { value: 'medium', label: 'Medium - Within 24-48 hours', color: 'text-yellow-600' },
    { value: 'high', label: 'High - Same day service needed', color: 'text-orange-600' },
    { value: 'critical', label: 'Critical - Vehicle unsafe to drive', color: 'text-red-600' }
  ];

  const contactTimes = [
    'Morning (8AM - 12PM)',
    'Afternoon (12PM - 5PM)',
    'Evening (5PM - 8PM)',
    'Anytime'
  ];

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const reportId = Date.now().toString(); // Generate a simple report ID
    const currentDate = new Date().toLocaleString();
    
    const subject = `🚨 Vehicle Issue Report #${reportId} - ${formData.issueType} (${formData.urgencyLevel.toUpperCase()} Priority)`;
    const body = `NEW VEHICLE ISSUE REPORT
========================
Report ID: #${reportId}
Submitted: ${currentDate}

CUSTOMER INFORMATION:
• Name: ${formData.customerName}
• Email: ${formData.email}
• Phone: ${formData.phone}
• Preferred Contact Time: ${formData.preferredContactTime}

VEHICLE DETAILS:
• Model: ${formData.vehicleModel}
• Year: ${formData.vehicleYear}
• License Plate: ${formData.licensePlate || 'Not provided'}
• Current Location: ${formData.location}

ISSUE DETAILS:
• Type: ${formData.issueType}
• Urgency Level: ${formData.urgencyLevel.toUpperCase()}
• Description: ${formData.description}

STATUS: PENDING REVIEW

---
NEXT STEPS:
1. Review and prioritize based on urgency level
2. Contact customer within appropriate timeframe:
   - Critical: Immediate response
   - High: Same day
   - Medium: 24-48 hours
   - Low: Within a few days
3. Assign technician
4. Schedule service appointment

Customer Contact: ${formData.email} | ${formData.phone}
Best Contact Time: ${formData.preferredContactTime}

---
This report was submitted through the FaZona EV website.
Report ID: #${reportId}
Timestamp: ${currentDate}`;

    const mailtoLink = `mailto:evfazona@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = mailtoLink;
    
    // Show success state
    setIsSubmitted(true);
    
    // Auto close after 5 seconds
    setTimeout(() => {
      handleClose();
    }, 5000);
  };

  const handleClose = () => {
    setFormData({
      customerName: '',
      email: '',
      phone: '',
      vehicleModel: '',
      vehicleYear: '',
      licensePlate: '',
      location: '',
      issueType: '',
      urgencyLevel: '',
      description: '',
      preferredContactTime: ''
    });
    setIsSubmitted(false);
    onClose();
  };

  const getUrgencyColor = (level: string) => {
    switch (level) {
      case 'low': return 'from-green-400 to-green-600';
      case 'medium': return 'from-yellow-400 to-yellow-600';
      case 'high': return 'from-orange-400 to-orange-600';
      case 'critical': return 'from-red-400 to-red-600';
      default: return 'from-gray-400 to-gray-600';
    }
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-2 sm:p-4"
          onClick={handleClose}
        >
          <motion.div
            initial={{ scale: 0.8, opacity: 0, y: 50 }}
            animate={{ scale: 1, opacity: 1, y: 0 }}
            exit={{ scale: 0.8, opacity: 0, y: 50 }}
            transition={{ type: "spring", stiffness: 300, damping: 30 }}
            className="bg-white rounded-2xl sm:rounded-3xl shadow-2xl w-full max-w-5xl max-h-[98vh] sm:max-h-[95vh] overflow-y-auto"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Header */}
            <div className="relative bg-gradient-to-r from-brand-red to-red-600 text-white p-4 sm:p-6 lg:p-8 rounded-t-2xl sm:rounded-t-3xl">
              <motion.button
                onClick={handleClose}
                whileHover={{ scale: 1.1, rotate: 90 }}
                whileTap={{ scale: 0.9 }}
                className="absolute top-3 right-3 sm:top-4 sm:right-4 lg:top-6 lg:right-6 w-8 h-8 sm:w-10 sm:h-10 bg-white/20 rounded-full flex items-center justify-center"
              >
                <X className="w-4 h-4 sm:w-5 sm:h-5" />
              </motion.button>
              
              <div className="flex items-center space-x-3 sm:space-x-4 pr-12 sm:pr-16">
                <motion.div
                  animate={{ rotate: [0, 15, -15, 0] }}
                  transition={{ duration: 2, repeat: Infinity, ease: "easeInOut" }}
                  className="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"
                >
                  <Wrench className="w-6 h-6 sm:w-7 sm:h-7 lg:w-8 lg:h-8" />
                </motion.div>
                <div className="min-w-0 flex-1">
                  <h2 className="text-xl sm:text-2xl lg:text-3xl font-montserrat font-bold leading-tight">
                    Report Vehicle Issue
                  </h2>
                  <p className="text-red-100 text-sm sm:text-base mt-1">
                    Get professional assistance for your FaZona EV
                  </p>
                </div>
              </div>
            </div>

            {/* Success State */}
            {isSubmitted ? (
              <motion.div
                initial={{ opacity: 0, scale: 0.8 }}
                animate={{ opacity: 1, scale: 1 }}
                className="p-4 sm:p-8 lg:p-12 text-center"
              >
                <motion.div
                  animate={{ scale: [1, 1.1, 1] }}
                  transition={{ duration: 1, repeat: Infinity }}
                  className="w-16 h-16 sm:w-20 sm:h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-6"
                >
                  <CheckCircle className="w-8 h-8 sm:w-10 sm:h-10 text-white" />
                </motion.div>
                <h3 className="text-xl sm:text-2xl font-montserrat font-bold text-brand-black mb-3 sm:mb-4">
                  Issue Report Submitted Successfully!
                </h3>
                <p className="text-gray-600 text-base sm:text-lg mb-4 sm:mb-6 leading-relaxed">
                  Your issue report has been sent to our technical team via email.
                  We'll contact you shortly based on the urgency level you specified.
                </p>
                <div className="bg-gray-50 rounded-xl p-4 sm:p-6 text-left">
                  <h4 className="font-semibold text-brand-black mb-3 text-sm sm:text-base">📋 Report Details:</h4>
                  <div className="space-y-2 text-xs sm:text-sm text-gray-600">
                    <p><strong>Report ID:</strong> #{Date.now().toString()}</p>
                    <p><strong>Status:</strong> <span className="text-yellow-600 font-semibold">Pending Review</span></p>
                    <p><strong>Submitted:</strong> {new Date().toLocaleString()}</p>
                    <p><strong>Priority:</strong> <span className={`font-semibold ${
                      formData.urgencyLevel === 'critical' ? 'text-red-600' :
                      formData.urgencyLevel === 'high' ? 'text-orange-600' :
                      formData.urgencyLevel === 'medium' ? 'text-yellow-600' : 'text-green-600'
                    }`}>{formData.urgencyLevel.toUpperCase()}</span></p>
                  </div>
                </div>
                <div className="mt-4 sm:mt-6 bg-blue-50 border border-blue-200 rounded-xl p-3 sm:p-4 text-xs sm:text-sm text-blue-800">
                  <p className="font-semibold mb-2">📞 Next Steps:</p>
                  <ul className="text-left space-y-1">
                    <li>• Our technical team will review your report</li>
                    <li>• You'll receive a call/email within the timeframe based on urgency</li>
                    <li>• A technician will be assigned to your case</li>
                    <li>• We'll contact you at your preferred time: {formData.preferredContactTime}</li>
                  </ul>
                </div>
              </motion.div>
            ) : (
              /* Form */
              <form onSubmit={handleSubmit} className="p-4 sm:p-6 lg:p-8 space-y-6 sm:space-y-8">
                {/* Customer Information */}
                <div>
                  <h3 className="text-lg sm:text-xl font-montserrat font-bold text-brand-black mb-4 sm:mb-6 flex items-center space-x-2">
                    <User className="w-5 h-5 sm:w-6 sm:h-6 text-brand-red flex-shrink-0" />
                    <span>Customer Information</span>
                  </h3>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div className="relative">
                      <User className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                      <input
                        type="text"
                        name="customerName"
                        value={formData.customerName}
                        onChange={handleChange}
                        required
                        placeholder="Full Name"
                        className="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base"
                      />
                    </div>
                    
                    <div className="relative">
                      <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                      <input
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={handleChange}
                        required
                        placeholder="Email Address"
                        className="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base"
                      />
                    </div>

                    <div className="relative">
                      <Phone className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                      <input
                        type="tel"
                        name="phone"
                        value={formData.phone}
                        onChange={handleChange}
                        required
                        placeholder="Phone Number"
                        className="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base"
                      />
                    </div>

                    <div className="relative">
                      <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                      <select
                                                name="preferredContactTime"
                        value={formData.preferredContactTime}
                        onChange={handleChange}
                        required
                        className="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base appearance-none bg-white"
                      >
                        <option value="">Preferred Contact Time</option>
                        {contactTimes.map((time) => (
                          <option key={time} value={time}>{time}</option>
                        ))}
                      </select>
                    </div>
                  </div>
                </div>

                {/* Vehicle Information */}
                <div>
                  <h3 className="text-lg sm:text-xl font-montserrat font-bold text-brand-black mb-4 sm:mb-6 flex items-center space-x-2">
                    <Car className="w-5 h-5 sm:w-6 sm:h-6 text-brand-red flex-shrink-0" />
                    <span>Vehicle Information</span>
                  </h3>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div className="relative">
                      <Car className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                      <input
                        type="text"
                        name="vehicleModel"
                        value={formData.vehicleModel}
                        onChange={handleChange}
                        required
                        placeholder="Vehicle Model (e.g., FaZona Premium)"
                        className="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base"
                      />
                    </div>
                    
                    <div className="relative">
                      <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                      <input
                        type="text"
                        name="vehicleYear"
                        value={formData.vehicleYear}
                        onChange={handleChange}
                        required
                        placeholder="Year (e.g., 2024)"
                        className="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base"
                      />
                    </div>

                    <div className="relative">
                      <input
                        type="text"
                        name="licensePlate"
                        value={formData.licensePlate}
                        onChange={handleChange}
                        placeholder="License Plate Number (Optional)"
                        className="w-full px-3 sm:px-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base"
                      />
                    </div>

                    <div className="relative">
                      <MapPin className="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                      <input
                        type="text"
                        name="location"
                        value={formData.location}
                        onChange={handleChange}
                        required
                        placeholder="Current Location/City"
                        className="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base"
                      />
                    </div>
                  </div>
                </div>

                {/* Issue Details */}
                <div>
                  <h3 className="text-lg sm:text-xl font-montserrat font-bold text-brand-black mb-4 sm:mb-6 flex items-center space-x-2">
                    <AlertTriangle className="w-5 h-5 sm:w-6 sm:h-6 text-brand-red flex-shrink-0" />
                    <span>Issue Details</span>
                  </h3>
                  
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div>
                      <label className="block text-xs sm:text-sm font-semibold text-brand-black mb-2">
                        Issue Type *
                      </label>
                      <select
                        name="issueType"
                        value={formData.issueType}
                        onChange={handleChange}
                        required
                        className="w-full px-3 sm:px-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base appearance-none bg-white"
                      >
                        <option value="">Select Issue Type</option>
                        {issueTypes.map((type) => (
                          <option key={type} value={type}>{type}</option>
                        ))}
                      </select>
                    </div>

                    <div>
                      <label className="block text-xs sm:text-sm font-semibold text-brand-black mb-2">
                        Urgency Level *
                      </label>
                      <select
                        name="urgencyLevel"
                        value={formData.urgencyLevel}
                        onChange={handleChange}
                        required
                        className="w-full px-3 sm:px-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 text-sm sm:text-base appearance-none bg-white"
                      >
                        <option value="">Select Urgency Level</option>
                        {urgencyLevels.map((level) => (
                          <option key={level.value} value={level.value}>{level.label}</option>
                        ))}
                      </select>
                    </div>
                  </div>

                  {/* Urgency Indicator */}
                  {formData.urgencyLevel && (
                    <motion.div
                      initial={{ opacity: 0, scale: 0.8 }}
                      animate={{ opacity: 1, scale: 1 }}
                      className={`mb-4 sm:mb-6 p-3 sm:p-4 rounded-xl bg-gradient-to-r ${getUrgencyColor(formData.urgencyLevel)} text-white`}
                    >
                      <div className="flex items-center space-x-2">
                        <AlertTriangle className="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" />
                        <span className="font-semibold text-sm sm:text-base">
                          {urgencyLevels.find(l => l.value === formData.urgencyLevel)?.label}
                        </span>
                      </div>
                      {formData.urgencyLevel === 'critical' && (
                        <p className="mt-2 text-xs sm:text-sm leading-relaxed">
                          ⚠️ For immediate safety concerns, please call +234 913 585 9888 or contact emergency services.
                        </p>
                      )}
                    </motion.div>
                  )}

                  <div>
                    <label className="block text-xs sm:text-sm font-semibold text-brand-black mb-2">
                      Detailed Description *
                    </label>
                    <textarea
                      name="description"
                      value={formData.description}
                      onChange={handleChange}
                      required
                      rows={4}
                      placeholder="Please describe the issue in detail. Include when it started, what symptoms you're experiencing, and any error messages..."
                      className="w-full px-3 sm:px-4 py-3 sm:py-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 resize-none text-sm sm:text-base"
                    />
                  </div>
                </div>

                {/* Submit Button */}
                <motion.button
                  type="submit"
                  whileHover={{
                    scale: 1.02,
                    boxShadow: "0 15px 35px rgba(214, 0, 28, 0.3)"
                  }}
                  whileTap={{ scale: 0.98 }}
                  className="w-full bg-gradient-to-r from-brand-red to-red-600 hover:from-red-700 hover:to-red-800 text-white flex items-center justify-center space-x-2 sm:space-x-3 text-base sm:text-lg py-3 sm:py-4 rounded-xl font-montserrat font-semibold transition-all duration-300"
                >
                  <Send className="w-5 h-5 sm:w-6 sm:h-6" />
                  <span>Send Issue Report</span>
                </motion.button>

                {/* Help Text */}
                <div className="bg-blue-50 border border-blue-200 rounded-xl p-3 sm:p-4 text-xs sm:text-sm text-blue-800">
                  <p className="font-semibold mb-2">📞 Emergency Contact:</p>
                  <p className="leading-relaxed">If this is a critical safety issue or emergency, please call us immediately at <strong>+234 913 585 9888</strong> or contact emergency services if needed.</p>
                </div>

                {/* Email Info */}
                <div className="bg-green-50 border border-green-200 rounded-xl p-3 sm:p-4 text-xs sm:text-sm text-green-800">
                  <p className="font-semibold mb-2">📧 Direct Email:</p>
                  <p className="leading-relaxed">Your report will be sent directly to our technical team at <strong>evfazona@gmail.com</strong> for immediate attention and response.</p>
                </div>
              </form>
            )}
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
};

export default ReportIssueModal;

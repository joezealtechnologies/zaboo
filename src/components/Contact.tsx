import { useState } from 'react';
import { motion } from 'framer-motion';
import { useInView } from 'react-intersection-observer';
import { Mail, Phone, Send, MessageCircle, Instagram } from 'lucide-react';

const Contact = () => {
  const [ref, inView] = useInView({
    triggerOnce: true,
    threshold: 0.1,
  });

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    vehicle: '',
    message: ''
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const subject = `Contact Form Submission from ${formData.name}`;
    const body = `New contact form submission:

Name: ${formData.name}
Email: ${formData.email}
Phone: ${formData.phone}
Interested Vehicle: ${formData.vehicle || 'Not specified'}

Message:
${formData.message}

---
This message was sent from the FaZona EV website contact form.`;

    const mailtoLink = `mailto:evfazona@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = mailtoLink;
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleGetQuote = () => {
    const subject = `General Quote Request`;
    const body = `Hello FaZona EV Team,

I am interested in getting a quote for your electric vehicles.

Please provide me with more information including:
- Available models and pricing
- Financing options
- Test drive scheduling
- Delivery timeline

My contact details:
Name: 
Phone: 
Email: 
Location: 

Thank you for your time.

Best regards,`;

    const mailtoLink = `mailto:evfazona@gmail.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
    window.location.href = mailtoLink;
  };

  const contactInfo = [
    {
      icon: Mail,
      title: 'Email Us',
      details: 'evfazona@gmail.com',
      action: 'Send Email',
      onClick: () => window.location.href = 'mailto:evfazona@gmail.com'
    },
    {
      icon: Phone,
      title: 'WhatsApp Us',
      details: '+234 913 585 9888',
      action: 'Chat on WhatsApp',
      onClick: () => window.open('https://wa.me/2349135859888', '_blank')
    },
    {
      icon: Instagram,
      title: 'Follow Us',
      details: '@fazona_ev',
      action: 'View Instagram',
      onClick: () => window.open('https://www.instagram.com/fazona_ev?igsh=MTdqeTZvMno4d294eQ%3D%3D&utm_source=qr', '_blank')
    }
  ];

  return (
    <section id="contact" className="py-20 bg-gray-50">
      <div className="container-max section-padding">
        <motion.div
          ref={ref}
          initial={{ opacity: 0, y: 50 }}
          animate={inView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.8 }}
          className="text-center mb-16"
        >
          <h2 className="text-4xl lg:text-5xl font-montserrat font-bold text-brand-black mb-6">
            Get in <span className="gradient-text">Touch</span>
          </h2>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            Ready to join the electric revolution? Contact us today to learn more about 
            our vehicles or schedule a test drive.
          </p>
        </motion.div>

        <div className="grid lg:grid-cols-2 gap-16">
          {/* Contact Form */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            animate={inView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.8, delay: 0.2 }}
            className="bg-white rounded-3xl p-8 shadow-lg"
          >
            <div className="flex items-center space-x-3 mb-8">
              <motion.div 
                className="w-12 h-12 bg-brand-red rounded-full flex items-center justify-center"
                animate={{ rotate: [0, 360] }}
                transition={{ duration: 4, repeat: Infinity, ease: "linear" }}
              >
                <MessageCircle className="w-6 h-6 text-white" />
              </motion.div>
              <h3 className="text-2xl font-montserrat font-bold text-brand-black">
                Send us a Message
              </h3>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-semibold text-brand-black mb-2">
                    Full Name *
                  </label>
                  <input
                    type="text"
                    name="name"
                    value={formData.name}
                    onChange={handleChange}
                    required
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                    placeholder="Your full name"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-brand-black mb-2">
                    Email Address *
                  </label>
                  <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    required
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                    placeholder="your@email.com"
                  />
                </div>
              </div>

              <div className="grid md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-semibold text-brand-black mb-2">
                    Phone Number
                  </label>
                  <input
                    type="tel"
                    name="phone"
                    value={formData.phone}
                    onChange={handleChange}
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                    placeholder="+234 (0) 123 456 7890"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold text-brand-black mb-2">
                    Interested Vehicle
                  </label>
                  <select
                    name="vehicle"
                    value={formData.vehicle}
                    onChange={handleChange}
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300"
                  >
                    <option value="">Select a vehicle</option>
                    <option value="premium-long-range">Premium Long Range (₦20M)</option>
                    <option value="mid-range">Mid-Range Model (₦12M)</option>
                    <option value="standard-range">Standard Range (₦9.5M)</option>
                    <option value="compact-entry">Compact Entry (₦6.5M)</option>
                    <option value="tricycle">Electric Tricycle</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-sm font-semibold text-brand-black mb-2">
                  Message *
                </label>
                <textarea
                  name="message"
                  value={formData.message}
                  onChange={handleChange}
                  required
                  rows={5}
                  className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-red focus:border-transparent transition-all duration-300 resize-none"
                  placeholder="Tell us about your requirements or questions..."
                />
              </div>

              <motion.button
                type="submit"
                whileHover={{ 
                  scale: 1.05,
                  boxShadow: "0 10px 25px rgba(214, 0, 28, 0.3)"
                }}
                whileTap={{ scale: 0.95 }}
                className="w-full btn-primary flex items-center justify-center space-x-2"
              >
                <Send className="w-5 h-5" />
                <span>Send Message</span>
              </motion.button>
            </form>
          </motion.div>

          {/* Contact Information */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            animate={inView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.8, delay: 0.4 }}
            className="space-y-8"
          >
            <div>
              <h3 className="text-3xl font-montserrat font-bold text-brand-black mb-6">
                Let's Start a Conversation
              </h3>
              <p className="text-lg text-gray-600 leading-relaxed mb-8">
                Whether you're interested in purchasing a vehicle, need technical support, 
                or want to learn more about our electric mobility solutions, we're here to help.
              </p>
            </div>

            {/* Contact Cards */}
            <div className="space-y-6">
              {contactInfo.map((info, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 30 }}
                  animate={inView ? { opacity: 1, y: 0 } : {}}
                  transition={{ duration: 0.8, delay: 0.6 + index * 0.1 }}
                  className="bg-white rounded-2xl p-6 shadow-lg card-hover cursor-pointer"
                  onClick={info.onClick}
                >
                  <div className="flex items-center space-x-4">
                    <motion.div 
                      className="w-12 h-12 bg-gradient-to-r from-brand-red to-red-600 rounded-xl flex items-center justify-center"
                      whileHover={{ scale: 1.1, rotate: 5 }}
                    >
                      <info.icon className="w-6 h-6 text-white" />
                    </motion.div>
                    <div className="flex-1">
                      <h4 className="font-montserrat font-semibold text-brand-black mb-1">
                        {info.title}
                      </h4>
                      <p className="text-gray-600 mb-2">{info.details}</p>
                      <button className="text-brand-red font-semibold hover:underline">
                        {info.action}
                      </button>
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>

            {/* CTA Section */}
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              animate={inView ? { opacity: 1, y: 0 } : {}}
              transition={{ duration: 0.8, delay: 0.9 }}
              className="bg-gradient-to-r from-brand-red to-red-600 rounded-2xl p-8 text-white"
            >
              <h4 className="text-2xl font-montserrat font-bold mb-4">
                Ready to Go Electric?
              </h4>
              <p className="text-red-100 mb-6 leading-relaxed">
                Schedule a test drive today and experience the future of transportation firsthand.
              </p>
              <motion.button
                whileHover={{ 
                  scale: 1.05,
                  boxShadow: "0 10px 25px rgba(0,0,0,0.2)"
                }}
                whileTap={{ scale: 0.95 }}
                onClick={handleGetQuote}
                className="bg-white text-brand-red px-6 py-3 rounded-full font-semibold hover:shadow-lg transition-all duration-300 flex items-center space-x-2"
              >
                <Mail className="w-5 h-5" />
                <span>Get Quote Now</span>
              </motion.button>
            </motion.div>
          </motion.div>
        </div>
      </div>
    </section>
  );
};

export default Contact;
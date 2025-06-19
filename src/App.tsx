import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Header from './components/Header';
import Hero from './components/Hero';
import Features from './components/Features';
import Vehicles from './components/Vehicles';
import About from './components/About';
import Contact from './components/Contact';
import Footer from './components/Footer';
import AdminPanel from './components/admin/AdminPanel';

function App() {
  return (
    <Router>
      <Routes>
        {/* Admin Route */}
        <Route path="/admin" element={<AdminPanel />} />
        
        {/* Main Website */}
        <Route path="/" element={
          <div className="min-h-screen bg-brand-white">
            <Header />
            <Hero />
            <Features />
            <Vehicles />
            <About />
            <Contact />
            <Footer />
          </div>
        } />
      </Routes>
    </Router>
  );
}

export default App;
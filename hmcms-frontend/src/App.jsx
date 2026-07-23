import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import MainLayout from './layouts/MainLayout';
import DoctorScheduling from './pages/DoctorScheduling';
import HealthWorkerAllocation from './pages/HealthWorkerAllocation';
import MedicineInventory from './pages/MedicineInventory';
import CampNotifications from './pages/CampNotifications';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<MainLayout />}>
          <Route index element={<Navigate to="/scheduling" replace />} />
          <Route path="scheduling" element={<DoctorScheduling />} />
          <Route path="allocation" element={<HealthWorkerAllocation />} />
          <Route path="inventory" element={<MedicineInventory />} />
          <Route path="notifications" element={<CampNotifications />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}

export default App;

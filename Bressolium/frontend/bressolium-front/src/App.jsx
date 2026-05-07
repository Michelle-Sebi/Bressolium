import AppRoutes from './routes/AppRoutes';
import { ToastProvider } from './contexts/ToastContext';

function App() {
  return (
    <ToastProvider>
      <div className="min-h-screen bg-[#f7f9f7]">
        <AppRoutes />
      </div>
    </ToastProvider>
  );
}

export default App;

/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useState, useCallback } from 'react';

export const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser]   = useState(() => {
    try { return JSON.parse(localStorage.getItem('clinic_user')); } catch { return null; }
  });
  const [token, setToken] = useState(() => localStorage.getItem('clinic_token'));

  const login = useCallback((userData, authToken) => {
    localStorage.setItem('clinic_user',  JSON.stringify(userData));
    localStorage.setItem('clinic_token', authToken);
    setUser(userData);
    setToken(authToken);
  }, []);

  const logout = useCallback(() => {
    localStorage.removeItem('clinic_user');
    localStorage.removeItem('clinic_token');
    setUser(null);
    setToken(null);
  }, []);

  const role = user?.role ?? null;
  const isAuthenticated = !!token;

  return (
    <AuthContext.Provider value={{ user, token, role, isAuthenticated, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used inside AuthProvider');
  return ctx;
}

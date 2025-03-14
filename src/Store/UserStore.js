import { defineStore } from 'pinia';
import * as jwtDecode from 'jwt-decode';

export const useUserStore = defineStore('user', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || {},
  }),
  getters: {
    isAuthenticated(state) {
      return !!state.token && this.isTokenValid;
    },
    isTokenValid(state) {
      if (!state.token) return false;
      try {
        const decoded = jwtDecode(state.token);
        return decoded.exp * 1000 > Date.now();
      } catch (error) {
        console.error('Invalid token:', error);
        return false;
      }
    },
  },
  actions: {
    setToken(token) {
      this.token = token;
      localStorage.setItem('token', token);
    },
    removeToken() {
      this.token = null;
      localStorage.removeItem('token');
    },
    setUser(user) {
      this.user = user;
      localStorage.setItem('user', JSON.stringify(user));
    },
    updateProfilePicture(pictureUrl) {
      this.user.profile_picture_url = pictureUrl;
      localStorage.setItem('user', JSON.stringify(this.user));
    },
    logout() {
      this.removeToken();
      this.user = {};
      localStorage.removeItem('user');
    },
  },
});

import { defineStore } from 'pinia';
import { jwtDecode } from 'jwt-decode';

export const useUserStore = defineStore('user', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
    isAdmin: JSON.parse(localStorage.getItem('user'))?.role === 'admin' || false,
    profilePicture: JSON.parse(localStorage.getItem('user'))?.profile_picture_url || '/images/profile.png',
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
      this.isAdmin = user?.role === 'admin';
      this.profilePicture = user?.profile_picture_url || '/images/profile.png';
      localStorage.setItem('user', JSON.stringify(user));
    },

    updateProfilePicture(pictureUrl) {
      if (this.user) {
        this.user.profile_picture_url = pictureUrl;
        this.profilePicture = pictureUrl;
        localStorage.setItem('user', JSON.stringify(this.user));
      }
    },

    restoreSession() {
      const storedUser = localStorage.getItem('user');
      const storedToken = localStorage.getItem('token');

      if (storedToken && this.isTokenValid) {
        this.token = storedToken;
        this.user = storedUser ? JSON.parse(storedUser) : null;
        this.isAdmin = this.user?.role === 'admin';
        this.profilePicture = this.user?.profile_picture_url || '/images/profile.png';
      } else {
        this.logout();
      }
    },

    logout() {
      this.$reset();
    },

    $reset() {
      this.token = null;
      this.user = null;
      this.isAdmin = false;
      this.profilePicture = '/images/profile.png';

      localStorage.removeItem('token');
      localStorage.removeItem('user');
      sessionStorage.clear();
    },
  },
});
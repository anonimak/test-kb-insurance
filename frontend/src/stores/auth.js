import { defineStore } from "pinia";
import axios from "axios";

const API_URL = "/api";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: JSON.parse(localStorage.getItem("user")) || null,
    token: localStorage.getItem("token") || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    getUser: (state) => state.user,
  },

  actions: {
    async register(userData) {
      try {
        this.loading = true;
        this.error = null;

        const response = await axios.post(`${API_URL}/register`, userData);

        return response.data;
      } catch (error) {
        const errorMessage =
          error.response?.data?.messages ||
          error.response?.data?.message ||
          error.response?.data?.error ||
          "Registration failed";
        this.error = errorMessage;
        throw new Error(errorMessage);
      } finally {
        this.loading = false;
      }
    },

    async login(credentials) {
      try {
        this.loading = true;
        this.error = null;

        const response = await axios.post(`${API_URL}/login`, credentials);

        const { token, user } = response.data;

        this.token = token;
        this.user = user;

        localStorage.setItem("token", token);
        localStorage.setItem("user", JSON.stringify(user));

        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        return user;
      } catch (error) {
        const errorMessage =
          error.response?.data?.messages ||
          error.response?.data?.message ||
          error.response?.data?.error ||
          "Login failed";
        this.error = errorMessage;
        throw new Error(errorMessage);
      } finally {
        this.loading = false;
      }
    },

    async getProfile() {
      try {
        this.loading = true;

        const response = await axios.get(`${API_URL}/profile`, {
          headers: { Authorization: `Bearer ${this.token}` },
        });

        // Update user data
        this.user = response.data.user;
        localStorage.setItem("user", JSON.stringify(this.user));

        return this.user;
      } catch (error) {
        if (error.response?.status === 401) {
          // Token expired or invalid
          this.logout();
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        if (this.token) {
          await axios.post(
            `${API_URL}/logout`,
            {},
            {
              headers: { Authorization: `Bearer ${this.token}` },
            }
          );
        }
      } catch (error) {
        console.error("Logout error:", error);
      } finally {
        // Clear state and storage regardless of API call result
        this.user = null;
        this.token = null;
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        delete axios.defaults.headers.common["Authorization"];
      }
    },

    initializeAuth() {
      const token = localStorage.getItem("token");
      if (token) {
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
      }
    },
  },
});

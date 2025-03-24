import axios from "axios";

const API_URL = "/api";

export default {
  getCustomers(page = 1) {
    return axios.get(`${API_URL}/customers?page=${page}`);
  },

  getCustomer(id) {
    return axios.get(`${API_URL}/customers/${id}`);
  },

  createCustomer(customerData) {
    return axios.post(`${API_URL}/customers`, customerData);
  },

  updateCustomer(id, customerData) {
    return axios.put(`${API_URL}/customers/${id}`, customerData);
  },

  deleteCustomer(id) {
    return axios.delete(`${API_URL}/customers/${id}`);
  },

  getCoverageTypes() {
    return axios.get(`${API_URL}/coverage-types`);
  },
};

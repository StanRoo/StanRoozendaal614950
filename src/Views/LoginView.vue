<template>
  <div class="login-container d-flex align-items-center justify-content-center vh-100">
    <div class="card p-4 shadow-lg">
      <div class="text-center mb-4">
        <img src="@/assets/icons/CubocardLogo.png" alt="Logo" style="width: 80px;" />
      </div>
      <h2 class="text-center mb-4">Login</h2>
      <form @submit.prevent="login">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input
            type="text"
            id="username"
            class="form-control"
            v-model="username"
            placeholder="Enter your username"
            required
          />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input
            type="password"
            id="password"
            class="form-control"
            v-model="password"
            placeholder="Enter your password"
            required
          />
        </div>

        <div class="form-check mb-3">
          <input type="checkbox" class="form-check-input" id="rememberMe" v-model="rememberMe" />
          <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

        <div class="text-center mt-3">
          <router-link to="/forgotPassword" class="small text-primary">
            Forgot password?
          </router-link><br />
          <router-link to="/createAccount" class="small text-primary">
            Create an account
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useUserStore } from '@/Store/UserStore';

export default {
  data() {
    return {
      username: "",
      password: "",
      rememberMe: false,
      errorMessage: "",
    };
  },

  methods: {
    async login() {
      this.errorMessage = "";
      try {
        const response = await axios.post("/login", {
          username: this.username,
          password: this.password,
        });

        if (response.status === 200 && response.data.token && response.data.user) {
          const userStore = useUserStore();
          userStore.setToken(response.data.token);
          userStore.setUser(response.data.user);

          if (this.rememberMe) {
            localStorage.setItem("token", response.data.token);
            localStorage.setItem("user", JSON.stringify(response.data.user));
          } else {
            sessionStorage.setItem("token", response.data.token);
            sessionStorage.setItem("user", JSON.stringify(response.data.user));
          }
          this.$router.push("/home");
        } else {
          this.errorMessage = "Login failed. Invalid response from server.";
        }
      } catch (error) {
        if (error.response) {
          this.errorMessage = error.response.data?.message || "Login failed.";
        } else if (error.request) {
          this.errorMessage = "Network error. Please check your connection.";
        } else {
          this.errorMessage = "An unexpected error occurred.";
        }
      }
    }
  }
};
</script>

<style scoped>
html, body {
  margin: 0;
  padding: 0;
  overflow-x: hidden;
  width: 100%;
}

.login-container {
  background-color: #f8f9fa;
  height: 100vh;
}

.card {
  width: 100%;
  max-width: 400px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.card h2 {
  font-weight: bold;
}

.form-control:focus {
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.error {
  color: red;
}
</style>
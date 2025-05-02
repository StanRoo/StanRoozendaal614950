<template>
  <div class="login-container">
    <div class="card login-card">
      <div class="logo-container">
        <img src="@/assets/icons/CubocardLogo.png" alt="Logo" class="logo" />
      </div>
      <h2 class="text-center mb-4">Login</h2>

      <form @submit.prevent="login">
        <div class="form-group">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            class="form-control"
            v-model="username"
            placeholder="Enter your username"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
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

        <div v-if="errorMessage" class="error-container">
          <p class="error">{{ errorMessage }}</p>
        </div>

        <div class="text-center mt-3 links">
          <router-link to="/forgotPassword">Forgot password?</router-link><br />
          <router-link to="/createAccount">Create an account</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { useUserStore } from "@/Store/UserStore";
import { handleApiError } from "@/Utils/errorHandler";

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
        this.errorMessage = handleApiError(error);
      }
    }
  }
};
</script>

<style scoped>
html, body {
  margin: 0;
  padding: 0;
  font-size: 16px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8f9fa;
}

.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
}

.login-card {
  width: 100%;
  max-width: 25rem;
  padding: 2rem;
  border-radius: 0.75rem;
  background: white;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.logo-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}
.logo {
  width: 5rem;
  max-width: 20vw;
}

.form-group {
  margin-bottom: 1.25rem;
}

label {
  font-weight: 600;
  margin-bottom: 0.25rem;
  display: block;
}

input.form-control {
  width: 100%;
  padding: 0.6rem 1rem;
  font-size: 1rem;
  border-radius: 0.5rem;
  border: 1px solid #ced4da;
}

input.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  outline: none;
}

.error-container {
  margin-top: 0.5rem;
  text-align: center;
}
.error {
  color: red;
  font-size: 0.9rem;
}

.links a {
  font-size: 0.9rem;
  color: #0d6efd;
  text-decoration: none;
}
.links a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .login-card {
    padding: 1.5rem;
  }
  .logo {
    width: 4rem;
  }
}

@media (max-width: 480px) {
  html {
    font-size: 0.9rem;
  }
  .login-card {
    padding: 1.25rem;
  }
  .form-group {
    margin-bottom: 1rem;
  }
}
</style>
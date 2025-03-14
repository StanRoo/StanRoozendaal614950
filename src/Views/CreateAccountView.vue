<template>
  <div class="create-account-container vh-100 d-flex align-items-center justify-content-center">
    <div class="card p-4 shadow-lg">
      <div class="text-center mb-4">
        <img src="@/assets/icons/CubocardLogo.png" alt="Logo" style="width: 80px;" />
      </div>
      <h2 class="text-center mb-4">Create an Account</h2>
      <form @submit.prevent="handleCreateAccount">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input
            type="text"
            id="username"
            v-model="username"
            class="form-control"
            placeholder="Enter your username"
            required
          />
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input
            type="email"
            id="email"
            v-model="email"
            class="form-control"
            placeholder="Enter your email"
            required
          />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <p class="password-userinfo">(must contain min. 8 characters, upper- lowercase letter, number, special character)</p>
          <input
            type="password"
            id="password"
            v-model="password"
            class="form-control"
            placeholder="Enter your password"
            required
          />
        </div>

        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Confirm Password</label>
          <input
            type="password"
            id="confirmPassword"
            v-model="confirmPassword"
            class="form-control"
            placeholder="Confirm your password"
            required
          />
        </div>

        <button class="btn btn-primary w-100" @click="handleCreateAccount" :disabled="isSubmitting">
          {{ isSubmitting ? "Creating Account..." : "Create Account" }}
        </button>

        <div class="text-center mt-3">
          <router-link to="/" class="small">Back to Login</router-link>
        </div>
      </form>

      <div class="user-feedback">
        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
        <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      username: "",
      email: "",
      password: "",
      confirmPassword: "",
      errorMessage: "",
      successMessage: "",
      isSubmitting: false,
    };
  },
  methods: {
    async handleCreateAccount() {
      if (this.isSubmitting) return;
      this.isSubmitting = true;
      this.errorMessage = "";
      this.successMessage = "";

      try {
        const response = await axios.post("/register", {
          username: this.username,
          email: this.email,
          password: this.password,
          confirmPassword: this.confirmPassword,
        });

        if (response.status === 201 || response.data.message === "Account created successfully!") {
          this.successMessage = "Account created successfully! Redirecting...";
          setTimeout(() => {
            this.$router.push("/");
          }, 2000);
          return;
        }

        this.errorMessage = response.data.message || "Failed to create account.";
      } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
          this.errorMessage = error.response.data.message;
        } else {
          this.errorMessage = "An error occurred. Please try again.";
        }
      } finally {
        this.isSubmitting = false;
      }
    },
  },
};
</script>

<style scoped>
  .create-account-container {
    background-color: #f8f9fa;
  }

  .card {
    max-width: 400px;
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }

  .password-userinfo {
    font-weight: lighter;
    font-size: small;
    font-style: italic;
    margin-top: 0px;
  }

  .error-message {
    color: red;
    margin-top: 10px;
  }

  .success-message {
    color: green;
    margin-top: 10px;
  }
</style>
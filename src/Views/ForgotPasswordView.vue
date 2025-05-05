<template>
  <div class="forgot-password-container">
    <div class="card forgot-password-card">

      <!--Logo-->
      <div class="logo-container">
        <img src="@/assets/icons/CubocardLogo.png" alt="Logo" class="logo" />
      </div>

      <h2 class="text-center mb-4">Forgot Password</h2>

      <!--Form-->
      <form @submit.prevent="handleForgotPassword">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            type="email"
            id="email"
            v-model="email"
            class="form-control"
            placeholder="Enter your email"
            required
          />
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="isSubmitting">
          {{ isSubmitting ? 'Sending Reset Link...' : 'Send Reset Link' }}
        </button>

        <div class="text-center mt-3">
          <router-link to="/" class="small">Back to Login</router-link>
        </div>
      </form>

      <!--User Feedback-->
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <p v-if="successMessage" class="succes">{{ successMessage }}</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "ForgotPassword",
  data() {
    return {
      email: "",
      errorMessage: "",
      successMessage: "",
      isSubmitting: false,
    };
  },
  methods: {
    async handleForgotPassword() {
      if (this.isSubmitting) return;
      this.isSubmitting = true;

      try {
        const response = await axios.post("/forgot-password", {
          email: this.email,
        });

        if (response.status === 200) {
          this.successMessage = "A password reset link has been sent to your email address.";
          setTimeout(() => (this.successMessage = ''), 3000);
          this.email = "";
        } else {
          this.errorMessage = response.data.message || "Failed to send reset link. Please try again.";
          setTimeout(() => (this.errorMessage = ''), 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => (this.errorMessage = ''), 3000);
      } finally {
        this.isSubmitting = false;
      }
    },
  },
};
</script>

<style scoped>
html,
body {
  margin: 0;
  padding: 0;
  font-size: 16px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8f9fa;
}

.forgot-password-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
}

.forgot-password-card {
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

.text-center {
  text-align: center;
}

.succes {
  text-align: center;
  color: green;
  margin-top: 5px;
}

.error {
  text-align: center;
  color: red;
  margin-top: 5px;
}

@media (max-width: 768px) {
  .forgot-password-card {
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
  .forgot-password-card {
    padding: 1.25rem;
  }
  .form-group {
    margin-bottom: 1rem;
  }
}
</style>
<template>
  <div class="create-account-container">
    <div class="card create-account-card">
      <div class="logo-container">
        <img src="@/assets/icons/CubocardLogo.png" alt="Logo" class="logo" />
      </div>
      <h2 class="text-center mb-4">Create an Account</h2>
      <form @submit.prevent="handleCreateAccount">
        <div class="form-group">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            v-model="username"
            class="form-control"
            placeholder="Enter your username"
            required
          />
        </div>

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

        <div class="form-group">
          <label for="password">Password</label>
          <p class="password-userinfo">(min. 8 characters, upper/lowercase, number, special char)</p>
          <input
            type="password"
            id="password"
            v-model="password"
            class="form-control"
            placeholder="Enter your password"
            required
          />
        </div>

        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input
            type="password"
            id="confirmPassword"
            v-model="confirmPassword"
            class="form-control"
            placeholder="Confirm your password"
            required
          />
        </div>

        <button class="btn btn-primary w-100" :disabled="isSubmitting">
          {{ isSubmitting ? "Creating Account..." : "Create Account" }}
        </button>

        <div class="text-center mt-3">
          <router-link to="/" class="small">Back to Login</router-link>
        </div>
      </form>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <p v-if="successMessage" class="succes">{{ successMessage }}</p>
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

      if (this.password !== this.confirmPassword) {
        this.errorMessage = "Passwords do not match.";
        this.isSubmitting = false;
        setTimeout(() => { this.errorMessage = ""; }, 3000);
        return;
      }

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
        } else {
          this.errorMessage = response.data.message || "Failed to create account.";
          setTimeout(() => { this.errorMessage = "";}, 3000);
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessage = "";}, 3000);
      } finally {
        this.isSubmitting = false;
      }
    },
  },
};
</script>

<style scoped>
html, body {
  margin: 0;
  padding: 0;
  font-size: 1rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8f9fa;
}

.create-account-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 2rem;
  background-color: #f8f9fa;
}

.create-account-card {
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

.password-userinfo {
  font-weight: 300;
  font-size: 0.8rem;
  font-style: italic;
  margin: 0.25rem 0 0.5rem;
  color: #6c757d;
}

.error {
  text-align: center;
  color: red;
  margin-top: 5px;
}

.succes {
  text-align: center;
  color: green;
  margin-top: 5px;
}

@media (max-width: 768px) {
  .create-account-card {
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
  .create-account-card {
    padding: 1.25rem;
  }
  .form-group {
    margin-bottom: 1rem;
  }
}
</style>
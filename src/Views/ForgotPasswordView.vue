<template>
    <div class="forgot-password-container vh-100 d-flex align-items-center justify-content-center">
      <div class="card p-4 shadow-lg">
        <div class="text-center mb-4">
          <img src="@/assets/icons/CubocardLogo.png" alt="Logo" style="width: 80px;" />
        </div>
        <h2 class="text-center mb-4">Forgot Password</h2>
        <form @submit.prevent="handleForgotPassword">
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

          <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
          <div class="text-center mt-3">
            <router-link to="/" class="small">Back to Login</router-link>
          </div>
        </form>
      </div>
    </div>
</template>
  
<script>
  export default {
    name: "ForgotPassword",
    data() {
      return {
        email: "", 
      };
    },
    methods: {
      async handleForgotPassword() {
        if (this.email) {
          try {
            alert(`A password reset link has been sent to ${this.email}`);
            this.email = "";
          } catch (error) {
            console.error("Error:", error);
            alert("Failed to send reset link. Please try again.");
          }
        }
      },
    },
  };
</script>
  
<style scoped>
  .forgot-password-container {
    background-color: #f8f9fa;
  }
  
  .card {
    max-width: 400px;
    width: 100%;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }
</style>
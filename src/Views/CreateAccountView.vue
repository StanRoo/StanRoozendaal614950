<template>
    <div class="create-account-container vh-100 d-flex align-items-center justify-content-center">
      <div class="card p-4 shadow-lg">
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
  
          <button type="submit" class="btn btn-primary w-100">Create Account</button>
          <div class="text-center mt-3">
            <router-link to="/" class="small">Back to Login</router-link>
          </div>
        </form>
      </div>
    </div>
</template>
  
<script>
  export default {
    name: "CreateAccount",
    data() {
      return {
        username: "",
        email: "",
        password: "",
        confirmPassword: "",
      };
    },
    methods: {
      async handleCreateAccount() {
        if (this.password !== this.confirmPassword) {
          alert("Passwords do not match!");
          return;
        }
  
        try {
          alert(`Account created for ${this.username}!`);
          this.username = "";
          this.email = "";
          this.password = "";
          this.confirmPassword = "";
        } catch (error) {
          console.error("Error creating account:", error);
          alert("Failed to create account. Please try again.");
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

</style>
<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const token = ref(route.query.token || '');
const password = ref('');
const confirmPassword = ref('');
const error = ref('');
const success = ref('');

const submit = async () => {
  error.value = '';
  success.value = '';

  if (!password.value || !confirmPassword.value) {
    error.value = 'Please fill in all fields.';
    return;
  }

  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match.';
    return;
  }

  try {
    const response = await axios.post('/reset-password', {
      token: token.value,
      newPassword: password.value,
    });

    if (response.data.success) {
      success.value = 'Your password has been reset! Redirecting to login...';
      setTimeout(() => router.push('/login'), 3000);
    } else {
      error.value = response.data.message || 'An error occurred.';
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Server error.';
  }
};
</script>

<template>
<div class="reset-password-container">
    <div class="card reset-password-card">

        <!--Logo-->
        <div class="logo-container">
            <img src="@/assets/icons/CubocardLogo.png" alt="Logo" class="logo" />
        </div>
        <h2 class="text-center mb-4">Reset Your Password</h2>

        <!--Form-->
        <form @submit.prevent="submit">
            <div class="form-group">
                <label for="password">New Password</label>
                <input
                    type="password"
                    id="password"
                    class="form-control"
                    v-model="password"
                    placeholder="Enter your new password"
                    required
                />
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input
                    type="password"
                    id="confirmPassword"
                    class="form-control"
                    v-model="confirmPassword"
                    placeholder="Confirm your new password"
                    required
                />
            </div>

            <button type="submit" class="btn btn-primary w-100" :disabled="isSubmitting">
            {{ isSubmitting ? "Resetting..." : "Reset Password" }}
            </button>

            <!--User Feedback-->
            <div v-if="error" class="error-container">
                <p class="error">{{ error }}</p>
            </div>
            <div v-if="success" class="success-container">
                <p class="success">{{ success }}</p>
            </div>
        </form>
    </div>
</div>
</template>

<style scoped>
html, body {
  margin: 0;
  padding: 0;
  font-size: 16px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8f9fa;
}

.reset-password-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
}

.reset-password-card {
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

button {
  margin-top: 1.5rem;
  padding: 0.6rem;
  background-color: #0d6efd;
  border: none;
  color: white;
  font-size: 1.1rem;
  border-radius: 0.5rem;
  cursor: pointer;
}

button:disabled {
  background-color: #b6c1d3;
}

.error-container, .success-container {
  margin-top: 1rem;
  text-align: center;
}

.error {
  color: red;
}

.success {
  color: green;
}

@media (max-width: 768px) {
  .reset-password-card {
    padding: 1.5rem;
  }
}

@media (max-width: 480px) {
  html {
    font-size: 0.9rem;
  }
  .reset-password-card {
    padding: 1.25rem;
  }
  .form-group {
    margin-bottom: 1rem;
  }
}
</style>
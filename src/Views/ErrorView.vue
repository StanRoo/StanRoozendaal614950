<template>
    <div class="error-container text-center">
      <img class="error-image" :src="CryingCubone" alt="Crying Cubone"/>
      <h1 class="display-1 text-danger">{{ status }}</h1>
      <h2 class="mb-4">{{ title }}</h2>
      <p class="lead">{{ message }}</p>
      <router-link class="btn btn-primary mt-4" to="/home">Return to Home</router-link>
    </div>
</template>
  
<script setup>
  import { computed } from "vue";
  import CryingCubone from '@/assets/images/CryingCubone.png';
  
  const props = defineProps({
    status: {
      type: [Number, String],
      default: 500,
    },
    message: {
      type: String,
      default: "Something went wrong.",
    },
  });
  
  const title = computed(() => {
    const titles = {
      400: "Bad Request",
      401: "Unauthorized",
      403: "Forbidden",
      404: "Page Not Found",
      500: "Internal Server Error",
    };
    return titles[props.status] || "An Error Occurred";
  });
</script>
  
<style scoped>
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  text-align: center;
  min-height: 70vh;
}

.error-image {
  width: 50%;
  max-width: 15.625rem;
  height: auto;
  margin-bottom: 1.5rem;
}

h1 {
  font-size: 3rem;
  color: #dc3545;
  margin-bottom: 0.5rem;
}

h2 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

p.lead {
  font-size: 1.125rem;
  max-width: 37.5rem;
  margin: 0 auto;
}

.btn {
  margin-top: 1.5rem;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  border-radius: 0.5rem;
}

@media (max-width: 768px) {
  h1 {
    font-size: 2.5rem;
  }

  h2 {
    font-size: 1.3rem;
  }

  p.lead {
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  h1 {
    font-size: 2.2rem;
  }

  h2 {
    font-size: 1.1rem;
  }

  p.lead {
    font-size: 1rem;
  }

  .error-image {
    width: 70%;
  }

  .btn {
    width: 100%;
    font-size: 0.95rem;
    padding: 0.75rem 1.25rem;
  }
}
</style>
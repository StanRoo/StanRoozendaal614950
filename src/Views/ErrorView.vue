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
.error-image {
  height: 20vw;
  width: 20vw;
}

.error-container {
  margin-top: 20px;
  padding: 2rem;
}
</style>
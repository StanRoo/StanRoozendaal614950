<script setup>
import CuboCardBanner from '@/assets/icons/CuboCardBanner.png'
import { useRouter } from 'vue-router';

const router = useRouter();
const goToCreateCard = () => {
  router.push({ name: 'CreateCard' });
};
</script>

<template>
 <main>
    <img class="banner" :src="CuboCardBanner" alt="CuboCard Banner"/>
    <div class="container">
      <h1>Welcome to CuboCard</h1>
      <div class="HomeText">
        Discover the ultimate marketplace for Pokémon card enthusiasts! At CuboCard, we bring together collectors, traders, and fans to buy, sell, and exchange their favorite Pokémon cards. Whether you're hunting for rare treasures or looking to share your duplicates, our platform is designed to make trading fun, secure, and hassle-free.
        <br><br>
        Join the CuboCard community today and take your Pokémon collection to the next level!
        <br>
        Start by creating your own card for your favorite Pokémon!
        <br><br>
        <button @click="goToCreateCard" class="btn btn-primary create-card-btn">
          Create Card Now
        </button>
      </div>
    </div>
 </main>
</template>

<style scoped>
.banner {
  width: 100%;
  height: auto;
  object-fit: cover;
  margin-top: -40px;
}

main {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.container {
  width: 100%;
  max-width: 50rem;
  padding: 2rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

h1 {
  font-size: 2rem;
  margin: 1.5rem 0 1rem;
}

.HomeText {
  font-size: 1.125rem;
  line-height: 1.7;
  margin-top: 0.5rem;
  max-width: 100%;
}

.create-card-btn {
  font-size: 1.2rem;
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
}

@media (max-width: 768px) {
  h1 {
    font-size: 1.75rem;
  }

  .HomeText {
    font-size: 1rem;
    padding: 0 0.5rem;
  }
}

@media (max-width: 480px) {
  h1 {
    font-size: 1.5rem;
  }

  .HomeText {
    font-size: 1rem;
    padding: 0 0.5rem;
  }
}
</style>
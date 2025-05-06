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
      <h1>Welcome to CuboCard – Design and Trade Your Own Pokémon Cards!</h1>
      <div class="HomeText">
        CuboCard is the ultimate online marketplace for fans who dream of creating and trading their own Pokémon-inspired cards. Whether you're a collector, an artist, or just a passionate fan, CuboCard gives you the tools to bring your card ideas to life.
        <br><br>
        Using our intuitive card editor, you can upload your favourite Pokémon images, set the card’s rarity, and choose its type. Want to make a Legendary Shiny Psyduck or a custom Fire-type Eevee? Go for it! Each card you create is fully personalized, yet stays true to the Pokémon world.
        <br><br>
        Once your card is ready, you can list it on the marketplace and trade it with other fans across the platform. All trading is done using CuboCoins, our exclusive in-platform currency designed to keep things fair, fun, and safe.
        <br><br>
        Join a community of fellow creators and collectors, explore unique fan-made cards, and build a collection that’s truly your own.
        <br><br>
        <strong>CuboCard</strong> – where your imagination meets the world of Pokémon.
        <br><br>
        Join the CuboCard community today and take your Pokémon collection to the next level!
        <br>
        Start by creating your own card for your favourite Pokémon!
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
  max-width: 90rem;
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
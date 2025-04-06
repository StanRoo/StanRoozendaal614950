<template>
  <header>
    <img class="banner" :src="InventoryBanner" alt="Inventory Banner"/>
  </header>
  
  <div class="inventory-container">
    <section v-if="cards.length > 0" class="inventory-grid">
      <CardDisplay 
        v-for="card in cards" 
        :key="card.id" 
        :card="card" 
        @click="selectCard(card)" 
      />
    </section>

    <p v-else class="empty-message">You haven't created any cards yet.</p>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import CardDisplay from '@/Components/CardDisplay.vue';
import InventoryBanner from '@/assets/images/Inventory_Banner.png'

const router = useRouter();

const cards = ref([]);
const selectedCard = ref(null);

onMounted(async () => {
  await fetchCards();
});

const fetchCards = async () => {
  try {
    const token = localStorage.getItem('token');
    if (!token) return;

    const response = await axios.get(`/cards/user`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    cards.value = response.data.cards || [];
  } catch (error) {
    console.error('Error fetching inventory:', error.response?.data || error);
  }
};

const selectCard = (card) => {
  selectedCard.value = card;
  router.push({ name: 'CardDetail', params: { id: card.id } });
};
</script>

<style scoped>
.banner {
  width: 100%;
  margin-top: 10px;
}

.inventory-container {
  padding: 2vw;
  text-align: center;
}

.inventory-grid {
  display: grid;
  width: 80%;
  grid-template-columns: repeat(5, 1fr);
  gap: 1vw;
  justify-items: center;
}

.empty-message {
  font-size: 1.2vw;
  color: gray;
  margin-top: 2vw;
}
</style>
<template>
  <div class="marketplace-detail-container" v-if="card && listingInfo">
    <button @click="goBack" class="back-button">← Back</button>

    <div class="main-content">
      <div class="left-column">
        <CardDisplay :card="card" />
      </div>

      <div class="right-column">
        <div class="info-card">
          <h3>Seller Info</h3>
          <p><strong>Seller:</strong> {{ listingInfo.seller_username }}</p>
          <p><strong>Listed At:</strong> {{ formatDate(listingInfo.listed_at) }}</p>
          <p><strong>Minimum Price:</strong> {{ listingInfo.price }} <img src="@/assets/icons/coin.png" class="coin-icon" /></p>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="loading-message">Loading card details...</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import CardDisplay from '@/Components/CardDisplay.vue';

const route = useRoute();
const router = useRouter();
const card = ref(null);
const listingInfo = ref(null);

onMounted(async () => {
  const cardId = route.params.id;
  const token = localStorage.getItem('token');

  if (!token) return;

  try {
    const response = await axios.get(`/marketplace/card/${cardId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    console.log('API Response:', response.data);
    card.value = response.data.card;
    listingInfo.value = {
      price: response.data.price,
      seller_id: response.data.seller_id,
      seller_username: response.data.seller_username,
      listed_at: response.data.listed_at,
    };
  } catch (error) {
    console.error('Error fetching card details:', error.response?.data || error);
  }
});

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleString();
};

const goBack = () => {
  router.back();
};
</script>

<style scoped>
.marketplace-detail-container {
  padding: 3vw 2vw;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
  position: relative;
}

.back-button {
  position: absolute;
  top: 2.5vw;
  left: 2vw;
  padding: 0.8vw 1.5vw;
  border: none;
  border-radius: 10px;
  background-color: #007bff;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-button:hover {
  background-color: #0056b3;
}

.main-content {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  max-width: 1200px;
  width: 100%;
  gap: 2vw;
}

.left-column {
  width: 48%;
}

.right-column {
  width: 48%;
}

.info-card {
  background-color: #f8f9fa;
  padding: 2vw;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.info-card h3 {
  margin-bottom: 1vw;
  color: #333;
}

.info-card p {
  font-size: 1.2vw;
  margin-bottom: 0.8vw;
  color: #444;
}

.coin-icon {
  width: 1.5vw;
  vertical-align: middle;
  margin-left: 0.5vw;
}

.loading-message {
  font-size: 1.5vw;
  color: gray;
}
</style>
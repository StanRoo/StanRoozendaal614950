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
          <p><strong>Price:</strong> {{ listingInfo.price }} <img src="@/assets/icons/coin.png" class="coin-icon" /></p>
        </div>

        <button @click="buyNow" class="buy-now-button">Buy Now</button>
        <div class="feedback">
          <p v-if="successMessage" class="success">{{ successMessage }}</p>
          <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
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
import { useUserStore } from '@/Store/UserStore';
import CardDisplay from '@/Components/CardDisplay.vue';

defineEmits(['profileUpdated'])

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const card = ref(null);
const listingInfo = ref(null);
const successMessage = ref('');
const errorMessage = ref('');

onMounted(async () => {
  const cardId = route.params.id;
  const token = localStorage.getItem('token');

  if (!token) return;

  try {
    const response = await axios.get(`/marketplace/card/${cardId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });
    console.log(response.data);
    card.value = response.data.card;
    listingInfo.value = {
      price: response.data.price,
      seller_id: response.data.seller_id,
      seller_username: response.data.seller_username,
      listed_at: response.data.listed_at,
      listing_id: response.data.listing_id,
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

const buyNow = async () => {
  const token = localStorage.getItem('token');
  if (!token) return;

  successMessage.value = '';
  errorMessage.value = '';

  try {
    await axios.post('/transaction/buyNow', 
      { listing_id: listingInfo.value.listing_id }, 
      { headers: { Authorization: `Bearer ${token}` } }
    );

    const currentBalance = userStore.user?.balance ?? 0;
    const newBalance = currentBalance - listingInfo.value.price;
    userStore.updateBalance(newBalance);

    successMessage.value = 'Purchase successful!';
    setTimeout(() => (successMessage.value = ''), 4000);
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Purchase failed.';
    setTimeout(() => (errorMessage.value = ''), 4000);
  }
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
  margin-bottom: 2vw;
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

.buy-now-button {
  padding: 1vw 2vw;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 1.2vw;
  cursor: pointer;
  margin-top: 2vw;
}

.buy-now-button:hover {
  background-color: #218838;
}

.loading-message {
  font-size: 1.5vw;
  color: gray;
}

.success {
  color: green;
}
.error {
  color: red;
}
</style>
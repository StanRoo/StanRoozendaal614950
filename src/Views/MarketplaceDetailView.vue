<template>
  <div class="marketplace-detail-container" v-if="card && listingInfo">
    <button @click="goBack" class="back-button">← Back</button>

    <div class="main-content">
      <div class="left-column">
        <CardDisplay :card="card" />
        <button @click="buyNow" class="buy-now-button">Buy Now: <img src="@/assets/icons/coin.png" class="coin-icon" /> {{ listingInfo.price }}</button>
        <div class="feedback">
          <p v-if="successMessage" class="success">{{ successMessage }}</p>
          <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
        </div>
      </div>

      <div class="right-column">
        <div class="info-card">
          <h3>Seller Info</h3>
          <p><strong>Seller:</strong> {{ listingInfo.seller_username }}</p>
          <p><strong>Listed At:</strong> {{ formatDate(listingInfo.listed_at) }}</p>
          <p><strong>Price:</strong><img src="@/assets/icons/coin.png" class="coin-icon" /> {{ listingInfo.price }} </p>
        </div>

        <div class="info-card bid-section" v-if="listingInfo && !isOwnListing">
          <h3>Place a Bid</h3>

          <p>
            <strong>Current Highest Bid: </strong>
            <span v-if="listingInfo.highest_bid !== null">{{ formatPrice(listingInfo.highest_bid.bid_amount) }}</span>
            <span v-else>No bids yet</span>
            <img src="@/assets/icons/coin.png" class="coin-icon" />
          </p>
          <p>
            <strong>Minimum Bid:</strong>
            {{ formatPrice(listingInfo.min_bid_price) }}
            <img src="@/assets/icons/coin.png" class="coin-icon" />
          </p>

          <form @submit.prevent="placeBid">
            <input
              v-model="bidAmount"
              type="number"
              :min="listingInfo.min_bid_price"
              step="0.01"
              required
              placeholder="Enter bid amount"
            />
            <button type="submit" class="bid-button">Submit Bid</button>
          </form>

          <p v-if="bidMessage" class="success">{{ bidMessage }}</p>
          <p v-if="bidError" class="error">{{ bidError }}</p>
        </div>

        <div class="info-card bid-section" v-if="listingInfo && isOwnListing">
          <h3>Place a Bid</h3>
          <p>You cannot bid on your own listing.</p>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="loading-message">Loading card details...</div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
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
const bidAmount = ref('');
const bidMessage = ref('');
const bidError = ref('');

onMounted(() => {
  fetchCardDetails();
});

const fetchCardDetails = async () => {
  const token = localStorage.getItem('token');
  if (!token) {
    router.push("/");
  }
  const cardId = route.params.id;

  try {
    const response = await axios.get(`/marketplace/card/${cardId}`, {
      headers: { Authorization: `Bearer ${token}` },
    });

    card.value = response.data.card;
    listingInfo.value = {
      price: response.data.price,
      seller_id: response.data.seller_id,
      seller_username: response.data.seller_username,
      listed_at: response.data.listed_at,
      listing_id: response.data.listing_id,
      min_bid_price: response.data.min_bid_price,
      highest_bid: response.data.highest_bid,
    };
  } catch (error) {
    console.error('Error fetching card details:', error.response?.data || error);
  }
};

const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleString();
};

const formatPrice = (value) => {
  if (!value && value !== 0) return '0';
  return Number(value).toLocaleString();
};

const goBack = () => {
  router.back();
};

const buyNow = async () => {
  const token = localStorage.getItem('token');
  if (!token) {
    router.push("/");
  }

  successMessage.value = '';
  errorMessage.value = '';

  try {
    await axios.post('/marketplace/buyNow', 
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

const placeBid = async () => {
  const token = localStorage.getItem('token');
  if (!token) {
    router.push("/");
  }

  bidMessage.value = '';
  bidError.value = '';

  try {
    await axios.post('/bid/place',
      {
        listing_id: listingInfo.value.listing_id,
        amount: parseFloat(bidAmount.value),
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    bidMessage.value = 'Bid placed successfully!';
    bidAmount.value = '';
    await fetchCardDetails();
    setTimeout(() => (bidMessage.value = ''), 3000);
  } catch (err) {
    bidError.value = err.response?.data?.message || 'Failed to place bid.';
    setTimeout(() => (bidError.value = ''), 3000);
  }
};

const isOwnListing = computed(() => {
  return listingInfo.value && userStore.user?.id === listingInfo.value.seller_id;
});
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
  border-radius: 0.625rem;
  background-color: #007bff;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
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

.left-column,
.right-column {
  width: 48%;
}

.left-column {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.buy-now-button {
  padding: 1vw 2vw;
  width: 18vw;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 0.625rem;
  font-size: 1.2vw;
  cursor: pointer;
  margin-top: 5vw;
  margin-bottom: 2rem;
}

.buy-now-button:hover {
  background-color: #218838;
}

.right-column {
  width: 48%;
}

.info-card {
  background-color: #f8f9fa;
  padding: 2vw;
  border-radius: 0.625rem;
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
  border-radius: 1rem;
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

.bid-section {
  background-color: #fefefe;
  padding: 2vw;
  border-radius: 0.625rem;
  box-shadow: 0 0 10px rgba(0, 128, 0, 0.1);
  margin-top: 2vw;
}

.bid-section form {
  margin-top: 1vw;
  display: flex;
  flex-direction: column;
}

.bid-section input[type="number"] {
  padding: 0.6vw;
  border-radius: 0.3125rem;
  border: 1px solid #ccc;
  font-size: 1vw;
  margin-bottom: 1vw;
}

.bid-button {
  background-color: #ffc107;
  color: #000;
  padding: 0.8vw 1.5vw;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 1.1vw;
  transition: all 0.3s ease;
}

.bid-button:hover {
  background-color: #e0a800;
}

@media (max-width: 1024px) {
  .main-content {
    gap: 0vw;
    padding: 2vw 1vw;
  }

  .buy-now-button {
    width: 67%;
    font-size: 1.2rem;
    margin-top: 2rem;
    margin-bottom: 2rem;
  }
}

@media (max-width: 768px) {
  .marketplace-detail-container {
    padding-top: 5rem;
  }

  .main-content {
    flex-direction: column;
    align-items: center;
  }

  .left-column,
  .right-column {
    width: 100%;
  }

  .left-column {
    align-items: center;
    justify-content: center;
  }

  .buy-now-button {
    width: 90%;
    font-size: 1.2rem;
    margin-top: 2rem;
    margin-bottom: 2rem;
  }

  .info-card p,
  .info-card h3 {
    font-size: 1.2rem;
  }

  .bid-section input[type="number"] {
    font-size: 1rem;
    padding: 0.8rem;
  }

  .bid-button {
    font-size: 1.1rem;
    padding: 0.8rem 1.2rem;
  }

  .coin-icon {
    width: 1.2rem;
  }

  .loading-message {
    font-size: 1.2rem;
  }
}

@media (max-width: 480px) {
  .back-button {
    top: 1.2rem;
    left: 1rem;
    padding: 0.6rem 1.2rem;
    font-size: 1rem;
  }

  .buy-now-button {
    font-size: 1rem;
  }

  .info-card p,
  .info-card h3 {
    font-size: 1rem;
  }

  .bid-button {
    font-size: 1rem;
  }
}
</style>
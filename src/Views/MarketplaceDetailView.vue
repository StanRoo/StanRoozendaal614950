<template>
  <div class="marketplace-detail-container" v-if="card && listingInfo">
    <button @click="goBack" class="back-button">← Back</button>

    <div class="main-content">

      <!--Left Column-->
      <div class="left-column">

        <!--CardDisplay Component-->
        <CardDisplay :card="card" />

        <button @click="buyNow" class="buy-now-button" :disabled="isBuyingNow || isOwner">
          <template v-if="isBuyingNow">
            Buying...
          </template>
          <template v-else>
            Buy Now:
            <img src="@/assets/icons/coin.png" class="coin-icon" />
            {{ listingInfo.price }}
          </template>
        </button>

        <!--User Feedback-->
        <p v-if="successMessageBuyNow" class="succes">{{ successMessageBuyNow }}</p>
        <p v-if="errorMessageBuyNow" class="error">{{ errorMessageBuyNow }}</p>
      </div>

      <!--Right Column-->
      <div class="right-column">

        <!--Info Card-->
        <div class="info-card">
          <h3>Seller Info</h3>
          <p><strong>Seller:</strong> {{ listingInfo.seller_username }}</p>
          <p><strong>Listed At:</strong> {{ formatDate(listingInfo.listed_at) }}</p>
          <p><strong>Expires At:</strong> {{ formatDate(listingInfo.expires_at) }}</p>
          <p><strong>Price:</strong><img src="@/assets/icons/coin.png" class="coin-icon" /> {{ listingInfo.price }} </p>
        </div>

        <!--Bid Section-->
        <div class="info-card bid-section" v-if="listingInfo && !isOwnListing">
          <h3>Place a Bid</h3>

          <p>
            <strong>Current Highest Bid: </strong>
            <span v-if="listingInfo.highest_bid !== null"><img src="@/assets/icons/coin.png" class="coin-icon" /> {{ formatPrice(listingInfo.highest_bid.bid_amount) }}</span>
            <span v-else>No bids yet</span>
          </p>
          <p>
            <strong>Current Highest Bidder: </strong>
            <span v-if="listingInfo.highest_bid_username !== null">{{ formatPrice(listingInfo.highest_bid_username) }}</span>
            <span v-else>No bids yet</span>
          </p>
          <p>
            <strong>Minimum Bid:</strong>
            <img src="@/assets/icons/coin.png" class="coin-icon" />
            {{ formatPrice(listingInfo.min_bid_price) }}
          </p>

          <!--Form-->
          <form @submit.prevent="placeBid">
            <input
              v-model="bidAmount"
              type="number"
              :min="listingInfo.min_bid_price"
              step="0.01"
              required
              placeholder="Enter bid amount"
            />
            <button type="submit" class="bid-button" :disabled="isSubmitting">
              {{ isSubmitting ? "Submitting Bid..." : "Submit Bid" }}
            </button>
          </form>

          <!--User Feedback-->
          <p v-if="successMessageBid" class="succes">{{ successMessageBid }}</p>
          <p v-if="errorMessageBid" class="error">{{ errorMessageBid }}</p>
        </div>

        <!--Bid Section (if owner)-->
        <div class="info-card bid-section" v-if="listingInfo && isOwnListing">
          <h3>Place a Bid</h3>
          <p>You cannot bid on your own listing.</p>
        </div>
      </div>
    </div>
  </div>

  <!--Loading State-->
  <div v-else class="loading-message">Loading card details...</div>

  <!--User Feedback-->
  <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
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
const bidAmount = ref('');

const isOwner = ref(false);
const isBuyingNow = ref(false);
const isSubmitting = ref(false);

const successMessageBuyNow = ref('');
const errorMessageBuyNow = ref('');
const successMessageBid = ref('');
const errorMessageBid = ref('');
const errorMessage = ref('');

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
      expires_at: response.data.expires_at,
      listing_id: response.data.listing_id,
      min_bid_price: response.data.min_bid_price,
      highest_bid: response.data.highest_bid,
      highest_bid_username: response.data.highest_bid_username,
    };
    const userId = userStore.user?.id;
    if (userId && response.data.seller_id === userId) {
      isOwner.value = true;
    }
  } catch (error) {
    this.errorMessage.value = error.response?.data?.message || error.message || "Something went wrong.";
    setTimeout(() => (this.errorMessage.value = ''), 3000);
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
  if (isBuyingNow.value) return;
  isBuyingNow.value = true;
  const token = localStorage.getItem('token');
  if (!token) {
    router.push("/");
  }

  try {
    await axios.post('/marketplace/buyNow', 
      { listing_id: listingInfo.value.listing_id }, 
      { headers: { Authorization: `Bearer ${token}` } }
    );

    const currentBalance = userStore.user?.balance ?? 0;
    const newBalance = currentBalance - listingInfo.value.price;
    userStore.updateBalance(newBalance);

    successMessageBuyNow.value = 'Purchase successful!';
    setTimeout(() => (successMessageBuyNow.value = ''), 3000);
  } catch (error) {
    errorMessageBuyNow.value = error.response?.data?.message || error.message || "Something went wrong.";
    setTimeout(() => (errorMessageBuyNow.value = ''), 3000);
  } finally {
    isBuyingNow.value = false;
  }
};

const placeBid = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  const token = localStorage.getItem('token');
  if (!token) {
    router.push("/");
  }

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

    successMessageBid.value = 'Bid placed successfully!';
    setTimeout(() => (successMessageBid.value = ''), 3000);
    bidAmount.value = '';
    await fetchCardDetails(); 
  } catch (error) {
    errorMessageBid.value = error.response?.data?.message || error.message || "Something went wrong.";
    setTimeout(() => (errorMessageBid.value = ''), 3000);
  } finally {
    isSubmitting.value = false;
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

.buy-now-button:disabled {
  background-color: gray;
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
  background-color: #007bff;
  color: #fff;
  padding: 0.8vw 1.5vw;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 1.1vw;
  transition: all 0.3s ease;
}

.bid-button:hover {
  background-color: #0056b3;
}

.succes {
  text-align: center;
  color: green !important;
  margin-top: 5px;
}

.error {
  text-align: center;
  color: red !important;
  margin-top: 5px;
}

.loading {
  font-size: 1.5rem;
  color: gray;
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
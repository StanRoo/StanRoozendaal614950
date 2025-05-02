<template>
  <div class="card-detail-container" v-if="card">
    <button @click="goBack" class="back-button">← Back</button>

    <div class="main-content">
      <div class="left-column">
        <CardDisplay :card="card" />
      </div>

      <div class="right-column">
        <div class="marketplace-section">
          <h3>List Card on Marketplace</h3>
          <label for="price">Buy Now Price</label>
          <div class="input-wrapper">
            <input v-model="price" type="number" id="price" min="0" placeholder="Enter buy now price" />
          </div>

          <label for="minBidPrice">Minimum Bid Price</label>
          <div class="input-wrapper">
            <input v-model="minBidPrice" type="number" id="minBidPrice" min="0" placeholder="Enter minimum bid price" />
          </div>

          <label for="expiryDate">Expiry Date</label>
          <div class="input-wrapper">
            <input v-model="expiryDate" type="datetime-local" id="expiryDate" />
          </div>

          <div class="marketplace-button-wrapper">
            <button @click="listOnMarketplace" :disabled="!price || !minBidPrice || listingComplete">
              Add to Marketplace
            </button>
          </div>

          <p v-if="listMessage" class="success">{{ listMessage }}</p>
          <p v-if="listError" class="error">{{ listError }}</p>
        </div>

        <button @click="confirmDelete" class="danger delete-button">Delete Card</button>

        <div class="feedback">
          <p v-if="deleteMessage" class="success">{{ deleteMessage }}</p>
          <p v-if="deleteError" class="error">{{ deleteError }}</p>
        </div>

        <div v-if="showConfirmDelete" class="popup-overlay">
          <div class="popup">
            <p>Are you sure you want to delete this card?</p>
            <div class="popup-actions">
              <button @click="deleteCard" class="danger">Yes, delete</button>
              <button @click="showConfirmDelete = false">Cancel</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="loading">Loading card details...</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import CardDisplay from '@/Components/CardDisplay.vue';

const route = useRoute();
const router = useRouter();
const card = ref(null);
const price = ref(null);
const minBidPrice = ref(null);
const expiryDate = ref('');

const listMessage = ref('');
const listError = ref('');
const listingComplete = ref(false);

const deleteMessage = ref('');
const deleteError = ref('');
const showConfirmDelete = ref(false);

onMounted(async () => {
  const token = localStorage.getItem('token');
  const id = route.params.id;

  try {
    const response = await axios.get(`/cards/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    card.value = response.data.card;
  } catch (error) {
    console.error(error);
  }
});

const listOnMarketplace = async () => {
  listMessage.value = '';
  listError.value = '';

  if (!price.value || !minBidPrice.value || !expiryDate.value) {
    listError.value = 'Buy Now Price, Minimum Bid Price, and Expiry Date are required.';
    setTimeout(() => {
      listError.value = '';
    }, 4000);
    return;
  }

  try {
    const token = localStorage.getItem('token');
    await axios.post('/marketplace/list', {
      card_id: card.value.id,
      price: price.value,
      min_bid_price: minBidPrice.value,
      expires_at: expiryDate.value
    }, {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    listMessage.value = 'Card listed on the marketplace!';
    listingComplete.value = true;
    setTimeout(() => {
      listMessage.value = '';
    }, 4000);
  } catch (error) {
    listError.value = error.response?.data?.message || 'Failed to list card.';
    setTimeout(() => {
      listError.value = '';
    }, 4000);
  }
};

const confirmDelete = () => {
  deleteMessage.value = '';
  deleteError.value = '';
  showConfirmDelete.value = true;
};

const deleteCard = async () => {
  try {
    const token = localStorage.getItem('token');
    await axios.delete(`/cards/${card.value.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    deleteMessage.value = 'Card deleted successfully!';
    router.push('/inventory');
  } catch (error) {
    deleteError.value = 'Failed to delete card.';
  } finally {
    showConfirmDelete.value = false;
  }
};

const goBack = () => {
  router.back();
};
</script>

<style scoped>
.card-detail-container {
  padding: 6vh 2vw 2vw 2vw;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 90vh;
  position: relative;
  flex-direction: column;
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
  align-self: flex-start;
  margin-bottom: 1.5rem;
  z-index: 10;
}

.back-button:hover {
  background-color: #0056b3;
}

.main-content {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  max-width: 120vh;
  width: 100%;
  padding-top: 1rem;
}

.left-column,
.right-column {
  width: 48%;
}

.right-column {
  display: flex;
  flex-direction: column;
  gap: 1.5vw;
}

.marketplace-section {
  background: #f8f9fa;
  padding: 2vw;
  border-radius: 0.625rem;
}

.marketplace-section label {
  font-size: 1.2rem;
  padding-right: 1vw;
  display: inline-block;
  margin-bottom: 0.4rem;
  text-align: left;
  width: 100%;
}

.input-wrapper {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  width: 60%;
}

.marketplace-section input {
  padding: 0.5rem;
  font-size: 1.1rem;
  border-radius: 0.3125rem;
  border: 1px solid #ccc;
  margin-bottom: 1.2rem;
  width: 100%;
}

.marketplace-button-wrapper {
  display: flex;
  justify-content: center;
  margin-top: 1.5rem;
}

.marketplace-section button {
  padding: 0.8rem 1.5rem;
  background-color: #28a745;
  color: white;
  font-weight: bold;
  border-radius: 0.625rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.marketplace-section button:disabled {
  background-color: #c6e0c7;
  cursor: not-allowed;
}

.marketplace-section button:hover {
  background-color: #218838;
}

.delete-button {
  padding: 0.8rem 1.5rem;
  background-color: #dc3545;
  color: white;
  font-weight: bold;
  border-radius: 0.625rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.delete-button:hover {
  background-color: #a71d2a;
}

.feedback p {
  margin-top: 1rem;
  font-weight: bold;
}

.success {
  color: green;
}
.error {
  color: red;
}

.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.popup {
  background: #3366af;
  padding: 2.5vw 2vw;
  border-radius: 0.75rem;
  box-shadow: 0px 1vh 2.5vh rgba(0, 0, 0, 0.2);
  width: 21rem;
  text-align: center;
  animation: fadeIn 0.3s ease;
}

.popup p {
  font-size: 1.1rem;
  color: #ffffff;
  margin-bottom: 1.8rem;
}

.popup-actions {
  display: flex;
  justify-content: center;
  gap: 1vw;
}

.popup-actions button {
  padding: 0.6rem 1.4rem;
  font-size: 1rem;
  border-radius: 0.5rem;
  font-weight: bold;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}

.popup-actions .danger {
  background-color: #dc3545;
  color: white;
}

.popup-actions .danger:hover {
  background-color: #b02a37;
}

.popup-actions button:not(.danger) {
  background-color: #e0e0e0;
  color: #333;
}

.popup-actions button:not(.danger):hover {
  background-color: #cfcfcf;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.96);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.loading {
  font-size: 1.5rem;
  color: gray;
}

.banner {
  width: 100%;
  height: 11vh;
  margin-top: 0.6rem;
}

@media (max-width: 768px) {
  .back-button {
    top: 3vh;
    padding: 0.5rem;
  }

  .main-content {
    flex-direction: column;
    align-items: center;
    padding-top: 2rem;
  }

  .left-column,
  .right-column {
    width: 100%;
  }

  .left-column {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 2rem;
  }

  .input-wrapper {
    width: 100%;
  }

  .banner {
    object-fit: cover;
  }
}

@media (max-width: 480px) {
  .back-button {
    top: 3vh;
  }

  .banner {
    object-fit: cover;
  }
}
</style>
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
          <label for="price">Minimum Price</label>
          <input v-model="price" type="number" id="price" min="0" placeholder="Enter minimum price" />
          <button @click="listOnMarketplace" :disabled="!price">Add to Marketplace</button>
        </div>

        <button @click="deleteCard" class="danger delete-button">Delete Card</button>
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

onMounted(async () => {
  const token = localStorage.getItem('token');
  const id = route.params.id;

  try {
    const response = await axios.get(`/cards/${id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    card.value = response.data.card;
  } catch (error) {

  }
});

const listOnMarketplace = async () => {
  try {
    const token = localStorage.getItem('token');
    await axios.post(`/marketplace/list`, {
      card_id: card.value.id,
      price: price.value
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });

    alert('Card listed on the marketplace!');
  } catch (error) {
    console.error(error);
    alert('Failed to list card.');
  }
};

const deleteCard = async () => {
  if (!confirm("Are you sure you want to delete this card?")) return;

  try {
    const token = localStorage.getItem('token');
    await axios.delete(`/cards/${card.value.id}`, {
      headers: { Authorization: `Bearer ${token}` }
    });

    alert('Card deleted!');
    router.push('/inventory');
  } catch (error) {
    console.error(error);
    alert('Failed to delete card.');
  }
};

const goBack = () => {
  router.back();
};
</script>

<style scoped>
.card-detail-container {
  padding: 3vw 2vw;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 80vh;
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
}

.left-column {
  width: 48%;
}

.right-column {
  width: 48%;
  display: flex;
  flex-direction: column;
  gap: 1.5vw;
}

.marketplace-section {
  background: #f8f9fa;
  padding: 2vw;
  border-radius: 10px;
}

.marketplace-section label {
  font-size: 1.2vw;
  padding-right: 1vw;
}

.marketplace-section input {
  padding: 0.5vw;
  font-size: 1.1vw;
  border-radius: 5px;
  border: 1px solid #ccc;
  margin-top: 1.2vw;
}

.marketplace-section button {
  padding: 0.8vw 1.5vw;
  margin-top: 1.5vw;
  background-color: #28a745;
  color: white;
  font-weight: bold;
  border-radius: 10px;
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
  padding: 0.8vw 1.5vw;
  background-color: #dc3545;
  color: white;
  font-weight: bold;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.delete-button:hover {
  background-color: #a71d2a;
}

.loading {
  font-size: 1.5vw;
  color: gray;
}
</style>
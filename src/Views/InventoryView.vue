<template>
  <header>
    <img class="banner" :src="InventoryBanner" alt="Inventory Banner"/>
  </header>
  
  <div class="card shadow-sm mb-4 p-3 filter-card">
    <div class="row g-3 align-items-center">
      <div class="col-md-4">
        <input 
          type="text" 
          v-model="searchQuery" 
          class="form-control" 
          placeholder="Search by card name..."
        />
      </div>

      <div class="col-md-2">
        <select class="form-select" v-model="selectedRarity">
          <option value="">All Rarities</option>
          <option value="Common">Common</option>
          <option value="Rare">Rare</option>
          <option value="Epic">Epic</option>
          <option value="Legendary">Legendary</option>
        </select>
      </div>

      <div class="col-md-2">
        <select class="form-select" v-model="selectedType">
          <option value="">All Types</option>
          <option value="Normal">Normal</option>
          <option value="Fire">Fire</option>
          <option value="Water">Water</option>
          <option value="Electric">Electric</option>
          <option value="Grass">Grass</option>
          <option value="Ice">Ice</option>
          <option value="Fighting">Fighting</option>
          <option value="Poison">Poison</option>
          <option value="Ground">Ground</option>
          <option value="Flying">Flying</option>
          <option value="Psychic">Psychic</option>
          <option value="Bug">Bug</option>
          <option value="Rock">Rock</option>
          <option value="Ghost">Ghost</option>
          <option value="Dragon">Dragon</option>
          <option value="Dark">Dark</option>
          <option value="Steel">Steel</option>
          <option value="Fairy">Fairy</option>
          <option value="Stellar">Stellar</option>
        </select>
      </div>

      <div class="col-md-4">
        <select class="form-select" v-model="sortOption">
          <option value="name_asc">Sort by Name (A-Z)</option>
          <option value="created_desc">Newest First</option>
          <option value="created_asc">Oldest First</option>
        </select>
      </div>
    </div>
  </div>

  <div class="inventory-container">
    <section v-if="cards.length > 0" class="inventory-grid">
      <CardDisplay 
        v-for="card in filteredCards" 
        :key="card.id" 
        :card="card"
        class="inventory-card"
        @click="selectCard(card)" 
      />
    </section>

    <p v-else class="empty-message">You haven't created any cards yet.</p>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import CardDisplay from '@/Components/CardDisplay.vue';
import InventoryBanner from '@/assets/images/Inventory_Banner.png'

defineEmits(['profileUpdated'])

const router = useRouter();

const cards = ref([]);
const selectedCard = ref(null);
const searchQuery = ref('');
const selectedRarity = ref('');
const selectedType = ref('');
const sortOption = ref('name_asc');

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

const filteredCards = computed(() => {
  let filtered = [...cards.value];

  filtered = filtered.filter(card => card.is_listed === 0);

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(card => card.name.toLowerCase().includes(query));
  }

  if (selectedRarity.value) {
    filtered = filtered.filter(card => card.rarity === selectedRarity.value);
  }

  if (selectedType.value) {
    filtered = filtered.filter(card => card.type === selectedType.value);
  }

  if (sortOption.value === 'name_asc') {
    filtered.sort((a, b) => a.name.localeCompare(b.name));
  } else if (sortOption.value === 'created_desc') {
    filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  } else if (sortOption.value === 'created_asc') {
    filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
  }

  return filtered;
});
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

.card .form-control,
.card .form-select {
  font-size: 0.95vw;
}

.inventory-card {
  background-color: #f8f9fa;
  border-radius: 10px;
  padding: 1.5vw;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  width: 21vw;
  cursor: pointer;
  transition: transform 0.2s ease-in-out;
}

.inventory-card:hover {
  transform: scale(1.05);
}

.empty-message {
  font-size: 1.2vw;
  color: gray;
  margin-top: 2vw;
}

.filters {
  margin: 1.5vw auto;
  display: flex;
  justify-content: center;
  gap: 1vw;
  flex-wrap: wrap;
}

.filters select {
  padding: 0.6vw 1vw;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 1vw;
}

.filter-card {
  background-color: #3366af;
  color: white;
  width: 100%;
  margin: 0 auto;
  border-radius: 0;
}

.filter-card .form-control,
.filter-card .form-select {
  background-color: white;
  color: black;
  border: 1px solid #ccc;
}
</style>
<template>
  <header>
    <img class="banner" :src="InventoryBanner" alt="Inventory Banner" />
  </header>

  <!--Filters-->
  <div class="card shadow-sm mb-4 p-3 filter-card">
    <div class="row g-3 align-items-center">
      <div class="col-md-4 col-12">
        <input 
          type="text" 
          v-model="searchQuery" 
          class="form-control" 
          placeholder="Search by card name..."
        />
      </div>

      <div class="col-md-2 col-6">
        <select class="form-select" v-model="selectedRarity">
          <option value="">All Rarities</option>
          <option value="Common">Common</option>
          <option value="Rare">Rare</option>
          <option value="Epic">Epic</option>
          <option value="Legendary">Legendary</option>
        </select>
      </div>

      <div class="col-md-2 col-6">
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

      <div class="col-md-4 col-12">
        <select class="form-select" v-model="sortOption">
          <option value="name_asc">Sort by Name (A-Z)</option>
          <option value="created_desc">Newest First</option>
          <option value="created_asc">Oldest First</option>
        </select>
      </div>
    </div>
  </div>

  <!--Inventory Grid-->
  <div class="inventory-container" v-if="cards">
    <section v-if="cards.length > 0" class="inventory-grid">

      <!--CardDisplay Component-->
      <CardDisplay 
        v-for="card in cards" 
        :key="card.id" 
        :card="card"
        class="inventory-card"
        @click="selectCard(card)" 
      />
    </section>
    <p v-else class="empty-message">You haven't created any cards yet.</p>
    
    <!--User Feedback-->
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

    <!--Loading State-->
    <p v-if="isLoading" class="text-muted">Loading more cards...</p>
  </div>
  <div v-else class="loading">Loading inventory details...</div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import CardDisplay from '@/Components/CardDisplay.vue';
import InventoryBanner from '@/assets/images/Inventory_Banner.png'

defineEmits(['profileUpdated'])

const router = useRouter();

const cards = ref([]);
const offset = ref(0)
const limit = 20
const hasMore = ref(true)
const isLoading = ref(false)
const selectedCard = ref(null);
const searchQuery = ref('');
const selectedRarity = ref('');
const selectedType = ref('');
const errorMessage = ref('');
const sortOption = ref('name_asc');

onMounted(() => {
  fetchCards()
  window.addEventListener('scroll', handleScroll)
})

const fetchCards = async () => {
  if (isLoading.value || !hasMore.value) return
  isLoading.value = true

  try {
    const token = localStorage.getItem('token')
    if (!token) {
      router.push("/");
    }
    const params = {
      search: searchQuery.value,
      rarity: selectedRarity.value,
      type: selectedType.value,
      sort: sortOption.value,
      offset: offset.value,
      limit
    }

    const response = await axios.get(`/user/cards`, {
      headers: { Authorization: `Bearer ${token}` },
      params
    })

    const newCards = response.data.cards || []
    cards.value.push(...newCards)
    offset.value += limit
    if (newCards.length < limit) {
      hasMore.value = false
    }
  } catch (error) {
    this.errorMessage.value = error.response?.data?.message || error.message || "Something went wrong.";
    setTimeout(() => (this.errorMessage.value = ''), 3000);
  } finally {
    isLoading.value = false
  }
}

watch([searchQuery, selectedRarity, selectedType, sortOption], async () => {
  cards.value = []
  offset.value = 0
  hasMore.value = true
  await fetchCards()
})


const handleScroll = () => {
  const scrollBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 200
  if (scrollBottom) {
    fetchCards()
  }
}

const selectCard = (card) => {
  selectedCard.value = card;
  router.push({ name: 'CardDetail', params: { id: card.id } });
};
</script>

<style scoped>
.banner {
  width: 100%;
  height: 11vh;
  margin-top: 0.6rem;
}

.inventory-container {
  padding: 2vw;
  text-align: center;
}

.inventory-grid {
  display: grid;
  width: 100%;
  margin: 0 auto;
  grid-template-columns: repeat(auto-fit, minmax(19rem, 1fr));
  gap: 1.2vw;
  justify-items: center;
}

.inventory-card {
  background-color: #f8f9fa;
  border-radius: 0.8rem;
  padding: 1.5vw;
  box-shadow: 0 0 1rem rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: transform 0.2s ease-in-out;
}

.inventory-card:hover {
  transform: scale(1.05);
}

.empty-message {
  font-size: 1.2rem;
  color: gray;
  margin-top: 2rem;
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
  font-size: 0.95rem;
}

.error {
  text-align: center;
  color: red;
  margin-top: 5px;
}

.loading {
  font-size: 1.5rem;
  color: gray;
}

@media (max-width: 1024px) {
  .banner{
    object-fit: cover;
  }
}

@media (max-width: 992px) {
  .inventory-card {
    width: 28vw;
    padding: 1.8vw;
  }

  .empty-message {
    font-size: 1.5rem;
  }
}

@media (max-width: 768px) {
  .inventory-card {
    width: 38vw;
    padding: 2.2vw;
  }
}

@media (max-width: 480px) {
  .banner {
    height: 10vh;
  }

  .inventory-card {
    width: 75vw;
    padding: 4vw;
  }

  .empty-message {
    font-size: 1.1rem;
  }
}
</style>
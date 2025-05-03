<template>
  <header>
    <img class="banner" :src="MarketplaceBanner" alt="Marketplace Banner" />
  </header>

  <div class="card shadow-sm mb-4 p-3 filter-card">
    <div class="row g-3 align-items-center">
      <div class="col-md-3">
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

      <div class="col-md-3">
        <div class="input-group">
          <input 
            type="number" 
            v-model.number="minPrice" 
            class="form-control" 
            placeholder="Min Price" 
          />
          <input 
            type="number" 
            v-model.number="maxPrice" 
            class="form-control" 
            placeholder="Max Price" 
          />
        </div>
      </div>

      <div class="col-md-2">
        <select class="form-select" v-model="sortOption">
          <option value="name_asc">Sort by Name (A-Z)</option>
          <option value="price_asc">Lowest Price</option>
          <option value="price_desc">Highest Price</option>
          <option value="created_desc">Newest First</option>
          <option value="created_asc">Oldest First</option>
        </select>
      </div>
    </div>
  </div>

  <div class="marketplace-header-actions">
    <button @click="goToMyListings" class="btn btn-primary my-listings-btn">
      My Listings
    </button>
  </div>

  <div class="marketplace-container">
    <section v-if="cards.length > 0" class="marketplace-grid">
      <div
        v-for="card in filteredCards"
        :key="card.id"
        class="marketplace-card"
        @click="selectCard(card)"
      >
        <CardDisplay :card="card" />
        <div class="price-container">
          <span class="price">Price: {{ card.price }}</span>
          <img src="@/assets/icons/coin.png" alt="Cubocoins" class="coin-icon" />
        </div>
      </div>
    </section>

    <p v-else class="empty-message">No cards listed on the marketplace yet.</p>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import CardDisplay from '@/Components/CardDisplay.vue';
import MarketplaceBanner from '@/assets/images/Marketplace_Banner.png';

defineEmits(['profileUpdated']);
const router = useRouter();

const cards = ref([]);
const searchQuery = ref('');
const selectedRarity = ref('');
const selectedType = ref('');
const minPrice = ref(null);
const maxPrice = ref(null);
const sortOption = ref('name_asc');

onMounted(async () => {
  await fetchMarketplaceCards();
});

const fetchMarketplaceCards = async () => {
  try {
    const token = localStorage.getItem('token');
    if (!token) return;

    const listingsResponse = await axios.get('/marketplace/list', {
      headers: { Authorization: `Bearer ${token}` },
    });

    const listings = listingsResponse.data.listings || [];

    const cardsWithDetails = await Promise.all(
      listings.map(async (listing) => {
        const cardResponse = await axios.get(`/cards/${listing.card_id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        return {
          ...cardResponse.data.card,
          price: listing.price,
          listing_id: listing.id,
        };
      })
    );

    cards.value = cardsWithDetails;
  } catch (error) {
    console.error('Error fetching marketplace cards:', error.response?.data || error);
  }
};

const selectCard = (card) => {
  router.push({ name: 'MarketplaceDetail', params: { id: card.id } });
};

const goToMyListings = () => {
  router.push({ name: 'MyMarketplaceListings' });
};

const filteredCards = computed(() => {
  let filtered = [...cards.value];

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

  if (minPrice.value !== null) {
    filtered = filtered.filter(card => card.price >= minPrice.value);
  }

  if (maxPrice.value !== null) {
    filtered = filtered.filter(card => card.price <= maxPrice.value);
  }

  switch (sortOption.value) {
    case 'name_asc':
      filtered.sort((a, b) => a.name.localeCompare(b.name));
      break;
    case 'price_asc':
      filtered.sort((a, b) => a.price - b.price);
      break;
    case 'price_desc':
      filtered.sort((a, b) => b.price - a.price);
      break;
    case 'created_desc':
      filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
    case 'created_asc':
      filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
      break;
  }

  return filtered;
});
</script>


<style scoped>
.banner {
  width: 100%;
  height: 11vh;
  margin-top: 0.6rem;
}

.marketplace-header-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 1.2rem;
  padding-right: 2rem;
}

.my-listings-btn {
  font-size: 1.2rem;
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
}

.marketplace-container {
  padding: 2vw;
  text-align: center;
}

.marketplace-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(17rem, 1fr));
  gap: 1.5rem;
  justify-items: center;
  width: 100%;
}

.marketplace-card {
  background-color: #f8f9fa;
  border-radius: 0.625rem;
  padding: 1rem;
  box-shadow: 0 0 0.625rem rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 20rem;
  cursor: pointer;
  transition: transform 0.2s ease-in-out;
}

.marketplace-card:hover {
  transform: scale(1.05);
}

.price-container {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
}

.price {
  font-size: 1rem;
  font-weight: bold;
  color: #007bff;
}

.coin-icon {
  width: 1.2rem;
  margin-left: 0.5rem;
}

.empty-message {
  font-size: 1rem;
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
  font-size: 1rem;
}

.filters {
  margin: 1.5rem auto;
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.filters select {
  padding: 0.6rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #ccc;
  font-size: 1rem;
}

@media (max-width: 1024px) {
  .banner {
    object-fit: cover;
  }
}

@media (max-width: 768px) { 
  .marketplace-header-actions {
    justify-content: center;
    padding-right: 0;
  }

  .my-listings-btn {
    width: 100%;
    font-size: 1.1rem;
  }

  .price {
    font-size: 0.95rem;
  }

  .coin-icon {
    width: 1rem;
  }

  .empty-message {
    font-size: 0.95rem;
  }
}

@media (max-width: 480px) {
  .banner {
    height: 10vh;
  }
}
</style>
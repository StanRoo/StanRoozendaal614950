<template>
  <header>
    <img class="banner" :src="MarketplaceBanner" alt="Marketplace Banner" />
  </header>

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
        v-for="card in cards"
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
    <p v-if="loading" class="text-muted">Loading more cards...</p>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import CardDisplay from '@/Components/CardDisplay.vue';
import MarketplaceBanner from '@/assets/images/Marketplace_Banner.png';

defineEmits(['profileUpdated']);
const router = useRouter();

const cards = ref([]);
const offset = ref(0);
const limit = 20;
const hasMore = ref(true);
const loading = ref(false);
const searchQuery = ref('');
const selectedRarity = ref('');
const selectedType = ref('');
const sortOption = ref('name_asc');

onMounted(() => {
  fetchMarketplaceCards();
  window.addEventListener('scroll', handleScroll);
});

const fetchMarketplaceCards = async () => {
  if (loading.value || !hasMore.value) return;
  loading.value = true;

  try {
    const token = localStorage.getItem('token');
    if (!token) {
      router.push("/");
    }

    const params = {
      search: searchQuery.value,
      rarity: selectedRarity.value,
      type: selectedType.value,
      sort: sortOption.value,
      offset: offset.value,
      limit,
    };

    const response = await axios.get('/marketplace/list', {
      headers: { Authorization: `Bearer ${token}` },
      params,
    });

    const listings = response.data.listings || [];
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

    cards.value.push(...cardsWithDetails);
    offset.value += limit;

    if (cardsWithDetails.length < limit) {
      hasMore.value = false;
    }
  } catch (error) {
    console.error('Error fetching marketplace cards:', error.response?.data || error);
  } finally {
    loading.value = false;
  }
};

watch([searchQuery, selectedRarity, selectedType, sortOption], async () => {
  cards.value = [];
  offset.value = 0;
  hasMore.value = true;
  await fetchMarketplaceCards();
});

const handleScroll = () => {
  const scrollBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 200;
  if (scrollBottom) {
    fetchMarketplaceCards();
  }
};

const selectCard = (card) => {
  router.push({ name: 'MarketplaceDetail', params: { id: card.id } });
};

const goToMyListings = () => {
  router.push({ name: 'MyMarketplaceListings' });
};
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

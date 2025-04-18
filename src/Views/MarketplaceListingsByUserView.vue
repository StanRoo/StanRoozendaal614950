<template>
    <header>
      <img class="banner" :src="MyListingsBanner" alt="My Listings Banner" />
    </header>
  
    <div class="my-listings-container">
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
  
      <p v-else class="empty-message">You haven't listed any cards yet.</p>
    </div>
  </template>
  
  <script setup>
  import { useRouter } from 'vue-router';
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  import CardDisplay from '@/Components/CardDisplay.vue';
  import MyListingsBanner from '@/assets/images/My_Listings_Banner.png';
  
  const router = useRouter();
  const cards = ref([]);
  
  onMounted(async () => {
    await fetchMyMarketplaceCards();
  });
  
  const fetchMyMarketplaceCards = async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) return;
  
      const listingsResponse = await axios.get('/marketplace/userListings', {
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
      console.error('Error fetching your listings:', error.response?.data || error);
    }
  };
  
  const selectCard = (card) => {
    router.push({ name: 'MarketplaceDetail', params: { id: card.id } });
  };
  </script>
  
  <style scoped>
  .banner {
    width: 100%;
    margin-top: 10px;
  }
  
  .my-listings-container {
    padding: 2vw;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .page-title {
    font-size: 2vw;
    font-weight: bold;
    margin-bottom: 2vw;
  }
  
  .marketplace-grid {
    display: grid;
    width: auto;
    grid-template-columns: repeat(4, 1fr);
    gap: 2vw;
    justify-items: center;
  }
  
  .marketplace-card {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 1.5vw;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 21vw;
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
  }
  
  .marketplace-card:hover {
    transform: scale(1.05);
  }
  
  .price-container {
    margin-top: 1vw;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .price {
    font-size: 1.4vw;
    font-weight: bold;
    color: #007bff;
  }
  
  .coin-icon {
    width: 2vw;
    margin-left: 0.5vw;
  }
  
  .empty-message {
    font-size: 1.2vw;
    color: gray;
    margin-top: 2vw;
  }
  </style>  
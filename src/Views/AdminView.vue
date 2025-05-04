<template>
  <header>
    <img class="banner" :src="AdminPanelBanner" alt="Admin Panel Banner" />
  </header>

  <!--Admin Tabs (Desktop)-->
  <div class="admin-tabs">
    <div class="tab-buttons">
      <button 
        v-for="tab in tabs" 
        :key="tab" 
        :class="{ active: activeTab === tab }" 
        @click="activeTab = tab"
      >
        {{ tab }}
      </button>
    </div>

    <!--Admin Dropdown (Tablet/Mobile)-->
    <div class="tab-dropdown">
      <select v-model="activeTab" class="input-small">
        <option 
          v-for="tab in tabs" 
          :key="tab" 
          :value="tab"
        >
          {{ tab }}
        </option>
      </select>
    </div>
  </div>

  <div class="admin-panel">
    <component :is="getTabComponent(activeTab)" />
  </div>
</template>

<script>
import AdminPanelBanner from '@/assets/images/Admin_Panel_Banner.png'
import UsersTab from '@/Components/AdminTabs/UsersTab.vue'
import CardsTab from '@/Components/AdminTabs/CardsTab.vue'
import ListingsTab from '@/Components/AdminTabs/ListingsTab.vue'
import BidsTab from '@/Components/AdminTabs/BidsTab.vue'
import TransactionsTab from '@/Components/AdminTabs/TransactionsTab.vue'

export default {
  emits: ['profileUpdated'],
  components: {
    UsersTab,
    CardsTab,
    ListingsTab,
    BidsTab,
    TransactionsTab
  },
  data() {
    return {
      AdminPanelBanner,
      activeTab: 'Users',
      tabs: ['Users', 'Cards', 'Listings', 'Bids', 'Transactions']
    }
  },
  methods: {
    getTabComponent(tab) {
      return {
        Users: 'UsersTab',
        Cards: 'CardsTab',
        Listings: 'ListingsTab',
        Bids: 'BidsTab',
        Transactions: 'TransactionsTab'
      }[tab]
    }
  }
}
</script>

<style scoped>
.banner {
  width: 100%;
  height: 11vh;
  margin-top: 0.6rem;
}

.admin-tabs {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  margin: 1.5rem;
  gap: 1rem;
}

.tab-buttons {
  display: flex;
  gap: 1rem;
}

.tab-buttons button {
  padding: 0.6rem 1.2rem;
  background-color: #eee;
  border: none;
  cursor: pointer;
  border-radius: 5px;
  font-weight: bold;
}

.tab-buttons button.active {
  background-color: #3366af;
  color: white;
}

.tab-dropdown {
  display: none;
}

.tab-dropdown select {
  padding: 0.6rem;
  font-size: 1rem;
  border-radius: 5px;
  border: 1px solid #ccc;
  background-color: #eee;
  font-weight: bold;
}

@media (max-width: 1024px) {
  .banner {
    object-fit: cover;
  }
}

@media (max-width: 600px) {
  .banner {
    height: 10vh;
  }

  .tab-buttons {
    display: none;
  }

  .tab-dropdown {
    display: block;
  }
}
</style>
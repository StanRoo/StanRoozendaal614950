<template>
  <header>
    <img class="banner" :src="AdminPanelBanner" alt="Admin Panel Banner" />
  </header>

  <div class="admin-tabs">
    <button 
      v-for="tab in tabs" 
      :key="tab" 
      :class="{ active: activeTab === tab }" 
      @click="activeTab = tab"
    >
      {{ tab }}
    </button>
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
  margin-top: 10px;
}

.admin-tabs {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin: 20px;
}

.admin-tabs button {
  padding: 10px 20px;
  background-color: #eee;
  border: none;
  cursor: pointer;
  border-radius: 5px;
  font-weight: bold;
}

.admin-tabs button.active {
  background-color: #3366af;
  color: white;
}
</style>
<template>
  <div class="admin-panel" v-if="cards">

    <!--Filters-->
    <div class="filters">
      <input
        v-model="filters.owner_id"
        @input="onFilterChange"
        type="number"
        placeholder="Filter by Owner ID"
        class="filter-input"
      />
      <input
        v-model="filters.name"
        @input="onFilterChange"
        placeholder="Filter by Name"
        class="filter-input"
      />
      <select v-model="filters.rarity" @change="onFilterChange" class="filter-select">
        <option value="">Filter by Rarity -None-</option>
        <option value="common">Common</option>
        <option value="rare">Rare</option>
        <option value="epic">Epic</option>
        <option value="legendary">Legendary</option>
      </select>
      <select v-model="filters.is_listed" @change="onFilterChange" class="filter-select">
        <option value="">Filter by Listed -None-</option>
        <option value="1">Listed</option>
        <option value="0">Not Listed</option>
      </select>
      <select v-model="filters.created_at" @change="onFilterChange" class="filter-select">
        <option value="">Created At -None-</option>
        <option value="asc">Created At ↑</option>
        <option value="desc">Created At ↓</option>
      </select>
    </div>

    <!--Table-->
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Image</th>
          <th>Owner ID</th>
          <th>Name</th>
          <th>Type</th>
          <th>Rarity</th>
          <th>Stats</th>
          <th>Listed</th>
          <th>Created At</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="card in cards" :key="card.id">
          <td>{{ card.id }}</td>
          <td><img :src="getImageUrl(card.image_url)" class="card-img" alt="Card" /></td>
          <td>
            <input type="number" v-model.number="card.owner_id" @change="updateCard(card)" class="input-small" />
          </td>
          <td>{{ card.name }}</td>
          <td>{{ card.type }}</td>
          <td>{{ card.rarity }}</td>
          <td>
            <div>HP: {{ card.hp }}</div>
            <div>ATK: {{ card.attack }}</div>
            <div>DEF: {{ card.defense }}</div>
            <div>SPD: {{ card.speed }}</div>
          </td>
          <td>
            <input
              type="checkbox"
              :checked="card.is_listed"
              @change="toggleListed(card)"
            />
          </td>
          <td>{{ formatDate(card.created_at) }}</td>
          <td>
            <button class="delete-btn" @click="deleteCard(card.id)">❌ Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!--Pagination-->
    <div class="pagination">
      <button :disabled="page === 1" @click="changePage(page - 1)">Previous</button>
      <span>Page {{ page }} of {{ totalPages }}</span>
      <button :disabled="page === totalPages" @click="changePage(page + 1)">Next</button>
    </div>

    <!--User Feedback-->
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    <p v-if="successMessage" class="succes">{{ successMessage }}</p>
  </div>

  <!--Loading State-->
  <div v-else class="loading">Loading admin details...</div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      cards: [],
      page: 1,
      limit: 10,
      totalPages: 1,
      filters: {
        owner_id: '',
        name: '',
        rarity: '',
        is_listed: '',
        created_at: '',
      },
      errorMessage: '',
      successMessage: '',
    }
  },
  async created() {
    await this.fetchCards()
  },
  methods: {
    async fetchCards() {
      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push("/");
        }
        const response = await axios.get('/admin/cards', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            page: this.page,
            limit: this.limit,
            ...this.filters,
          }
        })
        const { cards, pagination } = response.data
        this.cards = cards
        this.totalPages = pagination.totalPages
      } catch (error) {
        this.errorMessage = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessage = ''; }, 3000);
      }
    },
    onFilterChange() {
      this.page = 1
      this.fetchCards()
    },
    changePage(newPage) {
      if (newPage < 1 || newPage > this.totalPages) return
      this.page = newPage
      this.fetchCards()
    },
    async updateCard(card) {
      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push("/");
        }
        await axios.put(`/admin/cards/${card.id}`, {
          owner_id: card.owner_id,
          is_listed: card.is_listed,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.successMessage = 'Card updated successfully!'
        setTimeout(() => { this.successMessage = ''; }, 3000);
      } catch (error) {
        this.errorMessage = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessage = ''; }, 3000);
      }
    },
    async deleteCard(cardId) {
      if (!confirm('Are you sure you want to delete this card?')) return
      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push("/");
        }
        await axios.delete(`/admin/cards/${cardId}`, {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.cards = this.cards.filter(card => card.id !== cardId)
        this.successMessage = 'Card deleted successfully!'
        setTimeout(() => { this.successMessage = ''; }, 3000);
      } catch (error) {
        this.errorMessage = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessage = ''; }, 3000);
      }
    },
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleString()
    },
    getImageUrl(imagePath) {
      return `/uploads/${imagePath.replace(/\\/g, '/')}`
    },
    async toggleListed(card) {
      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push("/");
        }
        card.is_listed = card.is_listed ? 0 : 1
        await axios.put(`/admin/cards/${card.id}`, {
          owner_id: card.owner_id,
          is_listed: card.is_listed,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.successMessage = `Listing status updated!`
        setTimeout(() => { this.successMessage = ''; }, 3000);
      } catch (error) {
        this.errorMessage = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessage = ''; }, 3000);
      }
    }
  }
}
</script>

<style scoped>
.admin-panel {
  padding: 20px;
  text-align: center;
  overflow-x: auto;
}

.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 1.5rem;
  width: 100%;
}

.filter-input,
.filter-select {
  padding: 0.5rem;
  border-radius: 5px;
  border: 1px solid #ccc;
  width: 14.8%;
}

.filter-input:focus,
.filter-select:focus {
  border-color: #3366af;
}

table {
  width: 100%;
  min-width: 1200px;
  border-collapse: collapse;
}

th,
td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: center;
  white-space: nowrap;
}

th {
  background-color: #333;
  color: white;
}

input[type='checkbox'] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.card-img {
  width: 40px;
  height: auto;
  border-radius: 6px;
  object-fit: cover;
}

.input-small {
  width: 60px;
  padding: 4px;
}

select {
  padding: 4px;
}

input {
  padding: 5px;
  width: 100px;
}

.delete-btn {
  background-color: red;
  color: white;
  padding: 5px 10px;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

.pagination {
  margin-top: 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
}

.pagination button {
  padding: 0.5rem 1rem;
  border: none;
  background-color: #3366af;
  color: white;
  cursor: pointer;
  border-radius: 5px;
}

.pagination button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.error {
  text-align: center;
  color: red;
  margin-top: 5px;
}

.succes {
  text-align: center;
  color: green;
  margin-top: 5px;
}

.loading {
  font-size: 1.5rem;
  color: gray;
}

@media (max-width: 768px) {
  .admin-panel {
    box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
  }

  .filters {
    flex-direction: column;
    align-items: center;
  }

  .filter-input,
  .filter-select {
    width: 90%;
  }
}
</style>
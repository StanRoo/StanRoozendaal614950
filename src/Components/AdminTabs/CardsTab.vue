<template>
  <div class="admin-panel">
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

    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    <p v-if="successMessage" class="success-message">{{ successMessage }}</p>
  </div>
</template>

<script>
import axios from 'axios'
import { handleApiError } from '@/Utils/errorHandler'

export default {
  data() {
    return {
      cards: [],
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
        const response = await axios.get('/admin/cards', {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.cards = response.data.cards
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    async updateCard(card) {
      try {
        const token = localStorage.getItem('token')
        await axios.put(`/admin/cards/${card.id}`, {
          owner_id: card.owner_id,
          is_listed: card.is_listed,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.successMessage = 'Card updated successfully!'
        this.errorMessage = ''
      } catch (error) {
        this.successMessage = ''
        this.errorMessage = handleApiError(error)
      }
    },
    async deleteCard(cardId) {
      if (!confirm('Are you sure you want to delete this card?')) return
      try {
        const token = localStorage.getItem('token')
        await axios.delete(`/admin/cards/${cardId}`, {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.cards = this.cards.filter(card => card.id !== cardId)
        this.successMessage = 'Card deleted successfully!'
        this.errorMessage = ''
      } catch (error) {
        this.successMessage = ''
        this.errorMessage = handleApiError(error)
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
        card.is_listed = card.is_listed ? 0 : 1
        await axios.put(`/admin/cards/${card.id}`, {
          owner_id: card.owner_id,
          is_listed: card.is_listed,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.successMessage = `Listing status updated!`
        this.errorMessage = ''
      } catch (error) {
        this.successMessage = ''
        this.errorMessage = handleApiError(error)
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

table {
  width: 100%;
  min-width: 1200px;
  border-collapse: collapse;
}

th, td {
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
}

.input-small {
  width: 60px;
  padding: 4px;
}

select {
  padding: 4px;
}

.delete-btn {
  background-color: red;
  color: white;
  padding: 5px 10px;
  border: none;
  cursor: pointer;
}

.error-message {
  color: red;
  margin-top: 10px;
  font-weight: bold;
}

.success-message {
  color: green;
  margin-top: 10px;
  font-weight: bold;
}

@media (max-width: 768px) {
  .admin-panel {
    box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
  }
}
</style>
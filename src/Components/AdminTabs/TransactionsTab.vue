<template>
  <div class="transactions-tab">
    <div class="filters">
      <input v-model="filters.buyer" placeholder="Filter by Buyer" @input="onFilterChange" class="filter-input" />
      <input v-model="filters.seller" placeholder="Filter by Seller" @input="onFilterChange" class="filter-input" />

      <select v-model="filters.status" @change="onFilterChange" class="filter-select">
        <option value="">Filter by Status -None-</option>
        <option value="pending">Pending</option>
        <option value="completed">Completed</option>
        <option value="failed">Failed</option>
      </select>

      <select v-model="filters.date" @change="onFilterChange" class="filter-select">
        <option value="">Filter by Date -None-</option>
        <option value="asc">Date ↑</option>
        <option value="desc">Date ↓</option>
      </select>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Buyer</th>
          <th>Seller</th>
          <th>Card</th>
          <th>Price</th>
          <th>Status</th>
          <th>Date</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="txn in transactions" :key="txn.id">
          <td>{{ txn.id }}</td>
          <td>{{ txn.buyer_username }}</td>
          <td>{{ txn.seller_username }}</td>
          <td class="card-cell">
            <div class="card-name">{{ txn.card_name || 'N/A' }}</div>
            <div class="card-rarity">{{ txn.card_rarity || 'N/A' }}</div>
          </td>
          <td>{{ formatPrice(txn.price) }}</td>
          <td :class="txn.status.toLowerCase()">{{ txn.status }}</td>
          <td>{{ formatDate(txn.transaction_date) }}</td>
          <td>
            <button class="delete-btn" @click="deleteTransaction(txn.id)">❌ Delete</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="pagination">
      <button 
        :disabled="page === 1" 
        @click="changePage(page - 1)"
      >
        Previous
      </button>

      <span>Page {{ page }} of {{ totalPages }}</span>

      <button 
        :disabled="page === totalPages" 
        @click="changePage(page + 1)"
      >
        Next
      </button>
    </div>

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
      transactions: [],
      page: 1,
      limit: 10,
      totalPages: 1,
      filters: {
        buyer: '',
        seller: '',
        status: '',
        date: ''
      },
      errorMessage: '',
      successMessage: '',
    }
  },
  async created() {
    await this.fetchTransactions()
  },
  methods: {
    async fetchTransactions() {
      try {
        const token = localStorage.getItem('token')
        const response = await axios.get('/admin/transactions', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            page: this.page,
            limit: this.limit,
            buyer: this.filters.buyer,
            seller: this.filters.seller,
            status: this.filters.status,
            date: this.filters.date
          }
        })

        const { transactions, pagination } = response.data
        this.transactions = transactions
        this.totalPages = pagination.totalPages
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    onFilterChange() {
      this.page = 1
      this.fetchTransactions()
    },
    async deleteTransaction(transactionId) {
      if (!confirm('Are you sure you want to delete this transaction?')) return

      try {
        const token = localStorage.getItem('token')
        await axios.delete(`/admin/transactions/${transactionId}`, {
          headers: { Authorization: `Bearer ${token}` },
        })

        this.successMessage = 'Transaction deleted successfully!'
        this.transactions = this.transactions.filter(txn => txn.id !== transactionId)
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    changePage(newPage) {
      if (newPage < 1 || newPage > this.totalPages) return
      this.page = newPage
      this.fetchTransactions()
    },
    formatPrice(value) {
      return `${parseFloat(value).toFixed(2)} credits`
    },
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleString()
    }
  }
}
</script>

<style scoped>
.transactions-tab {
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

th, td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: center;
  vertical-align: middle;
  white-space: nowrap;
}

th {
  background-color: #333;
  color: white;
}

.card-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.card-name {
  font-size: 0.9rem;
  font-weight: bold;
  margin-top: 5px;
}

.pending {
  color: orange;
  font-weight: bold;
}

.completed {
  color: green;
  font-weight: bold;
}

.failed {
  color: red;
  font-weight: bold;
}

.delete-btn {
  background-color: red;
  color: white;
  padding: 5px 10px;
  border: none;
  cursor: pointer;
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

.error-message {
  color: red;
  margin-top: 10px;
}

.success-message {
  color: green;
  margin-top: 10px;
}

@media (max-width: 768px) {
  .transactions-tab {
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
<template>
  <div class="transactions-tab">
    <h2>All Transactions</h2>

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
          headers: { Authorization: `Bearer ${token}` }
        })
        this.transactions = response.data.transactions
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
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
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  padding: 10px;
  border: 1px solid #ccc;
  text-align: center;
  vertical-align: middle;
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

.error-message {
  color: red;
  margin-top: 10px;
}

.success-message {
  color: green;
  margin-top: 10px;
}

.delete-btn {
  background-color: red;
  color: white;
  padding: 5px 10px;
  border: none;
  cursor: pointer;
}
</style>
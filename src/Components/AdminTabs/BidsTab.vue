<template>
  <div class="admin-panel">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Listing ID</th>
          <th>Bidder ID</th>
          <th>Bid Amount</th>
          <th>Created At</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="bid in bids" :key="bid.id">
          <td>{{ bid.id }}</td>
          <td>{{ bid.listing_id }}</td>
          <td>{{ bid.bidder_id }}</td>
          <td>{{ bid.bid_amount }}</td>
          <td>{{ bid.created_at }}</td>
          <td>
            <button class="delete-btn" @click="deleteBid(bid.id)">❌ Delete</button>
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
      bids: [],
      errorMessage: '',
      successMessage: '',
    }
  },
  async created() {
    await this.fetchBids()
  },
  methods: {
    async fetchBids() {
      try {
        const token = localStorage.getItem('token')
        const response = await axios.get('/admin/bids', {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.bids = response.data.bids
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    async deleteBid(bidId) {
      if (!confirm('Are you sure you want to delete this bid?')) return

      try {
        const token = localStorage.getItem('token')
        await axios.delete(`/admin/bids/${bidId}`, {
          headers: { Authorization: `Bearer ${token}` },
        })

        this.successMessage = 'Bid deleted successfully!'
        this.bids = this.bids.filter(bid => bid.id !== bidId)
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
  },
}
</script>

<style scoped>
.admin-panel {
  padding: 20px;
  text-align: center;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: center;
}

th {
  background-color: #333;
  color: white;
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
}

.success-message {
  color: green;
  margin-top: 10px;
}
</style>
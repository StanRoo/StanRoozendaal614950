<template>
  <div class="admin-panel">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Card ID</th>
          <th>Seller ID</th>
          <th>Price</th>
          <th>Min Bid</th>
          <th>Listed At</th>
          <th>Expires At</th>
          <th>Status</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="listing in listings" :key="listing.id">
          <td>{{ listing.id }}</td>
          <td>{{ listing.card_id }}</td>
          <td>{{ listing.seller_id }}</td>
          <td>{{ listing.price }}</td>
          <td>{{ listing.min_bid_price }}</td>
          <td>{{ formatDate(listing.listed_at) }}</td>
          <td>{{ formatDate(listing.expires_at) }}</td>
          <td>
            <select v-model="listing.status" @change="updateListing(listing)">
              <option value="active">Active</option>
              <option value="sold">Sold</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </td>
          <td>
            <button class="delete-btn" @click="deleteListing(listing.id)">❌ Delete</button>
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
      listings: [],
      errorMessage: '',
      successMessage: '',
    }
  },
  async created() {
    await this.fetchListings()
  },
  methods: {
    async fetchListings() {
      try {
        const token = localStorage.getItem('token')
        const response = await axios.get('/admin/listings', {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.listings = response.data.listings
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    async updateListing(listing) {
      try {
        const token = localStorage.getItem('token')
        await axios.put(`/admin/listings/${listing.id}`, {
          status: listing.status,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.successMessage = 'Listing updated successfully!'
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    async deleteListing(listingId) {
      if (!confirm('Are you sure you want to delete this listing?')) return

      try {
        const token = localStorage.getItem('token')
        await axios.delete(`/admin/listings/${listingId}`, {
          headers: { Authorization: `Bearer ${token}` },
        })

        this.successMessage = 'Listing deleted successfully!'
        this.listings = this.listings.filter(l => l.id !== listingId)
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleString()
    },
  },
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

select {
  padding: 5px;
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

@media (max-width: 768px) {
  .admin-panel {
    box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
  }
}
</style>
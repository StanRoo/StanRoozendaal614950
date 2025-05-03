<template>
  <div class="admin-panel">
    <div class="filters">
      <input v-model="filters.card_id" placeholder="Filter by Card ID" @input="onFilterChange" class="filter-input" />
      <input v-model="filters.seller_id" placeholder="Filter by Seller ID" @input="onFilterChange" class="filter-input" />

      <select v-model="filters.listed_at" @change="onFilterChange" class="filter-select">
        <option value="">Listed At -None-</option>
        <option value="ASC">Listed At ↑</option>
        <option value="DESC">Listed At ↓</option>
      </select>

      <select v-model="filters.expires_at" @change="onFilterChange" class="filter-select">
        <option value="">Expires At -None-</option>
        <option value="ASC">Expires At ↑</option>
        <option value="DESC">Expires At ↓</option>
      </select>

      <select v-model="filters.status" @change="onFilterChange" class="filter-select">
        <option value="">Status -None-</option>
        <option value="active">Active</option>
        <option value="sold">Sold</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>

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

    <div class="pagination">
      <button :disabled="page === 1" @click="changePage(page - 1)">Previous</button>
      <span>Page {{ page }} of {{ totalPages }}</span>
      <button :disabled="page === totalPages" @click="changePage(page + 1)">Next</button>
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
      listings: [],
      page: 1,
      limit: 10,
      totalPages: 1,
      filters: {
        card_id: '',
        seller_id: '',
        listed_at: '',
        expires_at: '',
        status: ''
      },
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
          params: {
            page: this.page,
            limit: this.limit,
            card_id: this.filters.card_id,
            seller_id: this.filters.seller_id,
            listed_at: this.filters.listed_at,
            expires_at: this.filters.expires_at,
            status: this.filters.status
          }
        })

        const { listings, pagination } = response.data
        this.listings = listings
        this.totalPages = pagination.totalPages
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    onFilterChange() {
      this.page = 1
      this.fetchListings()
    },
    async updateListing(listing) {
      try {
        const token = localStorage.getItem('token')
        await axios.put(`/admin/listings/${listing.id}`, {
          status: listing.status
        }, {
          headers: { Authorization: `Bearer ${token}` }
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
          headers: { Authorization: `Bearer ${token}` }
        })

        this.successMessage = 'Listing deleted successfully!'
        this.fetchListings()
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    formatDate(dateStr) {
      return new Date(dateStr).toLocaleString()
    },
    changePage(newPage) {
      if (newPage < 1 || newPage > this.totalPages) return
      this.page = newPage
      this.fetchListings()
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
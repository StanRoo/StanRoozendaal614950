<template>
  <div class="admin-panel" v-if="bids">

    <div class="filters">
      <input v-model="filters.listing_id" placeholder="Filter by Listing ID" @input="onFilterChange" type="number" class="filter-input" />
      <input v-model="filters.bidder_id" placeholder="Filter by Bidder ID" @input="onFilterChange" type="number" class="filter-input" />

      <select v-model="filters.created_at" @change="onFilterChange" class="filter-select">
        <option value="">Sort by Created At -None-</option>
        <option value="asc">Created At ↑</option>
        <option value="desc">Created At ↓</option>
      </select>
    </div>

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

    <div class="pagination">
      <button :disabled="page === 1" @click="changePage(page - 1)">Previous</button>
      <span>Page {{ page }} of {{ totalPages }}</span>
      <button :disabled="page === totalPages" @click="changePage(page + 1)">Next</button>
    </div>

    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    <p v-if="successMessage" class="succes">{{ successMessage }}</p>
  </div>
  <div v-else class="loading">Loading admin details...</div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      bids: [],
      page: 1,
      limit: 10,
      totalPages: 1,
      filters: {
        listing_id: '',
        bidder_id: '',
        created_at: '',
      },
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
        if (!token) {
          this.$router.push("/");
        }
        const response = await axios.get('/admin/bids', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            page: this.page,
            limit: this.limit,
            ...this.filters
          }
        })

        const { bids, pagination } = response.data
        this.bids = bids
        this.totalPages = pagination.totalPages
      } catch (error) {
        this.errorMessage = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessage = ''; }, 3000);
      }
    },
    async deleteBid(bidId) {
      if (!confirm('Are you sure you want to delete this bid?')) return

      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push("/");
        }
        await axios.delete(`/admin/bids/${bidId}`, {
          headers: { Authorization: `Bearer ${token}` },
        })

        this.successMessage = 'Bid deleted successfully!'
        setTimeout(() => { this.successMessage = ''; }, 3000);
        this.bids = this.bids.filter(bid => bid.id !== bidId)
      } catch (error) {
        this.errorMessage = error.response?.data?.message || error.message || "Something went wrong.";
        setTimeout(() => { this.errorMessage = ''; }, 3000);
      }
    },
    onFilterChange() {
      this.page = 1
      this.fetchBids()
    },
    changePage(newPage) {
      if (newPage < 1 || newPage > this.totalPages) return
      this.page = newPage
      this.fetchBids()
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

.error {
  text-align: center;
  color: red;
  margin-top: 5px;
}

.succes{
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
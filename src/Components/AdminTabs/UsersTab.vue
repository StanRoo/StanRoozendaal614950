<template>
  <div class="admin-panel">

    <div class="filters">
      <input v-model="filters.id" placeholder="Filter by ID" @input="onFilterChange" type="number" class="filter-input" />
      <input v-model="filters.username" placeholder="Filter by Username" @input="onFilterChange" class="filter-input" />
      <input v-model="filters.email" placeholder="Filter by Email" @input="onFilterChange" class="filter-input" />
      
      <select v-model="filters.status" @change="onFilterChange" class="filter-select">
        <option value="">Filter by Status -None-</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
        <option value="banned">Banned</option>
      </select>

      <select v-model="filters.role" @change="onFilterChange" class="filter-select">
        <option value="">Filter by Role -None-</option>
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>

      <select v-model="filters.last_login" @change="onFilterChange" class="filter-select">
        <option value="">Filter by Last Login -None-</option>
        <option value="asc">Last Login ↑</option>
        <option value="desc">Last Login ↓</option>
      </select>
    </div>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Profile</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Bio</th>
          <th>Created At</th>
          <th>Updated At</th>
          <th>Last Login</th>
          <th>Balance</th>
          <th>Last Daily Claim</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.id }}</td>
          <td>
            <img :src="user.profile_picture_url" class="profile-pic" alt="Profile" />
          </td>
          <td>{{ user.username }}</td>
          <td>{{ user.email }}</td>
          <td>
            <select v-model="user.role" @change="updateUser(user)">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </td>
          <td>
            <select v-model="user.status" @change="updateUser(user)">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="banned">Banned</option>
            </select>
          </td>
          <td>{{ user.bio }}</td>
          <td>{{ user.created_at }}</td>
          <td>{{ user.updated_at }}</td>
          <td>{{ user.last_login }}</td>
          <td>
            <input v-model="user.balance" @blur="updateUser(user)" type="number" step="0.01" min="0" />
          </td>
          <td>{{ user.last_daily_claim }}</td>
          <td>
            <button class="delete-btn" @click="deleteUser(user.id)">❌ Delete</button>
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
      users: [],
      page: 1,
      limit: 10,
      totalPages: 1,
      filters: {
        id: '',
        username: '',
        email: '',
        status: '',
        role: '',
        last_login: ''
      },
      errorMessage: '',
      successMessage: '',
    }
  },
  async created() {
    await this.fetchUsers()
  },
  methods: {
    async fetchUsers() {
      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push("/");
        }
        const response = await axios.get('/users', {
          headers: { Authorization: `Bearer ${token}` },
          params: {
            page: this.page,
            limit: this.limit,
            id: this.filters.id,
            username: this.filters.username,
            email: this.filters.email,
            status: this.filters.status,
            role: this.filters.role,
            last_login: this.filters.last_login
          }
        })

        const { users, pagination } = response.data
        this.users = users
        this.totalPages = pagination.totalPages
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    onFilterChange() {
      this.page = 1
      this.fetchUsers()
    },
    async updateUser(user) {
      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push("/");
        }
        await axios.put(`/users/${user.id}`, {
          role: user.role,
          status: user.status,
          balance: user.balance,
        }, {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.successMessage = 'User updated successfully!'
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    async deleteUser(userId) {
      if (!confirm('Are you sure you want to delete this user?')) return

      try {
        const token = localStorage.getItem('token')
        if (!token) {
          this.$router.push("/");
        }
        await axios.delete(`/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` },
        })

        this.successMessage = 'User deleted successfully!'
        this.users = this.users.filter(user => user.id !== userId)
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    changePage(newPage) {
      if (newPage < 1 || newPage > this.totalPages) return
      this.page = newPage
      this.fetchUsers()
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

.profile-pic {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

select {
  padding: 5px;
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
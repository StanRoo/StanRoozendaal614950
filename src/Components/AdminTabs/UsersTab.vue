<template>
  <div class="admin-panel">
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
        const response = await axios.get('/users', {
          headers: { Authorization: `Bearer ${token}` },
        })
        this.users = response.data.users;
      } catch (error) {
        this.errorMessage = handleApiError(error)
      }
    },
    async updateUser(user) {
      try {
        const token = localStorage.getItem('token')
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
        await axios.delete(`/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` },
        })

        this.successMessage = 'User deleted successfully!'
        this.users = this.users.filter(user => user.id !== userId)
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
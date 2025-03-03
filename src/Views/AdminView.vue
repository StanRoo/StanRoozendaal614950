<template>
  <div class="admin-panel">
    <h1>Admin Panel - User List</h1>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Last Login</th>
          <th>Created At</th>
          <th>Updated At</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.id }}</td>
          <td>{{ user.username }}</td>
          <td>{{ user.email }}</td>
          <td>{{ user.role }}</td>
          <td>{{ user.status }}</td>
          <td>{{ user.last_login || 'Never' }}</td>
          <td>{{ user.created_at }}</td>
          <td>{{ user.updated_at }}</td>
        </tr>
      </tbody>
    </table>

    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      users: [],
      errorMessage: "",
    };
  },
  async created() {
    try {
      const token = localStorage.getItem("token");
      const response = await axios.get("/users", {
        headers: { Authorization: `Bearer ${token}` }
      });

      this.users = response.data.users;
    } catch (error) {
      console.error("Failed to fetch users:", error);
      this.errorMessage = "Failed to load user data.";
    }
  }
};
</script>

<style>
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
}

th {
  background-color: #333;
  color: white;
}

.error-message {
  color: red;
  margin-top: 10px;
}
</style>
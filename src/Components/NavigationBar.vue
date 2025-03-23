<script setup>
import { computed, ref } from "vue";
import { useUserStore } from "@/Store/UserStore";
import CuboCard from "@/assets/icons/CubocardLogo.png";
import CoinIcon from "@/assets/icons/coin.png";

const userStore = useUserStore();
const baseUrl = "http://localhost:8000/";

const user = computed(() => userStore.user);
const isAdmin = computed(() => userStore.user?.role === "admin");
const dropdownVisible = ref(false);
const userBalance = computed(() => userStore.user?.balance ?? 0);

const profilePicture = computed(() => {
  const url = userStore.user?.profile_picture_url;
  if (!url) return "/images/profile.png";

  if (url.startsWith("data:image")) {
    return url;
  }

  if (!url.startsWith("http")) {
    return `${baseUrl}${url}`;
  }

  return url;
});

const toggleDropdown = () => {
  dropdownVisible.value = !dropdownVisible.value;
};

const logout = () => {
  userStore.logout();
  window.location.href = "/";
};
</script>

<template>
  <nav class="navbar sticky-top">
    <div class="nav-left">
      <router-link to="/home" class="nav-link logo">
        <img class="logoImage" :src="CuboCard" />
      </router-link>
      <router-link to="/home" class="nav-link" active-class="active">Home</router-link>
      <router-link to="/cards" class="nav-link" active-class="active">Cards</router-link>
    </div>

    <div class="nav-right">
      <router-link v-if="isAdmin" to="/admin" class="nav-link" active-class="active">Admin Panel</router-link>

      <router-link to="/balance" class="user-balance">
        <img :src="CoinIcon" class="currency-icon" alt="Coin Icon" />
        <span>{{ userBalance.toFixed(2) }}</span>
      </router-link>

      <div class="profile-dropdown profile-container nav-link" active-class="active">
        <img
          :src="profilePicture"
          class="profile-pic"
          alt="Profile Picture"
          @click="toggleDropdown"
        />
        <div v-if="dropdownVisible" class="dropdown-menu">
          <router-link to="/profile" class="dropdown-item" @click="toggleDropdown">
            My Profile
          </router-link>
          <button @click="logout" class="dropdown-item">Logout</button>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar {
  position: fixed;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #3366af;
  height: 60px;
  padding: 0;
  width: 100%;
  box-sizing: border-box;
  z-index: 1000;
}

.nav-left {
  display: flex;
  align-items: center;
  height: 100%;
}

.nav-left .nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  padding: 0 20px;
  display: flex;
  align-items: center;
  height: 100%;
  transition: background 0.3s;
}

.nav-left .nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
}

.nav-left .active {
  background: #4992f8;
  color: white;
}

.nav-right {
  display: flex;
  align-items: center;
  height: 100%;
}

.nav-right .nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  padding: 0 20px;
  display: flex;
  align-items: center;
  height: 100%;
  transition: background 0.3s;
}

.nav-right .nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
}

.nav-right .active {
  background: #4992f8;
  color: white;
}

.icon-link {
  display: flex;
  align-items: center;
  padding: 0 15px;
  height: 100%;
  color: white;
  text-decoration: none;
}

.icon-link:hover {
  background: rgba(255, 255, 255, 0.2);
}

.profile-dropdown {
  position: relative;
}

.profile-container {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 60px;
}

.profile-pic {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid white;
  cursor: pointer;
  transition: 0.3s;
}

.profile-pic:hover {
  transform: scale(1.1);
}

.dropdown-menu {
  color: white;
  position: absolute;
  top: 50px;
  right: 0;
  background: #3366af;
  border-radius: 5px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  z-index: 100;
  display: flex;
  flex-direction: column;
  padding: 10px;
}

.dropdown-item {
  color: white;
  padding: 8px 12px;
  background: #3366af;
  border: none;
  text-align: left;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.3s;
}

.dropdown-item:hover {
  color: white;
  background: rgba(255, 255, 255, 0.2);
}

.logoImage {
  max-width: 55px;
  padding: 0;
  float: left;
}

.item {
  padding: 0;
  max-width: 35px;
  height: 30px;
}

.user-balance {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.15);
  padding: 5px 12px;
  border-radius: 15px;
  font-weight: bold;
  font-size: 1.1rem;
  color: white;
  text-decoration: none;
}

.currency-icon {
  width: 20px;
  height: 20px;
  margin-right: 6px;
  border-radius: 50%;
  object-fit: cover;
}
</style>
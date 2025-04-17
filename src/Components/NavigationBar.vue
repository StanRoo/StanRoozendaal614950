<script setup>
import { computed, ref } from "vue";
import { useUserStore } from "@/Store/UserStore";
import CuboCard from "@/assets/icons/CubocardLogo.png";
import CoinIcon from "@/assets/icons/coin.png";

const userStore = useUserStore();
const baseUrl = "http://localhost:8000/";

const user = computed(() => userStore.user);
const isAdmin = computed(() => userStore.user?.role === "admin");
const dropdownVisibleMarketplace = ref(false);
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
      <router-link to="/inventory" class="nav-link" active-class="active">Inventory</router-link>
      <div
        class="nav-link dropdown-wrapper"
        @mouseenter="dropdownVisibleMarketplace = true"
        @mouseleave="dropdownVisibleMarketplace = false"
      >
        <span class="dropdown-title">Marketplace &#11167;</span>
        <div
          v-if="dropdownVisibleMarketplace"
          class="marketplace-dropdown-menu"
        >
          <router-link to="/marketplace" class="dropdown-item">Marketplace</router-link>
          <router-link to="/myMarketplaceListings" class="dropdown-item">My Listings</router-link>
        </div>
      </div>
      <router-link to="/createCard" class="nav-link" active-class="active">Create Card</router-link>
    </div>

    <div class="nav-right">
      <router-link to="/balance" class="user-balance">
        <img :src="CoinIcon" class="currency-icon" alt="Coin Icon" />
        <span>{{ userBalance.toFixed(2) }}</span>
      </router-link>

      <router-link v-if="isAdmin" to="/admin" class="nav-link" active-class="active">Admin Panel</router-link>

      <div class="profile-dropdown profile-container nav-link">
        <img
          :src="profilePicture"
          class="profile-pic"
          alt="Profile Picture"
        />
        <div class="profile-dropdown-menu">
          <router-link to="/profile" class="dropdown-item">My Profile</router-link>
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

.dropdown-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  height: 100%;
  padding: 0 20px;
  cursor: pointer;
}

.dropdown-title {
  color: white;
  font-size: 1.1rem;
  user-select: none;
}

.marketplace-dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: #3366af;
  border-radius: 5px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  z-index: 100;
  display: flex;
  flex-direction: column;
  padding: 10px;
  width: 8vw;
  color: white;
}

.dropdown-wrapper:hover {
  background: rgba(255, 255, 255, 0.2);
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

.profile-dropdown:hover .profile-dropdown-menu {
  display: flex;
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

.profile-dropdown-menu {
  display: none;
  position: absolute;
  top: 100%;
  right: 0;
  background: #3366af;
  border-radius: 5px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  z-index: 100;
  flex-direction: column;
  padding: 10px;
  width: 8vw;
  color: white;
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
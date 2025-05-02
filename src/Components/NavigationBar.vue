<script setup>
import { ref, computed , onMounted, onBeforeUnmount} from "vue";
import { useUserStore } from "@/Store/UserStore";
import CuboCard from "@/assets/icons/CubocardLogo.png";
import CoinIcon from "@/assets/icons/coin.png";

const userStore = useUserStore();
const baseUrl = "http://localhost:8000/";

const user = computed(() => userStore.user);
const isAdmin = computed(() => userStore.user?.role === "admin");
const dropdownVisibleMarketplace = ref(false);
const dropdownVisibleProfile = ref(false);
const hasClickedMarketplace = ref(false);
const hasClickedProfile = ref(false);
const userBalance = computed(() => userStore.user?.balance ?? 0);
const isHamburgerOpen = ref(false);

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

const toggleHamburgerMenu = () => {
  isHamburgerOpen.value = !isHamburgerOpen.value;
};

const handleClickOutside = (event) => {
  const profileDropdown = document.querySelector(".profile-dropdown");
  const marketplaceDropdown = document.querySelector(".dropdown-wrapper");

  if (profileDropdown && !profileDropdown.contains(event.target)) {
    dropdownVisibleProfile.value = false;
    hasClickedProfile.value = false;
  }

  if (marketplaceDropdown && !marketplaceDropdown.contains(event.target)) {
    dropdownVisibleMarketplace.value = false;
    hasClickedMarketplace.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <nav class="navbar sticky-top">
    <div class="nav-left">
      <router-link to="/home" class="nav-link logo">
        <img class="logoImage" :src="CuboCard" />
      </router-link>
      <div class="desktop-nav-left">
        <router-link to="/home" class="nav-link" active-class="active">Home</router-link>
        <router-link to="/inventory" class="nav-link" active-class="active">Inventory</router-link>
        <div
          class="nav-link dropdown-wrapper"
            @mouseenter="!hasClickedMarketplace && (dropdownVisibleMarketplace = true)"
            @mouseleave="!hasClickedMarketplace && (dropdownVisibleMarketplace = false)"
            @click="() => {
              dropdownVisibleMarketplace = !dropdownVisibleMarketplace;
              hasClickedMarketplace = true;
            }"
        >
          <span class="dropdown-title">Marketplace &#11167;</span>
          <div v-if="dropdownVisibleMarketplace" class="marketplace-dropdown-menu">
            <router-link to="/marketplace" class="dropdown-item">Marketplace</router-link>
            <router-link to="/myMarketplaceListings" class="dropdown-item">My Listings</router-link>
          </div>
        </div>
        <router-link to="/createCard" class="nav-link" active-class="active">Create Card</router-link>
      </div>
    </div>

    <div class="nav-right">
      <router-link to="/balance" class="user-balance">
          <img :src="CoinIcon" class="currency-icon" />
          <span>{{ userBalance.toFixed(2) }}</span>
        </router-link>
      <div class="desktop-nav-right">
        <router-link v-if="isAdmin" to="/admin" class="nav-link" active-class="active">Admin Panel</router-link>

        <div class="profile-dropdown profile-container nav-link" 
          @mouseenter="!hasClickedProfile && (dropdownVisibleProfile = true)"
          @mouseleave="!hasClickedProfile && (dropdownVisibleProfile = false)"
          @click="() => {
            dropdownVisibleProfile = !dropdownVisibleProfile;
            hasClickedProfile = true;
          }"
        >
          <img :src="profilePicture" class="profile-pic" />
          <div class="profile-dropdown-menu">
            <router-link to="/profile" class="dropdown-item">My Profile</router-link>
            <button @click="logout" class="dropdown-item logout">Logout</button>
          </div>
        </div>
      </div>

      <div class="hamburger-icon" @click="toggleHamburgerMenu">&#9776;</div>

      <div v-if="isHamburgerOpen" class="mobile-menu">
        <router-link to="/home" class="mobile-menu-item" @click="toggleHamburgerMenu">Home</router-link>
        <router-link to="/inventory" class="mobile-menu-item" @click="toggleHamburgerMenu">Inventory</router-link>
        <router-link to="/marketplace" class="mobile-menu-item" @click="toggleHamburgerMenu">Marketplace</router-link>
        <router-link to="/myMarketplaceListings" class="mobile-menu-item" @click="toggleHamburgerMenu">My Listings</router-link>
        <router-link to="/createCard" class="mobile-menu-item" @click="toggleHamburgerMenu">Create Card</router-link>
        <router-link v-if="isAdmin" to="/admin" class="mobile-menu-item" @click="toggleHamburgerMenu">Admin Panel</router-link>
        <router-link to="/profile" class="mobile-menu-item" @click="toggleHamburgerMenu">My Profile</router-link>
        <button class="mobile-menu-item logout" @click="() => { toggleHamburgerMenu(); logout(); }">Logout</button>
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

.desktop-nav-left {
  display: flex;
  align-items: center;
  height: 100%;
}

.desktop-nav-left .nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  padding: 0 20px;
  display: flex;
  align-items: center;
  height: 100%;
  transition: background 0.3s;
}

.desktop-nav-left .nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
}

.desktop-nav-left .active {
  background: #4992f8;
  color: white;
}

.dropdown-wrapper {
  position: relative;
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
  font-size: 1.1rem;
  border-radius: 5px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
  z-index: 100;
  display: flex;
  flex-direction: column;
  padding: 10px;
  width: 100%;
  color: white;
}

.dropdown-wrapper:hover {
  background: rgba(255, 255, 255, 0.2);
}

.nav-right {
  display: flex;
  padding: 0px;
  height: 100%;
}

.desktop-nav-right {
  display: flex;
  align-items: center;
  height: 100%;
}

.desktop-nav-right .nav-link {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  padding: 0 20px;
  display: flex;
  align-items: center;
  height: 100%;
  transition: background 0.3s;
}

.desktop-nav-right .nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
}

.desktop-nav-right .active {
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
  width: 200%;
  color: white;
}

.profile-dropdown:hover .profile-dropdown-menu {
  display: flex;
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
}

.item {
  padding: 0;
  max-width: 35px;
  height: 30px;
}

.user-balance {
  display: flex;
  align-items: center;
  padding: 0 12px;
  font-weight: bold;
  font-size: 1.1rem;
  color: white;
  text-decoration: none;
}

.user-balance:hover {
  color: white;
  background: rgba(255, 255, 255, 0.2);
}

.currency-icon {
  width: 20px;
  height: 20px;
  margin-right: 6px;
  border-radius: 50%;
  object-fit: cover;
}

.logout {
  background-color: red;
}

.logout:hover {
  background-color: rgb(255, 101, 101);
}

.hamburger-icon {
  display: none;
}

@media (max-width: 895px) {
  .desktop-nav-left {
    display: none;
  }
  
  .desktop-nav-right{
    display: none;
  }

  .hamburger-icon {
    display: block;
    font-size: 2rem;
    color: white;
    padding-right: 20px;
    padding-top: 3px;
    padding-left: 20px;
    cursor: pointer;
  }

  .hamburger-icon:hover {
    color: white;
    background: rgba(255, 255, 255, 0.2);
  }

  .mobile-menu {
    position: absolute;
    top: 60px;
    right: 0;
    width: 100%;
    background: #3366af;
    display: flex;
    flex-direction: column;
    z-index: 999;
  }

  .mobile-menu-item {
    color: white;
    text-decoration: none;
    padding: 1rem;
    font-size: 1.1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    background: #3366af;
    text-align: left;
  }

  .mobile-menu-item:hover {
    background: rgba(255, 255, 255, 0.2);
  }

  .mobile-dropdown {
    display: flex;
    flex-direction: column;
  }

  .mobile-dropdown-title {
    color: white;
    font-weight: bold;
    padding: 1rem;
    background: #3366af;
  }

  .logout {
    background-color: red;
  }

  .logout:hover {
    background-color: rgb(255, 101, 101);
  }
}
</style>
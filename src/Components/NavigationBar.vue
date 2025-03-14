<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useUserStore } from '@/Store/UserStore';
import CuboCard from '@/assets/icons/CubocardLogo.png';
import ShoppingCart from '@/assets/icons/shoppingcart.png';

const userStore = useUserStore();
const baseUrl = "http://localhost:8000/";

const user = computed(() => userStore.user);
const isAdmin = computed(() => userStore.user?.role === 'admin');
const dropdownVisible = ref(false);
const profilePicture = ref('/images/profile.png'); // Default image

// Function to get profile picture URL correctly
const getProfilePicture = () => {
  if (userStore.user?.profile_picture_url) {
    let picUrl = userStore.user.profile_picture_url.startsWith("http")
      ? userStore.user.profile_picture_url
      : `${baseUrl}${userStore.user.profile_picture_url}`; // Fix template literal

    console.log("Updated profile picture URL:", picUrl); // Debugging
    return picUrl;
  }
  return "/images/profile.png";
};

// Watch for profile picture changes and update it
watch(() => userStore.user?.profile_picture_url, (newVal) => {
  console.log("Profile picture changed:", newVal); // Debugging
  profilePicture.value = getProfilePicture();
}, { immediate: true });

const toggleDropdown = () => {
  dropdownVisible.value = !dropdownVisible.value;
};

const logout = () => {
  userStore.logout();
  window.location.reload();
};

// Event listener for profile updates
const updateProfilePictureHandler = (newProfilePic) => {
  console.log("Profile picture updated via event:", newProfilePic); // Debugging
  userStore.user.profile_picture_url = newProfilePic;
  profilePicture.value = getProfilePicture(); // Update immediately
};

onMounted(() => {
  window.addEventListener('profileUpdated', (event) => {
    if (event.detail) {
      updateProfilePictureHandler(event.detail);
    }
  });

  console.log("Initial profile picture:", profilePicture.value); // Debugging
});
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

      <router-link to="/shoppingcart" class="icon-link" active-class="active">
        <img class="item" :src="ShoppingCart" />
      </router-link>

      <div class="profile-dropdown profile-container nav-link" active-class="active">
        <img
          :src="profilePicture"
          class="profile-pic"
          alt="Profile Picture"
          @click="toggleDropdown"
        />
        <div v-if="dropdownVisible" class="dropdown-menu">
          <router-link to="/profile" class="dropdown-item" @click="toggleDropdown">My Profile</router-link>
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
</style>
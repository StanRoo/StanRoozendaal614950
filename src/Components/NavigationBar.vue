<script setup>
import CuboCard from '@/assets/icons/CubocardLogo.png'
import ShoppingCart from '@/assets/icons/shoppingcart.png'
import Profile from '@/assets/icons/profile.png'
</script>

<template>
  <nav class="navbar sticky-top">
    <div class="nav-left">
      <router-link to="/home" class="nav-link logo"><img class="logoImage" :src="CuboCard" /></router-link>
      <router-link to="/home" class="nav-link" active-class="active">Home</router-link>
      <router-link to="/cards" class="nav-link" active-class="active">Cards</router-link>
    </div>

    <div class="nav-right">
      <router-link to="/shoppingcart" class="icon-link" active-class="active">
        <img class="item" :src="ShoppingCart" />
      </router-link>
      <router-link to="/profile" class="profile-container" active-class="active">
        <img
          v-if="profilePicture"
          :src="profilePicture + '?t=' + new Date().getTime()"
          alt="Profile"
          class="profile-pic"
        />
        <img v-else src="@/assets/icons/profile.png" alt="Default Avatar" class="profile-pic" />
      </router-link>
    </div>
  </nav>
</template>

<script>
export default {
  props: ["profilePicture"],
};
</script>

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
  transition: 0.3s;
}

.profile-pic:hover {
  transform: scale(1.1);
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
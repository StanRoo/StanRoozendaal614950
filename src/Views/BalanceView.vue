<template>
 <header>
    <img class="banner" :src="BalanceBanner" alt="Inventory Banner"/>
  </header>
</template>

<script setup>
import BalanceBanner from '@/assets/images/Balance_Banner.png';
</script>

<style scoped>
.banner {
  width: 100%;
  margin-top: 10px;
}
</style>
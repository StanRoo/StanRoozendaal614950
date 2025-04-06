<template>
  <header>
     <img class="banner" :src="MarketplaceBanner" alt="Inventory Banner"/>
   </header>
 </template>
 
 <script setup>
 import MarketplaceBanner from '@/assets/images/Marketplace_Banner.png';
 </script>
 
 <style scoped>
 .banner {
   width: 100%;
   margin-top: 10px;
 }
 </style>
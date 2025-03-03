<script setup>
 import CuboCardBanner from '@/assets/icons/CuboCardBanner.png'
</script>

<template>
 <main>
    <img class="banner" :src="CuboCardBanner" alt="CuboCard Banner"/>
    <div class="container">
      <h1>Welcome to CuboCard</h1>
      <div class="HomeText">
       Discover the ultimate marketplace for Pokémon card enthusiasts! At CuboCard, we bring together collectors, traders, and fans to buy, sell, and exchange their favorite Pokémon cards. Whether you're hunting for rare treasures or looking to share your duplicates, our platform is designed to make trading fun, secure, and hassle-free.
        <br>
        <br>
       Join the CuboCard community today and take your Pokémon collection to the next level!
      </div>
    </div>
 </main>
</template>

<style scoped>
  html, body {
    margin: 0;
    padding: 0;
   overflow-x: hidden;
   overflow-y: hidden;
   width: 100%;
   height: 100%;
  }

  header {
    line-height: 1.5;
  }

  main{
    flex: 1;
  }

  .container {
    width: 100%;
  }

  .banner {
   width: 100%;
   left: 0;
   margin-top: -40px;
  }

  h1 {
   text-align: center;
   width: 100%;
   margin-top: 2vw;
  }

  .HomeText {
    text-align: center;
   margin-top: 1vw;
    width: 100%;
  }
</style>
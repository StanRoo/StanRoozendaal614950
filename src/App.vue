<script setup>
  import NavigationBar from '@/Components/NavigationBar.vue';
</script>

<template>
  <div id="app">
    <header v-if="showHeaderAndFooter">
      <NavigationBar :profilePicture="profilePicture"/>
    </header>

    <main>
      <RouterView @profileUpdated="updateProfilePicture"/>
    </main>

    <footer v-if="showHeaderAndFooter">
      <div class="footerText">© 2024 Copyright: CuboCard</div>
    </footer>
  </div>
</template>

<script>
  export default {
    name: "App",
    computed: {
      showHeaderAndFooter() {
        const hiddenRoutes = ["Login", "ForgotPassword", "CreateAccount"];
        return !hiddenRoutes.includes(this.$route.name);
      },
    },
    data() {
      return {
        profilePicture: localStorage.getItem("profile_picture") || null
      };
    },
    methods: {
      updateProfilePicture(newPicture) {
        this.profilePicture = newPicture;
        localStorage.setItem("profile_picture", newPicture); 
      }
    },
    components: { NavigationBar }
  };
</script>

<style>
  html, body {
    height: 100%;
    margin: 0;
  }

  header {
    background-color: #f8f9fa;
  }

  main {
    padding-top: 50px; 
  }

  footer {
    margin-top: auto;
    background-color: #3366af;
    width: 100%;
    height: 40px;
    text-align: center;
    color: white;
    line-height: 2.5;
  }
</style>
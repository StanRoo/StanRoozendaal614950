<script setup>
import { ref } from 'vue';
import NavigationBar from '@/Components/NavigationBar.vue';

const profilePicture = ref(localStorage.getItem("profile_picture") || null);

function updateProfilePicture(newPicture) {
  profilePicture.value = newPicture;
  localStorage.setItem("profile_picture", newPicture); 
}
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
  created() {
    const token = localStorage.getItem('token');

    if (!token) {
      this.$router.push('/');
    } else {
      this.validateToken(token);
    }
  },
  data() {
    return {
      profilePicture: localStorage.getItem("profile_picture") || null
    };
  },
  methods: {
    validateToken(token) {
      const decoded = this.decodeToken(token);

      if (decoded && decoded.exp > Date.now() / 1000) {

      } else {
        localStorage.removeItem('token');
        this.$router.push('/');
      }
    },
    decodeToken(token) {
      return jwt_decode(token);
    },
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
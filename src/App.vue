<script setup>
import { ref, watch } from 'vue';
import { useUserStore } from '@/Store/UserStore'; 
import NavigationBar from '@/Components/NavigationBar.vue';

const userStore = useUserStore();
const profilePicture = ref(userStore.user.profile_picture_url || null);

watch(() => userStore.user.profile_picture_url, (newPicture) => {
  profilePicture.value = newPicture;
});

function updateProfilePicture(newPicture) {
  userStore.updateProfilePicture(newPicture);
  profilePicture.value = newPicture;
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
  methods: {
    validateToken(token) {
      const decoded = this.decodeToken(token);

      if (decoded && decoded.exp > Date.now() / 1000) {
        console.log("Token is valid");
      } else {
        console.log("Token expired or invalid. Logging out...");
        localStorage.removeItem("token");
        this.$router.push("/");
      }
    },
    decodeToken(token) {
      try {
        return jwtDecode(token);
      } catch (error) {
        console.error("Invalid token:", error);
        return null;
      }
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
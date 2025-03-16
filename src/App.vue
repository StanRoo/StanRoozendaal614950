<script setup>
import { ref, computed, watchEffect, onMounted } from "vue";
import { useUserStore } from "@/Store/UserStore";
import { useRoute, useRouter } from "vue-router";
import NavigationBar from "@/Components/NavigationBar.vue";

const userStore = useUserStore();
const route = useRoute();
const router = useRouter();

const profilePicture = computed(() => userStore.user?.profile_picture_url || "/images/profile.png");

function updateProfilePicture(newPicture) {
  userStore.updateProfilePicture(newPicture);
}

const showHeaderAndFooter = computed(() => {
  const hiddenRoutes = ["Login", "ForgotPassword", "CreateAccount"];
  return !hiddenRoutes.includes(route.name);
});

onMounted(() => {
  userStore.restoreSession();

  watchEffect(() => {
    if (!userStore.isAuthenticated) {
      router.push("/");
    }
  });
});
</script>

<template>
  <div id="app">
    <header v-if="showHeaderAndFooter">
      <NavigationBar :profilePicture="profilePicture" />
    </header>

    <main>
      <RouterView @profileUpdated="updateProfilePicture" />
    </main>

    <footer v-if="showHeaderAndFooter">
      <div class="footerText">© 2024 Copyright: CuboCard</div>
    </footer>
  </div>
</template>

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
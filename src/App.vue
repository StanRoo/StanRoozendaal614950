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
    const publicRoutes = ["Login", "ForgotPassword", "CreateAccount"];
    if (!userStore.isAuthenticated && !publicRoutes.includes(route.name)) {
      router.push("/");
    }
  });
});
</script>

<template>
  <div id="app">
    <header v-if="showHeaderAndFooter" :class="{ hidden: !showHeaderAndFooter }">
      <NavigationBar :profilePicture="profilePicture" />
    </header>

    <main :class="{ 'no-header': !showHeaderAndFooter, 'no-footer': !showHeaderAndFooter }">
      <RouterView @profileUpdated="updateProfilePicture" />
    </main>

    <footer v-if="showHeaderAndFooter" :class="{ hidden: !showHeaderAndFooter }">
      <div class="footerText">© 2024 Copyright: CuboCard</div>
    </footer>
  </div>
</template>

<style>
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
  overflow-x: hidden;
  display: flex;
  flex-direction: column;
}

.hidden {
  height: 0 !important;
  overflow: hidden;
  padding: 0 !important;
  margin: 0 !important;
  display: none !important;
}

header {
  background-color: #f8f9fa;
  transition: height 0.3s ease;
}

main {
  flex: 1;
  padding-top: 50px;
  padding-bottom: 40px;
}

main.no-header {
  padding-top: 0 !important;
}

main.no-footer {
  padding-bottom: 0 !important;
}

footer {
  background-color: #3366af;
  width: 100%;
  height: 40px;
  text-align: center;
  color: white;
  line-height: 2.5;
  transition: height 0.3s ease;
}
</style>
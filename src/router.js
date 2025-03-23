import { createWebHistory, createRouter } from 'vue-router'

import HomeView from '@/Views/HomeView.vue'
import CardsView from '@/Views/CardsView.vue'
import BalanceView from '@/Views/BalanceView.vue'
import ProfileView from '@/Views/ProfileView.vue'
import LoginView from '@/Views/LoginView.vue'
import ForgotPasswordView from '@/Views/ForgotPasswordView.vue'
import CreateAccountView from '@/Views/CreateAccountView.vue'
import AdminView from '@/Views/AdminView.vue'

const routes = [
  { path: '/', name: 'Login', component: LoginView },
  { path: '/forgotPassword', name: 'ForgotPassword', component: ForgotPasswordView },
  { path: '/createAccount', name: 'CreateAccount', component: CreateAccountView },
  { path: '/home', name: 'Home', component: HomeView },
  { path: '/cards', name: 'Cards', component: CardsView },
  { path: '/balance', name: 'Balance', component: BalanceView },
  { path: '/profile', name: 'Profile', component: ProfileView },
  {
    path: "/admin",
    component: AdminView,
    beforeEnter: (to, from, next) => {
      const userRole = localStorage.getItem("role");
      if (userRole !== "admin") {
        alert("Access Denied: Admins only!");
        next("/home");
      } else {
        next();
      }
    }
  },
]

const router = createRouter({
  history: createWebHistory(), 
  routes
});

export default router
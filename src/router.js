import { createWebHistory, createRouter } from 'vue-router'

import HomeView from '@/Views/HomeView.vue'
import CardsView from '@/Views/CardsView.vue'
import ShoppingCartView from '@/Views/ShoppingCartView.vue'
import ProfileView from '@/Views/ProfileView.vue'
import LoginView from '@/Views/LoginView.vue'
import ForgotPasswordView from '@/Views/ForgotPasswordView.vue'
import CreateAccountView from '@/Views/CreateAccountView.vue'

const routes = [
  { path: '/', name: 'Login', component: LoginView },
  { path: '/forgotPassword', name: 'ForgotPassword', component: ForgotPasswordView },
  { path: '/createAccount', name: 'CreateAccount', component: CreateAccountView },
  { path: '/home', name: 'Home', component: HomeView },
  { path: '/cards', name: 'Cards', component: CardsView },
  { path: '/shoppingcart', name: 'ShoppingCart', component: ShoppingCartView },
  { path: '/profile', name: 'Profile', component: ProfileView },
]

const router = createRouter({
  history: createWebHistory(), 
  routes
});

export default router
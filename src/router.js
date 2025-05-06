import { createWebHistory, createRouter } from 'vue-router'

import HomeView from '@/Views/HomeView.vue'
import InventoryView from '@/Views/InventoryView.vue'
import MarketplaceView from '@/Views/MarketplaceView.vue'
import MarketplaceDetailView from '@/Views/MarketplaceDetailView.vue';
import MarketplaceListingsByUserView from '@/Views/MarketplaceListingsByUserView.vue';
import CreateCardView from '@/Views/CreateCardView.vue'
import BalanceView from '@/Views/BalanceView.vue'
import ProfileView from '@/Views/ProfileView.vue'
import LoginView from '@/Views/LoginView.vue'
import ForgotPasswordView from '@/Views/ForgotPasswordView.vue'
import CreateAccountView from '@/Views/CreateAccountView.vue'
import AdminView from '@/Views/AdminView.vue'
import CardDetailPageView from '@/Views/CardDetailPageView.vue';
import ResetPasswordView from '@/Views/ResetPasswordView.vue';
import ErrorView from '@/Views/ErrorView.vue';

const routes = [
  { path: '/', name: 'Login', component: LoginView },
  { path: '/forgotPassword', name: 'ForgotPassword', component: ForgotPasswordView },
  { path: '/createAccount', name: 'CreateAccount', component: CreateAccountView },
  { path: '/home', name: 'Home', component: HomeView },
  { path: '/inventory', name: 'Inventory', component: InventoryView },
  { path: '/marketplace', name: 'Marketplace', component: MarketplaceView },
  { path: '/createCard', name: 'CreateCard', component: CreateCardView },
  { path: '/balance', name: 'Balance', component: BalanceView },
  { path: '/profile', name: 'Profile', component: ProfileView },
  { path: '/card/:id', name: 'CardDetail', component: CardDetailPageView, props: true },
  { path: '/marketplace/detail/:id', name: 'MarketplaceDetail', component: MarketplaceDetailView, props: true },
  { path: '/myMarketplaceListings', name: 'MyMarketplaceListings', component: MarketplaceListingsByUserView },
  { path: '/reset-password', name: 'ResetPassword', component: ResetPasswordView },
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
  {
    path: '/error',
    name: 'ErrorView',
    component: ErrorView,
    props: route => ({
      status: route.query.status || 500,
      message: route.query.message || 'Something went wrong.',
    }),
  },
  {
    path: '/:catchAll(.*)',
    name: 'NotFound',
    component: ErrorView,
    props: {
      status: 404,
      message: "The page you're looking for doesn't exist.",
    },
  },
]

const router = createRouter({
  history: createWebHistory(), 
  routes
});

export default router
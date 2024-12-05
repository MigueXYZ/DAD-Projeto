import HomeComponent from '@/components/HomeComponent.vue'
import LaravelTester from '@/components/LaravelTester.vue'
import WebSocketTester from '@/components/WebSocketTester.vue'
import { createRouter, createWebHistory } from 'vue-router'
import LoginView from "@/views/LoginView.vue";
import DashboardView from "@/views/DashboardView.vue";
import SelectSizeView from "@/views/SelectSizeView.vue";
import GameView from "@/views/GameView.vue";
import {useAuthStore} from "@/stores/auth.js";
import {useGameStore} from "@/stores/game.js";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: LoginView
    },
    { path: '/login', component: LoginView }, // Login page route
    { path: '/dashboard', component: DashboardView, name: 'dashboard' }, // Dashboard page route
    { path: '/size', component: SelectSizeView, name: 'size' },
    { path: '/game', component: GameView, name: 'game' },
    {
      path: '/testers',
      children: [
        {
          path: 'laravel',
          component: LaravelTester
        },
        {
          path: 'websocket',
          component: WebSocketTester
        }
      ]
    }
  ]
})

// To avoid calling restoreToken multiple times
let handlingFirstRoute = true;

router.beforeEach(async (to, from, next) => {
  const storeAuth = useAuthStore();
  const gameStore = useGameStore();

  if (handlingFirstRoute) {
    handlingFirstRoute = false; // Ensure the token restoration is done once
    await storeAuth.restoreToken();
  }

  if(to.name === 'dashboard'){
    await gameStore.deleteGame();
    console.log("game state cleared");
  }

  // Check if user is logged in and trying to access protected routes
  const protectedRoutes = ['Scoreboard']; // Example routes that need authentication

  if (protectedRoutes.includes(to.name) && !storeAuth.isLoggedIn) {
    // If the user is not logged in, redirect to login page
    next({ name: 'login' });
  } else {
    // Otherwise, allow the navigation
    next();
  }
});


export default router

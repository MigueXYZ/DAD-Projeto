<template>
  <header class="bg-blue-600 w-full py-4 text-white text-center">
    <div class="lg:flex lg:justify-between mx-5">
      <div class="flex-initial">
        <h1 class="text-3xl font-semibold">
          <button
              @click="home"
              class="text-3xl font-semibold"
          >
            Memory Game
          </button>
        </h1>
      </div>
      <!-- Play Game Card -->

    </div>
  </header>
  <div class="!p-0" id="app">
    <router-view />
    <GlobalAlertDialog ref="alert-dialog"></GlobalAlertDialog>
  </div>
  <footer class="bg-blue-600 w-full py-4 text-white text-center">
    <p>&copy; 2024 Memory Game. All rights reserved.</p>
  </footer>
</template>

<script setup>
import { useTemplateRef,provide } from 'vue'
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import GlobalAlertDialog from '@/components/GlobalAlertDialog.vue'
import {useGameStore} from "@/stores/game.js";

const authStore = useAuthStore(); // Access the auth store
const router = useRouter(); // Access the router instance
const gameStore = useGameStore(); // Access the game store

// Navigate to dashboard if the user is logged in
const home = () => {
  gameStore.clearGame();
  router.push('/dashboard');
};

const alertDialog = useTemplateRef('alert-dialog')
provide('alertDialog', alertDialog)
</script>


<style>
/* Global styles can go here */
</style>

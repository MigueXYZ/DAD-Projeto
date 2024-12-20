<template>
  <header class="bg-blue-600 w-full py-4 text-white">
  <div class="flex justify-between items-center mx-4">
    <!-- Left Side -->
    <div class="flex items-center space-x-4">
      <h1 class="text-xl lg:text-3xl font-semibold">
        <button v-if="!authStore.isAdmin"
          @click="home"
          class="text-xl lg:text-3xl font-semibold"
        >
          Memory Game
        </button>
        <button v-else
          @click="home"
          class="text-l lg:text-2xl font-semibold"
        >
          Memory Game Administration
        </button>
      </h1>
      
    </div>
    
    <!-- Right Side -->
    <div>
      <BrainCoinsNav v-if="gameStore.game != null"></BrainCoinsNav>
      <h1 v-else @click="profile" > {{authStore.userFirstLastName}}</h1>
    </div>
  </div>
</header>


  <div class="!p-0" id="app">
    <router-view />
    <GlobalAlertDialog ref="alert-dialog"></GlobalAlertDialog>
    <Toaster
      v-if="authStore.notifications==true"
    />
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
import BrainCoinsNav from './components/BrainCoinsNav.vue';
import Toaster from './components/ui/toast/Toaster.vue';
import { useToast } from '@/components/ui/toast/use-toast';
import { ToastAction } from '@/components/ui/toast';


const authStore = useAuthStore(); // Access the auth store
const router = useRouter(); // Access the router instance
const gameStore = useGameStore(); // Access the game store
const { toast } = useToast(); // Access the toast function

// Navigate to dashboard if the user is logged in
const home = () => {
  gameStore.clearGame();
  router.push('/dashboard');
};

// Navigate to profile page
const profile = () => {
  if (authStore.isLoggedIn){
    router.push('/profile');
  }else{
    toast({
      description: 'You must be logged in to access your profile.',
    })
  }
};

const alertDialog = useTemplateRef('alert-dialog')
provide('alertDialog', alertDialog)




</script>


<style>
/* Global styles can go here */
</style>

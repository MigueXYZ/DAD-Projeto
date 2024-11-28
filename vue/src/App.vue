<script setup>
import Toaster from './components/ui/toast/Toaster.vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';  // Import useRouter from vue-router

const router = useRouter();  // Get the router instance using useRouter

function home() {
  const authStore = useAuthStore();
  if (authStore.isLoggedIn) {
    router.push('/dashboard');  // Use router.push() to navigate
  }
}
</script>

<template>
  <Toaster/>
  <div class="min-h-screen bg-gray-50">
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
      </div>
    </header>

    <main class=" ">
      <router-view/>
      <footer class="bg-blue-600 w-full py-4 text-white text-center">
        <p>&copy; 2024 Memory Game. All rights reserved.</p>
      </footer>
    </main>


  </div>
</template>

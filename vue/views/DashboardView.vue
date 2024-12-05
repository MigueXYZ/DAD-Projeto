<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Header Section -->


    <!-- Main Content Section -->
    <main class="flex-1 w-full max-w-4xl px-6 py-8 mx-auto">
      <div class="grid grid-cols-1 gap-6">
        <!-- Brain Coins Balance Component -->

        <div class="bg-white p-6 mt-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
          <BrainCoinsBalance
              v-if="authStore.isLoggedIn"
          />
          <div v-else class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 flex items-center justify-center">
            <p class="text-gray-700 text-center">Login to unlock all features</p>
          </div>


          <div class="mt-4 grid grid-cols-1">
            <button
                @click="startGame"
                class="w-auto lg:w-full py-3 bg-blue-700 hover:bg-blue-600 text-white rounded-md  transition duration-200 my-2"
            >
              Play SinglePlayer
            </button>
            <!-- TODO
              <button
                @click="startGame"
                class="w-auto lg:w-full py-3  bg-blue-700 text-white rounded-md hover:bg-blue-600 transition duration-200"
              >
                Join Lobby
              </button>
              -->
          </div>

        </div>
        <!-- Logout Card -->
        <div class="transition w-full duration-300 flex justify-end">
          <button
              @click="logout"
              :class="authStore.isLoggedIn ? 'py-3 px-10 w-full sm:w-auto bg-red-500 hover:bg-red-700 text-white rounded-md transition duration-200' : 'py-3 px-10 w-full sm:w-auto bg-blue-700 hover:bg-blue-600 text-white rounded-md transition duration-200'"

          >
            {{ authStore.isLoggedIn ? 'Logout' : 'Sign In' }}
          </button>
        </div>
      </div>
    </main>

    <!-- Footer Section -->
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth'; // Import authentication store
import { useRouter } from 'vue-router';
import axios from 'axios';

import BrainCoinsBalance from '@/components/BrainCoinsBalance.vue'; // Import components
import PlayGame from '@/components/PlayGame.vue';
import SizeSelector from '@/components/SizeSelector.vue';


const user = ref(null); // Reactive variable for the authenticated user
const boards = ref([]); // Reactive variable for board data
const router = useRouter(); // Access the router instance
const authStore = useAuthStore(); // Access authentication store

// Check authentication status
const checkAuth = () => {
  if (authStore.user) {
    user.value = authStore.user;
  } else {
    router.push('/login'); // Redirect to login if not authenticated
  }
};

// Fetch board data from API
const load = async () => {
  try {
    // const response = await axios.get('http://api-dad-group-5-172.22.21.101.sslip.io/api/boards');
    const response = await axios.get('http://localhost:8081/api/boards');
    boards.value = response.data; // Assign response data
    console.log('Boards:', boards.value); // Log fetched boards
  } catch (error) {
    console.error('Error fetching boards:', error.message);
    console.error('Error details:', error.response || error.config);
  }
};

// Start the game by navigating to the game page
const startGame = () => {
  router.push('/size'); // Navigate to size selection page
};

// Navigate to the dashboard
const home = () => {
  router.push('dashboard');
};

// Logout the user
const logout = async () => {
  try {
    if(authStore.isLoggedIn){
      await authStore.logout(); // Perform logout using the store
    }
    router.push('/login'); // Redirect to login page
  } catch (error) {
    console.error('Logout error:', error);
    alert('An error occurred while logging out. Please try again.');
  }
};

// Run on component mount
onMounted(() => {
  checkAuth();
  load();
});

</script>

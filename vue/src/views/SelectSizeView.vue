<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Header Section -->


    <!-- Main Content Section -->
    <main class="flex-1 w-full max-w-4xl px-6 py-8 mx-auto">
      <div class="bg-white p-6 my-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
        <h1 class="text-black text-xl font-semibold" >Select Board Dimentions:</h1>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 my-8">
          <!-- Fallback message if boards array is empty -->
          <div v-if="boards.length === 0">Loading boards...</div>

          <!-- Loop through boards and render each SizeSelector -->
          <div v-for="board in boards" :key="board.id">
            <SizeSelector
                :board="board"
                v-if="showBoard(board.id)"
            />
          </div>

          <div class="text-center bold" v-if="!authStore.isLoggedIn">Login to unlock bigger Boards!</div>
        </div>

      </div>
    </main>

    <!-- Footer Section -->
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useGameStore } from '@/stores/game'; // Import the game store
import { storeToRefs } from 'pinia';
import { useAuthStore } from '@/stores/auth.js';
import axios from 'axios';
import { useRouter } from 'vue-router';
import SizeSelector from '@/components/SizeSelector.vue';


const boards = ref([]); // Reactive variable to hold board data
const router = useRouter(); // Access the router instance
const gameStore = useGameStore(); // Access the game store
const { games } = storeToRefs(gameStore); // Reactive reference to games
const authStore = useAuthStore(); // Access the auth store



//make a function that return false if user has 0 brain coins and board id is not 1
const showBoard = (boardId) => {
  if(!authStore.isLoggedIn && boardId !== 1){
    return false;
  }
  return true;
}

// Fetch boards from API
const load = async () => {
  try {
    const response = await axios.get('http://localhost:8081/api/boards');
    boards.value = response.data.data; // Assign fetched data
    console.log('Boards:', boards.value); // Check fetched data in console
  } catch (error) {
    console.error('Error fetching boards:', error.message);
    console.error('Error details:', error.response || error.config);
  }
};

// Format date in 'YYYY-MM-DD HH:MM:SS'
const formatDate = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0'); // Month with 2 digits
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};

// Test game creation
const testCreateGame = async () => {
  const gameData = {
    type: 'S', // Replace with the desired type
    status: 'PL', // Initial status
    began_at: formatDate(new Date()), // Format the current date
    board_id: boards.value[0]?.id || 1, // Use the first board ID or default to 1
    custom: null, // Additional info (optional)
  };

  const success = await gameStore.createGame(gameData); // Call createGame method
  if (success) {
    console.log('Game created successfully:', games.value);
  } else {
    console.error('Error creating the game.');
  }
};

// Start the game
const startGame = () => {
  testCreateGame(); // Test creating a game before starting
  router.push('/size'); // Navigate to the size selection page
};

// Navigate to the home/dashboard
const home = () => {
  router.push('dashboard');
};

// Load boards on component mount
onMounted(() => {
  load();
});


</script>
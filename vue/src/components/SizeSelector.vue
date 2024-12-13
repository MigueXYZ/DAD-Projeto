<template>

  <p v-if="board.custom">Custom Size: {{ board.custom }}</p>
  <button @click="startGame"
          :class=" checkBrainCoins(board.id)===true  ? 'border w-full rounded-lg shadow-md p-8 bg-blue-600 text-white font-bold transition duration-200 text-xl hover:bg-blue-700'
                                  : 'border w-full rounded-lg shadow-md p-8 bg-gray-400 text-white font-bold transition duration-200 text-xl hover:bg-blue-700'">
    {{ board.board_cols }} x {{ board.board_rows }}
  </button>

</template>
<script setup>
import { useGameStore } from '@/stores/game'; // Import the game store
import { useRouter } from 'vue-router';
import { reactive } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import { useToast } from '@/components/ui/toast/use-toast';
import { ToastAction } from '@/components/ui/toast';


// Define props using defineProps
const props = defineProps({
  board: {
    type: Object,
    required: true, // Ensure the prop is passed
  },
});


const router = useRouter(); // Access the router instance
const gameStore = useGameStore(); // Access the game store
const authStore = useAuthStore();
const { toast } = useToast();


const checkBrainCoins = (boardId) => {
  if(!authStore.isLoggedIn && boardId !== 1){
    return false;
  }
  else if(authStore.isLoggedIn && authStore.user.brain_coins_balance === 0 && boardId !== 1){
    return false;
  }
  return true;
}

// Reactive state for the game data
const state = reactive({
  gameData: {
    type: 'S', // Replace with desired type
    status: 'PL', // Initial status
    began_at: '', // Placeholder for formatted date
    board_id: props.board.id, // Use the ID of the passed board prop
    custom: null, // Additional information (optional)
  },
});

// Function to format date as 'YYYY-MM-DD HH:MM:SS'
const formatDate = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0'); // Adjust month to 2 digits
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
};

// Start the game by creating a new game and navigating
const startGame = async () => {
  if(checkBrainCoins(props.board.id)){
    state.gameData.began_at = formatDate(new Date()); // Set the formatted current date

    const success = await gameStore.createGame(state.gameData);

    if(props.board.id !== 1){
      // Deduct 1 Brain Coin
      await axios.patch(`/users/me`, {
        brain_coins_balance: authStore.user.brain_coins_balance - 1,
      });
      const coin = authStore.user.brain_coins_balance-1;
      const response = await axios.get(`/users/me`);
      authStore.setUser(response.data.data);
      toast({
        description: 'Teve 1 brain coin descontado, Novo saldo: ' + coin,
      })


    }

    if (success) {
      await router.push('/game'); // Navigate to the game page
    } else {
      console.error('Failed to create game', success);
    }
  }
};
</script>

<template>

      <p v-if="board.custom">Custom Size: {{ board.custom }}</p>
      <button @click="startGame"
        class="border w-full rounded-lg shadow-md p-8 bg-blue-600 text-white font-bold transition duration-200 text-xl hover:bg-blue-700">
  {{ board.board_cols }} x {{ board.board_rows }}
</button>

  </template>
<script>
import { useGameStore } from '@/stores/game'; // Import the game store
import { useRouter } from 'vue-router';
import { computed } from 'vue';

export default {
  props: {
    board: {
      type: Object,
      required: true, // Ensure the prop is passed
    },
  },
  setup(props) {
    const router = useRouter(); // Access the router instance
    const gameStore = useGameStore(); // Access the game store


    // Start the game by creating a new game and navigating
    const startGame = async () => {
      const gameData = {
        type: 'S', // Replace with desired type
        status: 'PL', // Initial status
        began_at: gameStore.getFormattedDate(), // Format the current date
        board_id: props.board.id, // Use the ID of the passed board prop
        custom: null, // Additional information (optional)
      };

      const success = await gameStore.createGame(gameData);

      if (success) {
        router.push('/game'); // Navigate to the game page
      } else {
        console.error('Failed to create game' + success);
      }
    };

    return {
      startGame,
    };
  },
};
</script>

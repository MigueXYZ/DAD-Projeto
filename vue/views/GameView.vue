<template>
  <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4">
    <h1 class="text-2xl font-bold mb-4 text-center">Game Board</h1>
    <div @click="winpopUp">TESTE</div>
    <!-- Responsive grid for the game board -->
    <div
      v-if="board.board_cols > 0"
      :style="`grid-template-columns: repeat(${board.board_cols}, minmax(2rem, 1fr));`"
      class="grid gap-2 bg-white p-4 rounded shadow w-full max-w-lg"
    >
      <!-- Render each tile dynamically -->
      <div
        v-for="(tile, index) in tiles"
        :key="index"
        class="w-full h-auto bg-gray-200 border border-gray-300 flex items-center justify-center text-sm sm:text-base cursor-pointer"
        @click="flipTile(tile)"
      >
        <!-- Show the tile's image -->
        <img
          :src="getTileImage(tile)"
          :alt="'Tile ' + tile.id"
          class="w-full h-auto object-cover"
        />
      </div>
    </div>

    <!-- Display a message if no board data is available -->
    <div v-else class="text-gray-500 text-center">Loading board...</div>
  </div>
</template>

<script setup>
import { useGameStore } from '@/stores/game'; // Import the game store
import { ref, computed, onMounted, reactive, inject } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import  router  from '@/router/index'

const authStore = useAuthStore();
const gameStore = useGameStore();
    const board = reactive({
      board_cols: 0, // Default value for columns
      board_rows: 0, // Default value for rows
    });

    const tilesFlipped = ref(0); // Reactive counter to track flipped tiles
    const flippedTiles = ref([]); // To store the currently flipped tiles
    const tiles = ref([]); // Reactive tiles array

    const alertDialog = inject('alertDialog'); // Inject alert dialog
    const shuffleArray = (array) => array.sort(() => Math.random() - 0.5);
    // Home function to navigate to the dashboard
    const home = () => {
      gameStore.clearGame();
      router.push('/dashboard');
    };

    const playAgain = () => {
      router.push('/size')

    }
    // Function to show win popup
    const winpopUp = () => {
      console.log(alertDialog.value)
      alertDialog.value.open(
        home,playAgain,
        'Parabens, venceu o jogo!', 'Voltar a Dashboard', 'Jogar de novo', `O seu jogo durou: ${gameStore.game.total_time} segundos`
      );
    };

    // Function to flip a tile
    const flipTile = (tile) => {
      // If the tile is already flipped or two tiles are flipped, prevent flipping
      if (tile.isFlipped || tilesFlipped.value >= 2) return;

      tile.isFlipped = true; // Flip the tile
      flippedTiles.value.push(tile); // Add this tile to the flippedTiles array
      tilesFlipped.value++; // Increment the flipped tiles counter

      // Check if two tiles are flipped
      if (tilesFlipped.value === 2) {
        // Compare the two flipped tiles
        const [firstTile, secondTile] = flippedTiles.value;
        if (firstTile.id === secondTile.id) {
          // If they match, mark them as matched
          firstTile.isMatched = true;
          secondTile.isMatched = true;
        } else {
          // If they don't match, flip them back after a delay
          setTimeout(() => {
            firstTile.isFlipped = false;
            secondTile.isFlipped = false;
          }, 500); // Adjust delay as needed
        }

        // Reset flipped tiles array and counter after processing
        setTimeout(() => {
          flippedTiles.value = [];
          tilesFlipped.value = 0;
          checkWin(); // Check for win after processing
        }, 500); // Align with the same delay
      }
    };

    // Function to check if all tiles are matched
    const checkWin = () => {
      const allMatched = tiles.value.every(tile => tile.isMatched); // Check if all tiles are matched
      if (allMatched) {
        console.log('You win! All pairs are matched.');
        gameStore.calculateTotalTime();
        gameStore.updateGame(); // Update the game data with the total time
        winpopUp();

      }
    };

    // Function to get tile image (flipped or default)
    const getTileImage = (tile) => {
      return computed(() =>
        tile.isFlipped
          ? new URL(`../assets/Tiles/cards/${tile.image}`, import.meta.url).href
          : new URL('../assets/Tiles/cards/0.png', import.meta.url).href
      ).value;
    };

    // Function to load board data from API
    const load = async () => {
      try {
        
        await gameStore.restoreGame();
        const game = gameStore.game;
        console.log(game);
        console.log('BOARD ID', game.value.board_id);

        // Fetch board data based on board_id
        const response = await axios.get(`boards/${game.value.board_id}`);
        try {
          console.log('BOARD 2', response);
          const t_data = response.data.data;
          board.board_cols = t_data.board_cols || 0;
          board.board_rows = t_data.board_rows || 1;

          console.log('Generating tiles...');
          const numPairs = (board.board_rows * board.board_cols) / 2;
          for (let i = 0; i < numPairs; i++) {
            const tile = { id: i + 1, image: `${i + 1}.png`, isFlipped: false, isMatched: false };
            tiles.value.push(tile);
            tiles.value.push({ ...tile });
          }

          console.log('Shuffling tiles...');
          shuffleArray(tiles.value);
          console.log('bruh');
        } catch (error) {
          console.error('Error during tile generation:', error);
        }

        shuffleArray(tiles.value);
        console.log('tiles', tiles.value);

      } catch (error) {
        console.error('Error loading board data:', error);
      }
    };

    // Run the load function when the component is mounted
    onMounted(() => {
      load();
    });


</script>

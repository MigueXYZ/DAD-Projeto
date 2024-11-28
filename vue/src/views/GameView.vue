<template>
  <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4">
    <h1 class="text-2xl font-bold mb-4 text-center">Game Board</h1>
    
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



<script>
import { useGameStore } from '@/stores/game'; // Import the game store
import { ref, computed, onMounted, reactive } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const board = reactive({
      board_cols: 0, // Default value for columns
      board_rows: 0, // Default value for rows
      
    });


    const tilesFlipped = ref(0); // Reactive counter to track flipped tiles
const flippedTiles = ref([]); // To store the currently flipped tiles

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
  const allMatched = tiles.every(tile => tile.isMatched); // Check if all tiles are matched
  if (allMatched) {
    console.log("You win! All pairs are matched.");
  }
};

    const tiles = reactive([]);
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
        const gameStore = useGameStore();
        await gameStore.restoreGame();
        const game = gameStore.game;
        console.log(game);
        console.log("BOARD ID", game.board_id);

        // Fetch board data based on board_id
        const { data } = await axios.get(`http://localhost:8081/api/boards/${game.board_id}`);
        
        console.log("BOARD", data);
        board.board_cols = data.board_cols || 0; // Set the number of columns
        board.board_rows = data.board_rows || 1; // Set the number of rows, defaulting to 1

        const shuffleArray = (array) => array.sort(() => Math.random() - 0.5);
        const numPairs = (board.board_rows*board.board_cols)/2;
        for (let i = 0; i<numPairs; i++){
          const tile = { id: i + 1, image: `${i + 1}.png`, isFlipped: false, isMatched: false };
      
          // Push the original tile
          tiles.push(tile);

          // Push a shallow copy for the pair
          tiles.push({ ...tile });
        }

        shuffleArray(tiles);
        console.log("tiles",tiles)

      } catch (error) {
        console.error('Error loading board data:', error);
      }
    };

    // Run the load function when the component is mounted
    onMounted(() => {
      load();
    });

    return { board, tiles, flipTile, getTileImage };
  },
};
</script>
<template>
  <div
      v-if="board"
      class="grid"
      :style="`grid-template-columns: repeat(${board.board_cols}, 1fr)`"
  >
    <!-- Generate board cells -->
    <div
        v-for="(cell, index) in totalCells"
        :key="index"
        class="relative w-20 h-20 bg-gray-300 rounded-md"
        @click="handleCardClick(index)"
    >
      <div
          v-if="isCardRevealed(index)"
          class="absolute inset-0 flex items-center justify-center text-xl font-bold"
      >
        {{ getCardValue(index) }}
      </div>
      <div
          v-else
          class="absolute inset-0 flex items-center justify-center bg-slate-800 text-white opacity-75 rounded-md"
      >
        ?
      </div>
    </div>
  </div>
  <div v-else>Loading board...</div>
</template>

<script setup>
import {ref, computed, onMounted} from 'vue'
import axios from 'axios'

// Props
const props = defineProps({
  game: {
    type: Object,
    required: true,
  },
})

// Local state
const board = ref(null) // Holds the fetched board data

// Fetch the board data when the component is mounted
const fetchBoard = async () => {
  try {
    const response = await axios.get(`/boards/${props.game.board_id}`)
    board.value = response.data.data // Assuming the board data is in the response
    console.log('Fetched board:', board.value)
  } catch (error) {
    console.error('Error fetching board:', error)
  }
}

onMounted(fetchBoard)

// Computed property for the total number of cells
const totalCells = computed(() => {
  if (!board.value) return 0
  return board.value.board_cols * board.value.board_rows
})

// Check if a card is revealed
const isCardRevealed = (index) => {
  return props.game.revealedCards.includes(index)
}

// Get the card value by index
const getCardValue = (index) => {
  // Update this logic as needed to get the correct card value
  return props.game.board[index]
}

// Handle card click
const handleCardClick = (index) => {
  if (!isCardRevealed(index)) {
    emit('playPieceOfBoard', index)
  }
}
</script>

<style scoped>
.grid {
  display: grid;
  gap: 1rem;
}

.card {
  position: relative;
  width: 5rem;
  height: 5rem;
  background-color: #f0f0f0;
  border-radius: 0.5rem;
  cursor: pointer;
}

.card-revealed {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
}

.card-hidden {
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #333;
  color: white;
  opacity: 0.75;
  border-radius: 0.5rem;
}
</style>

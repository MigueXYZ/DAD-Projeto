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
        class="relative card bg-white shadow-lg rounded-lg cursor-pointer"
        @click="handleCardClick(index)"
    >
      <div
          class="absolute inset-0 flex items-center justify-center h-full w-full p-1"
      >
        <img :src="getCardImage(index)" class="w-full h-full object-cover rounded-lg" />
      </div>
    </div>
  </div>
  <div v-else>Loading board...</div>
</template>

<script setup>
import {ref, computed, onMounted} from 'vue'
import axios from 'axios'
import {useToast} from '@/components/ui/toast/use-toast'

const {toast} = useToast()

// Defina os eventos que o componente pode emitir
const emit = defineEmits(['playPieceOfBoard']);

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
  // check if the revealed cards array has a number not equal to -1 in the index
  return props.game.revealedCards[index] !== -1
}

// Get the image URL for the card
const getCardImage = (index) => {
  const card = props.game.revealedCards[index] // Assuming board is an array of cards with 'image' property
  //console.log('Card:', card)
  if (isCardRevealed(index)) {
    // Return the image URL for the revealed card
    return new URL(`../../assets/Tiles/cards/${card}.png`, import.meta.url).href
  }
  // Return the back face of the card (default image when hidden)
  return new URL('../../assets/Tiles/cards/0.png', import.meta.url).href
}

// Handle card click
const handleCardClick = (index) => {
  if (!isCardRevealed(index)) {
    // Emit event or handle card flip
    emit('playPieceOfBoard', index)
  }
}

</script>

<style scoped>
.grid {
  display: grid;
  gap: 1rem;
  padding: 1rem;
}

.card {
  position: relative;
  width: 6rem; /* Width of the card */
  height: 9rem; /* Height of the card */
  background-color: white;
  border-radius: 0.5rem;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s ease-in-out;
}

.card:hover {
  transform: scale(1.05); /* Slight scale effect when hovered */
}

.card-revealed {
  display: flex;
  align-items: center;
  justify-content: center;
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

.card img {
  border-radius: 0.5rem;
}
</style>

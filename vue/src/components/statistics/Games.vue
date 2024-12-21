<template>
  <div class="flex flex-col items-center justify-center">
    <div class="grid-cols-1 grid text-center py-2">
      <h1>Total Games: {{ totalGames }}</h1>
    </div>

    <!-- Container for the pie charts -->
    <div class="flex space-x-4">
      <!-- Render Games Per Board in a Pie Chart -->
      <div v-if="gamesPerBoard.length > 0" class="w-12 text-center">
        <h2 class="font-bold">Games Per Board</h2>
        <Pie :data="gamesPerBoardChartData" :options="chartOptions" />
      </div>
      <p v-else>No data available for games per Board.</p>

      <!-- Render Games Per Gamemode in a Pie Chart -->
      <div v-if="gamesPerGamemode.length > 0" class="w-12 text-center">
        <h2 class="font-bold">Games Per Gamemode</h2>
        <Pie :data="gamesPerGamemodeChartData" :options="chartOptions" />
      </div>
      <p v-else>No data available for games per gamemode.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from "axios"
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale } from 'chart.js'

// Register necessary chart components
ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale)

const totalGames = ref(0)
const gamesPerBoard = ref([])
const gamesPerGamemode = ref([])
const boards = ref([])

const fetchBoards = async () => {
  const response = await axios.get("/boards")
  boards.value = response.data.data
}

const fetchGamesPerBoard = async () => {
  const response = await axios.get("/games/total/board")
  gamesPerBoard.value = response.data
}

const fetchGamesPerGamemode = async () => {
  const response = await axios.get("/games/total/gamemode")
  gamesPerGamemode.value = response.data
}

const fetchTotalGames = async () => {
  const response = await axios.get("/games/total")
  totalGames.value = response.data.num_games
}

onMounted(() => {
  fetchTotalGames()
  fetchGamesPerBoard()
  fetchGamesPerGamemode()
  fetchBoards()
})

// Pie chart options
const chartOptions = {
  responsive: true,
  plugins: {
    title: {
      display: true,
      text: 'Games Distribution'
    },
    tooltip: {
      callbacks: {
        label: (tooltipItem) => {
          return `${tooltipItem.label}: ${tooltipItem.raw} games`
        }
      }
    }
  }
}

// Pie chart data for Games per Board
const gamesPerBoardChartData = computed(() => {
  return {
    labels: gamesPerBoard.value.map(board => {
      // Find the matching board in the boards data
      const boardDetails = boards.value.find(b => b.id === board.board_id) // Use `id` to match
      if (boardDetails) {
        return `Board ${boardDetails.board_cols}x${boardDetails.board_rows}`
      }
      return `Board ${board.board_id}`
    }),
    datasets: [{
      data: gamesPerBoard.value.map(board => board.total),
      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#FF5733', '#33FF57', '#5733FF'],
      hoverOffset: 4
    }]
  }
})

// Pie chart data for Games per Gamemode
const gamesPerGamemodeChartData = computed(() => {
  return {
    labels: gamesPerGamemode.value.map(gamemode => {
      return gamemode.type === 'S' ? 'Singleplayer' : 'Multiplayer'
    }),
    datasets: [{
      data: gamesPerGamemode.value.map(gamemode => gamemode.total),
      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#FF5733'], // Colors for each segment
      hoverOffset: 4
    }]
  }
})
</script>

<style scoped>
/* Make the pie charts smaller and display them side by side */
.w-12 {
  width: 48%;
  margin-bottom: 2rem;
}

.flex {
  display: flex;
}

.space-x-4 {
  gap: 16px;
}

/* Optional: Add some custom styling for your pie charts */
</style>

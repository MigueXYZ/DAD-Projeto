<template>
  <div class="top-players">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold">Top Players</h2>
      <button @click="back" class="bg-orange-600 rounded-md text-white px-4 py-2">
        Back
      </button>
    </div>


    <!-- Filtros -->
    <div class="flex flex-wrap gap-4 mb-4">
      <!-- Filtro por Board -->
      <select v-model="filters.board" class="border p-3 rounded-md w-full md:w-1/3 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option v-for="board in boards" :key="board.id" :value="board.id" class="text-black">
          {{ board.board_cols }}x{{ board.board_rows }} Board
        </option>
      </select>

      <!-- Ordenar por -->
      <select v-model="filters.by" class="border p-3 rounded-md w-full md:w-1/3 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="total_time">Order by: Time</option>
        <option value="total_turns_winner">Order by: Turns</option>
      </select>

      <!-- Botão de Filtrar -->
      <button @click="applyFilters" class="bg-blue-500 rounded-md text-white px-4 py-2 w-full md:w-auto">
        Apply Filters
      </button>
    </div>

    <!-- Tabela de Jogadores -->
    <div v-if="loading" class="text-gray-500">Loading...</div>
    <div v-else-if="error" class="text-red-500">{{ error }}</div>
    <div v-else>
      <table class="w-full border-collapse border border-gray-300 rounded-md">
        <thead>
        <tr class="bg-gray-100">
          <th class="border border-gray-300 p-3 text-left">Rank</th>
          <th class="border border-gray-300 p-3 text-left">Player</th>
          <th class="border border-gray-300 p-3 text-left">Time</th>
          <th class="border border-gray-300 p-3 text-left">Board</th>
          <th class="border border-gray-300 p-3 text-left">Turns</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="(player, index) in topPlayers" :key="player.id" class="hover:bg-gray-50">
          <td class="border border-gray-300 p-3">{{ index + 1 }}</td>
          <td class="border border-gray-300 p-3">{{ player.name }}</td>
          <td class="border border-gray-300 p-3">{{ player.total_time }}</td>
          <td class="border border-gray-300 p-3">{{ player.board }}</td>
          <td class="border border-gray-300 p-3">{{ player.total_turns_winner }}</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div>
      <span class="flex items-center justify-center my-2">
        Page {{ page }} of {{ totalPages }}
      </span>
    </div>
    <div class="pagination flex w-full">
      <button class="w-1/2 py-3 bg-blue-700 hover:bg-blue-600 text-white rounded-md transition duration-200 my-2 mx-2" @click="loadPage(page - 1)" :disabled="page === 1">Previous</button>
      <button class="w-1/2 py-3 bg-blue-700 hover:bg-blue-600 text-white rounded-md transition duration-200 my-2 mx-2" @click="loadPage(page + 1)" :disabled="page === totalPages">Next</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import router from "@/router/index.js";

const topPlayers = ref([]);
const loading = ref(false);
const error = ref(null);
const page = ref(1);
const totalPages = ref(0);
const boards = ref([]);
const filters = ref({
  board: "1",
  by: "total_time",  // Ordenar por total_time por padrão
});

// Função para buscar os tabuleiros
const fetchBoards = async () => {
  try {
    const response = await axios.get("/boards");
    boards.value = response.data.data;
  } catch (err) {
    console.error("Error fetching boards:", err);
  }
};

// Função para buscar os jogadores com filtros
const fetchTopPlayers = async () => {
  loading.value = true;
  error.value = null;

  try {
    // Enviar filtros como parâmetros de consulta
    const response = await axios.get("/users/top", {
      params: {
        board: filters.value.board, // Filtro por tabuleiro
        by: filters.value.by, // Filtro por ordem (tempo ou turnos)
        page: page.value, // Adicionar a página atual à requisição
      }
    });
    topPlayers.value = response.data.data; // Atualizar os jogadores com a resposta
    totalPages.value = response.data.meta.last_page; // Atualizar o total de páginas
  } catch (err) {
    console.error("Error fetching top players:", err);
    error.value = "Failed to load top players. Please try again later.";
  } finally {
    loading.value = false;
  }
};


// Função para aplicar os filtros
const applyFilters = async () => {
  await fetchTopPlayers(); // Recarregar os jogadores com os filtros aplicados
};

// Função para carregar a próxima página
const loadPage = async (newPage) => {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage;
    await fetchTopPlayers(); // Recarregar os jogadores com a nova página
  }
};

// Função para voltar
const back = () => {
  router.push('/scoreboard');
};

onMounted(() => {
  fetchTopPlayers(); // Carregar os jogadores ao montar o componente
  fetchBoards(); // Carregar os tabuleiros ao montar o componente
});
</script>



<style scoped>
.top-players {
  padding: 16px;
}
</style>

<template>
  <div class="min-h-screen p-4">
    <!-- Check if stype is initialized correctly -->
    <h1 class="text-2xl font-bold mb-4">{{ stype }}</h1> <!-- You don't need .value here -->

    <!-- Filtros -->
    <div class="flex flex-wrap gap-4 mb-4">
      <!-- Filtro por Board -->
      <select v-model="filters.board" class="border p-2 w-full md:w-auto">
        <option value="">All Boards</option>
        <option v-for="board in boards" :key="board.id" :value="board.id" class="text-black">
          {{ board.board_cols }}x{{ board.board_rows }}
        </option>
      </select>

      <!-- Ordenar por -->
      <select v-model="filters.by" class="border p-2 w-full md:w-auto">
        <option value="">Order By</option>
        <option value="total_time">Total Time</option>
        <option value="total_turns_winner">Total Turns</option>
      </select>

      <!-- Botão de Filtrar -->
      <button @click="fetchGames" class="bg-blue-500 rounded-md text-white px-4 py-2 w-full md:w-auto">
        Apply Filters
      </button>

      <!-- Botão de Personal Scoreboard -->
      <button
          v-if="authStore.isLoggedIn"
          @click="toggleScoreboard" class="bg-green-500 rounded-md text-white px-4 py-2 w-full md:w-auto">
        Toggle {{ stype }}
      </button>

      <!-- Botão de Top Players -->
      <button @click="toggleTopPlayers" class="bg-purple-500 rounded-md text-white px-4 py-2 w-full md:w-auto">
        Show Top Players
      </button>

    </div>

    <!-- Tabela de Jogos -->
    <div v-if="loading" class="text-gray-500">Loading...</div>
    <table v-else class="w-full border-collapse border border-gray-300">
      <thead>
      <tr class="bg-gray-100">
        <th class="border border-gray-300 p-2 text-left">Game ID</th>
        <th class="border border-gray-300 p-2 text-left">Board</th>
        <th class="border border-gray-300 p-2 text-left">Total Turns</th>
        <th class="border border-gray-300 p-2 text-left">Total Time</th>
        <th class="border border-gray-300 p-2 text-left">Winner</th>
        <th class="border border-gray-300 p-2 text-left">Creator</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="game in filteredGames" :key="game.id">
        <td class="border border-gray-300 p-2">{{ game.id }}</td>
        <td class="border border-gray-300 p-2">
          {{ boards.find(board => board.id === game.board_id)?.board_cols || '-' }}x{{ boards.find(board => board.id === game.board_id)?.board_rows || 'N/A' }}
        </td>
        <td class="border border-gray-300 p-2">{{ game.total_turns_winner === null ? 'N/A' : game.total_turns_winner }}</td>
        <td class="border border-gray-300 p-2">{{ game.total_time === null ? 'N/A' : game.total_time }}</td>
        <td class="border border-gray-300 p-2">
          {{ game.winner_user_id === null ? 'N/A' : game.winner_user_id }}
        </td>
        <td class="border border-gray-300 p-2">
          {{ names.length ? names.find(name => name.id === game.created_user_id)?.name || 'N/A' : 'Loading...' }}
        </td>
      </tr>
      </tbody>
    </table>

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
import {ref, onMounted, computed} from "vue";
import {useAuthStore} from "@/stores/auth";
import axios from "axios";
import router from "@/router/index.js";

// Estado
const authStore = useAuthStore();
const stype = ref("Global Scoreboard");
const stype_url = ref("/games");
const games = ref([]);
const boards = ref([]);
const names = ref([]);
const filters = ref({
  board: "",
  by: "total_time",  // Ordenar por total_time por padrão
  order: "asc",      // Ordem ascendente por padrão
});
const loading = ref(false);
const page = ref(1);
const totalPages = ref(0);

// Função para buscar jogos com paginação
const fetchGames = async () => {
  loading.value = true;
  try {
    const {data} = await axios.get(stype_url.value, {
      params: {
        board: filters.value.board,
        by: filters.value.by,
        order: filters.value.order,
        page: page.value,
        ended: true,
      },
    });

    games.value = data.data; // Assumed to be the list of games
    totalPages.value = data.meta.last_page; // Make sure this value exists and is properly mapped
  } catch (error) {
    console.error("Error fetching games:", error);
  } finally {
    loading.value = false;
  }
};


// Função para filtrar os jogos para o placar pessoal
const filteredGames = computed(() => {
  let filtered = games.value;

  // Only include games with a valid total_time
  filtered = filtered.filter(game => game.total_time !== null);

  // Filter for personal scoreboard if needed
  if (stype.value === "Personal Scoreboard") {
    const userId = authStore.user.id; // Assumindo que o ID do usuário é armazenado no authStore
    filtered = filtered.filter(game => game.created_user_id === userId);
  }

  return filtered;
});

const loadPage = async (newPage) => {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage;
    await fetchGames();
  }
};

// Função para alternar entre placar global e pessoal
const toggleScoreboard = () => {
  stype.value = stype.value === "Global Scoreboard" ? "Personal Scoreboard" : "Global Scoreboard";
  stype_url.value = stype.value === "Global Scoreboard" ? "/games" : "/games/me";
  fetchGames(); // Recarrega os jogos com o novo tipo de placar
};

const fetchNames = async () => {
  try {
    const response = await axios.get("/users/names");
    names.value = response.data;
  } catch (error) {
    console.error("Error fetching names:", error);
  }
};

const toggleTopPlayers = () => {
  router.push('/top-players');
};

// Função para buscar boards (exemplo, se aplicável)
const fetchBoards = async () => {
  try {
    const {data} = await axios.get("/boards");
    boards.value = data.data;
  } catch (error) {
    console.error("Error fetching boards:", error);
  }
};

// Busca inicial
onMounted(() => {
  fetchGames();
  fetchBoards();
  fetchNames();
});
</script>

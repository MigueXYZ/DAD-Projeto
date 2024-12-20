<template>
  <div class="min-h-screen p-4">
    <h1 class="text-2xl font-bold mb-4">Games</h1>

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
        <option value="created_at">Played At</option>
        <option value="total_time">Total Time</option>
        <option value="total_turns_winner">Total Turns</option>
        <option value="winner_user_id">Winner</option>
      </select>

      <!-- Ordem -->
      <select v-model="filters.order" class="border p-2 w-full md:w-auto">
        <option value="">Order Direction</option>
        <option value="asc">Ascending</option>
        <option value="desc">Descending</option>
      </select>

      <!-- Botão de Filtrar -->
      <button @click="fetchGames" class="w-full lg:w-full py-3 bg-blue-700 hover:bg-blue-600 text-white rounded-md  transition duration-200 my-2">
        Apply Filters
      </button>
    </div>

    <!-- Lista de Jogos -->
    <div v-if="loading" class="text-gray-500">Loading...</div>
    <ul v-else>
      <li v-for="game in games" :key="game.id" class="mb-2 border p-2">
        <p><strong>Game ID:</strong> {{ game.id }}</p>
        <p><strong>State:</strong> {{ game.status }} </p>
        <p><strong>Type:</strong> {{game.type==='M'?'Multiplayer':'Singleplayer'}}</p>
        <p v-if="game.type==='M'"><strong></strong></p>
        <p><strong>Board:</strong>{{ boards.find(board => board.id === game.board_id)?.board_cols || '-' }}x{{ boards.find(board => board.id === game.board_id)?.board_rows || 'N/A' }}</p>
        <p><strong>Total Turns:</strong> {{ game.total_turns_winner === null ? 'N/A' : game.total_turns_winner}}</p>
        <p><strong>Total Time:</strong> {{ game.total_time === null ? 'N/A' : game.total_time }}</p>
        <p><strong>Winner:</strong> {{ names.length ? names.find(name => name.id === game.winner_user_id)?.name || 'N/A' : 'Loading...' }}</p>
      </li>
    </ul>

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
import { useAuthStore } from "@/stores/auth";
import axios from "axios";

// Estado
const authStore = useAuthStore();
const games = ref([]);
const boards = ref([]);
const filters = ref({
  board: "",
  by: "created_at",  // Ordenar por created_at por padrão
  order: "desc",      // Ordem descendente por padrão
});

const loading = ref(false);
const page = ref(1);
const totalPages = ref(0);
const names = ref([]);

const fetchNames = async () => {
  try {
    const response = await axios.get("/users/names");
    names.value = response.data;
  } catch (error) {
    console.error("Error fetching names:", error);
  }
};

// Função para buscar jogos
const fetchGames = async () => {
  loading.value = true;

  try {
    const { data } = await axios.get(!authStore.isAdmin?"/games/me":"/games", {
      params: {
        board: filters.value.board,
        by: filters.value.by,
        order: filters.value.order,
        page: page.value,  // Adiciona a página atual à requisição
      },
    });
    games.value = data.data; // Supondo que a API usa `data` como wrapper
    totalPages.value = data.meta.last_page;  // Atualiza o total de páginas
    console.log("Games fetched:", games.value);
  } catch (error) {
    console.error("Error fetching games:", error);
  } finally {
    loading.value = false;
  }
};

// Função para buscar boards
const fetchBoards = async () => {
  try {
    const { data } = await axios.get("/boards");
    boards.value = data.data;
  } catch (error) {
    console.error("Error fetching boards:", error);
  }
};

// Navegar para outra página
const loadPage = async (newPage) => {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage;
    await fetchGames();
  }
};

// Busca inicial
onMounted(() => {
  fetchGames();
  fetchNames();
  fetchBoards();
});
</script>

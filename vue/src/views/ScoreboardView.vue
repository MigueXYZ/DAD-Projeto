<template>
  <div class="scoreboard-container p-6 bg-gray-50 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-center mb-6">Melhores Jogos por Tabuleiro</h1>

    <!-- Controle de seleção de Scoreboards -->
    <div class="text-center mb-6">
      <button
          class="px-4 py-2 bg-blue-500 text-white rounded-md"
          :class="{'bg-blue-700': isPersonal}"
          v-if="authStore.isLoggedIn && !authStore.isAdmin"
          @click="toggleScoreboard('personal')"
      >
        Scoreboard Pessoal
      </button>
      <button
          class="px-4 py-2 bg-blue-500 text-white rounded-md ml-4"
          :class="{'bg-blue-700': !isPersonal}"
          @click="toggleScoreboard('global')"
      >
        Scoreboard Global
      </button>
    </div>

    <div v-if="boards.length" class="space-y-6">
      <!-- Loop através dos boards -->
      <div
          v-for="(board, index) in boards"
          :key="index"
          class="p-6 bg-white rounded-lg shadow-md border border-gray-200"
      >
        <h2 class="text-xl font-semibold mb-4 text-center">Board {{ board.board_cols }}x{{ board.board_rows }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Melhor Jogo por Tempo -->
          <div class="border p-4 rounded-lg shadow-md">
            <h3 class="font-semibold text-lg text-center mb-4">Melhor Jogo por Tempo</h3>
            <div v-if="getBestGameByTime(board.id)">
              <div class="flex justify-between mb-2">
                <span class="text-gray-600">Jogo:</span>
                <span class="font-medium">{{ getBestGameByTime(board.id)?.board_id }}</span>
              </div>
              <div class="flex justify-between mb-2">
                <span class="text-gray-600">Tempo:</span>
                <span class="font-medium">{{ getBestGameByTime(board.id)?.min_time }} segundos</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Jogador:</span>
                <span class="font-medium">{{ getBestNicknameByTime(board.id) }}</span>
              </div>
            </div>
            <div v-else>
              <p class="text-center text-gray-500">Nenhum jogo registrado.</p>
            </div>
          </div>

          <!-- Melhor Jogo por Turnos -->
          <div class="border p-4 rounded-lg shadow-md">
            <h3 class="font-semibold text-lg text-center mb-4">Melhor Jogo por Turnos</h3>
            <div v-if="getBestGameByTurns(board.id)">
              <div class="flex justify-between mb-2">
                <span class="text-gray-600">Jogo:</span>
                <span class="font-medium">{{ getBestGameByTurns(board.id)?.board_id }}</span>
              </div>
              <div class="flex justify-between mb-2">
                <span class="text-gray-600">Turnos:</span>
                <span class="font-medium">{{ getBestGameByTurns(board.id)?.min_turns }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Jogador:</span>
                <span class="font-medium">{{ getBestNicknameByTurns(board.id) }}</span>
              </div>
            </div>
            <div v-else>
              <p class="text-center text-gray-500">Nenhum jogo registrado.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center text-gray-500">
      Nenhum board disponível.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const scoreboards = ref({ time: [], turns: [] });
const nicknames = ref([]);
const boards = ref([]);
const isPersonal = ref(false); // Controle para saber se é o scoreboard pessoal ou global

// Função para alternar entre os scoreboards pessoal e global
const toggleScoreboard = (type) => {
  isPersonal.value = type === 'personal';
  fetchScoreboards(); // Recarrega os scoreboards conforme a escolha
};

// Buscar os boards
const fetchBoards = async () => {
  try {
    const { data } = await axios.get("/boards");
    boards.value = data.data;
  } catch (error) {
    console.error("Error fetching boards:", error);
  }
};

// Buscar os nicknames dos jogadores
const fetchNicknames = async () => {
  const response = await axios.get("/users/nicknames");
  nicknames.value = response.data;
};

// Buscar os scoreboards (pontuações dos jogos)
const fetchScoreboards = async () => {
  const url = isPersonal.value ? "/scoreboards/me" : "/scoreboards";
  try {
    const response = await axios.get(url);
    scoreboards.value = response.data;
  } catch (error) {
    console.error("Error fetching scoreboards:", error);
  }
};

// Obter o nickname de um jogador
const getNickname = (userId) => {
  const user = nicknames.value.find((nickname) => nickname.id === userId);
  return user ? user.nickname : null;
};


// Obter o melhor jogo por tempo para um board específico
const getBestGameByTime = (boardId) => {
  const bestTime = scoreboards.value.time.find((game) => game.board_id === boardId);
  return bestTime ? bestTime : null; // Retorna o melhor jogo por tempo
};

// Obter o melhor jogo por turnos para um board específico
const getBestGameByTurns = (boardId) => {
  const bestTurns = scoreboards.value.turns.find((game) => game.board_id === boardId);
  return bestTurns ? bestTurns : null; // Retorna o melhor jogo por turnos
};

// Obter o nickname do jogador com melhor tempo para um board
const getBestNicknameByTime = (boardId) => {
  const bestGame = getBestGameByTime(boardId);
  return bestGame ? getNickname(bestGame.created_user_id) : null;
};

// Obter o nickname do jogador com menos turnos para um board
const getBestNicknameByTurns = (boardId) => {
  const bestGame = getBestGameByTurns(boardId);
  return bestGame ? getNickname(bestGame.created_user_id) : null;
};

// Carregar os dados ao montar o componente
onMounted(() => {
  fetchNicknames();
  fetchScoreboards();
  fetchBoards();
});
</script>

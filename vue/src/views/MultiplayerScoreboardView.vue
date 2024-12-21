<template>
  <div class="p-6 bg-gray-100 min-h-screen">
    <h1 class="text-2xl font-bold text-center mb-6">Multiplayer Scoreboards</h1>

    <div v-if="scoreboards.length" class="space-y-8">
      <div
          v-for="scoreboard in scoreboards"
          :key="scoreboard.board_id"
          class="bg-white shadow rounded-lg p-4"
      >
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
          Board: {{ getBoardDimensions(scoreboard.board_id) }}
        </h2>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-200">
            <tr>
              <th class="px-4 py-2">Rank</th>
              <th class="px-4 py-2">Player</th>
              <th class="px-4 py-2">Victories</th>
              <th class="px-4 py-2">Defeats</th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="(player, index) in scoreboard.scoreboard"
                :key="player.user_id"
                class="border-t"
            >
              <td class="px-4 py-2">{{ index + 1 }}</td>
              <td class="px-4 py-2">{{ getPlayerNickname(player.user_id) }}</td>
              <td class="px-4 py-2">{{ player.victories }}</td>
              <td class="px-4 py-2">{{ player.total_games-player.victories }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-else class="text-center text-gray-600">
      <p>No scoreboards available at the moment.</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import axios from "axios";

const scoreboards = ref([]);
const names = ref([]);
const boards = ref([]);

// Função para buscar as dimensões do tabuleiro pelo ID
const getBoardDimensions = (boardId) => {
  const board = boards.value.find((b) => b.id === boardId);
  return board ? `${board.board_cols} x ${board.board_rows}` : "Unknown Board";
};

// Função para buscar o nickname do jogador pelo user_id
const getPlayerNickname = (userId) => {
  const user = names.value.find((u) => u.id === userId);
  return user ? user.name : "Unknown Player";
};

const fetchBoards = async () => {
  try {
    const { data } = await axios.get("/boards");
    boards.value = data.data;
  } catch (error) {
    console.error("Error fetching boards:", error);
  }
};

const fetchNames = async () => {
  try {
    const response = await axios.get("/users/names");
    names.value = response.data;
  } catch (error) {
    console.error("Error fetching names:", error);
  }
};

const fetchScoreboards = async () => {
  try {
    const response = await axios.get("/multiplayer-scoreboard");
    scoreboards.value = response.data;
  } catch (error) {
    console.error("Error fetching scoreboards:", error);
  }
};

onMounted(() => {
  fetchScoreboards();
  fetchBoards();
  fetchNames();
});
</script>

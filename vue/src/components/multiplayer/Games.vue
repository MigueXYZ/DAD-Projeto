<script setup>
import { onMounted } from 'vue'
import Game from './Game.vue'
import { useGameStore } from '@/stores/game';

const storeGames = useGameStore()

onMounted(() => {
  storeGames.fetchPlayingGames()
})
</script>

<template>
  <div class="justify-stretch grid grid-cols-2">
    <Game v-for="game in storeGames.games" :key="game.id" :game="game">
    </Game>
  </div>
</template>
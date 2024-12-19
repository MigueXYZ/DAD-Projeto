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
  <div class="flex flex-wrap justify-stretch">
    <Game v-for="game in storeGames.games" :key="game.id" :game="game">
    </Game>
  </div>
</template>
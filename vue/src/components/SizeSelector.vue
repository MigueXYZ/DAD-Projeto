<template>
    
      <p v-if="board.custom">Custom Size: {{ board.custom }}</p>
      <button @click="startGame" 
        class="border w-full rounded-lg shadow-md p-8 bg-blue-600 text-white font-bold transition duration-200 text-xl hover:bg-blue-700">
  {{ board.board_cols }} x {{ board.board_rows }}
</button>
    
  </template>
  
  <script>
  import { useGameStore } from '@/stores/game'; // Importa a store de autenticação
  export default {
    props: {
      board: {
      type: Object,
      required: true, // Ensure the prop is passed
    },// Expecting a 'board' prop
    },
    methods: {
      formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0'); // Ajusta o mês para 2 dígitos
      const day = String(date.getDate()).padStart(2, '0');
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      const seconds = String(date.getSeconds()).padStart(2, '0');
      return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    },
      async startGame() {
        const gameStore = useGameStore();
        const gameData = {
        type: 'S', // Substitua com o tipo desejado
        status: 'PL', // Status inicial do jogo
        began_at: this.formatDate(new Date()), // Formata a data atual
        board_id: this.board.id, // Usa o ID do primeiro tabuleiro ou um valor padrão
        custom: null, // Informações adicionais (opcional)
      };
        const success = await gameStore.createGame(gameData);
        this.$router.push('/game'); // Navigate to the size selection page
      },
    }
  };
  </script>
  
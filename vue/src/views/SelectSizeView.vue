<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
      <!-- Header Section -->
      
  
      <!-- Main Content Section -->
      <main class="flex-1 w-full max-w-4xl px-6 py-8 mx-auto">
        <div class="bg-white p-6 my-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
          <h1 class="text-black text-xl font-semibold" >Select Board Dimentions:</h1>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 my-8">
            <!-- Fallback message if boards array is empty -->
            <div v-if="boards.length === 0">Loading boards...</div>
            
            <!-- Loop through boards and render each SizeSelector -->
            <div v-for="board in boards" :key="board.id">
              <SizeSelector :board="board" />
            </div>
          </div>
            
        </div>
      </main>
  
      <!-- Footer Section -->
    </div>
  </template>

<script>
import SizeSelector from '@/components/SizeSelector.vue';
import { useGameStore } from '@/stores/game'; // Importa a game store

import { storeToRefs } from 'pinia';
import axios from 'axios';
import {useAuthStore} from "@/stores/auth.js";

export default {
  components: {
    SizeSelector,
  },
  data() {
    return {
      boards: [], // Holds the fetched board data
    };
  },
  methods: {
    async load() {
      try {
        const response = await axios.get('http://localhost:8081/api/boards');
        this.boards = response.data.data; // Set the response data
        console.log('Boards:', this.boards); // Check the fetched data in the console
      } catch (error) {
        console.error('Error fetching boards:', error.message);
        console.error('Error details:', error.response || error.config);
      }
    },
    // Função para formatar a data no formato 'YYYY-MM-DD HH:MM:SS'
    formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0'); // Ajusta o mês para 2 dígitos
      const day = String(date.getDate()).padStart(2, '0');
      const hours = String(date.getHours()).padStart(2, '0');
      const minutes = String(date.getMinutes()).padStart(2, '0');
      const seconds = String(date.getSeconds()).padStart(2, '0');
      return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    },
    async testCreateGame() {
      const gameStore = useGameStore(); // Acessa a game store
      const { games } = storeToRefs(gameStore); // Referência reativa aos jogos

      // Dados de exemplo para criar um jogo
      const gameData = {
        type: 'S', // Substitua com o tipo desejado
        status: 'PL', // Status inicial do jogo
        began_at: this.formatDate(new Date()), // Formata a data atual
        board_id: this.boards[0]?.id || 1, // Usa o ID do primeiro tabuleiro ou um valor padrão
        custom: null, // Informações adicionais (opcional)
      };

      const success = await gameStore.createGame(gameData); // Chama o método createGame
      if (success) {
        console.log('Jogo criado com sucesso:', games.value);
      } else {
        console.error('Erro ao criar o jogo.');
      }
    },
    startGame() {
      this.testCreateGame(); // Testa a criação de um jogo antes de iniciar
      this.$router.push('/size'); // Navega para a página de seleção de tamanho
    },
    home() {
      this.$router.push('dashboard');
    },

  },
  mounted() {
    this.load(); // Fetch the boards when the component is mounted

  },
};
</script>


  <!-- Script antigo
  <script>
  import SizeSelector from '@/components/SizeSelector.vue';
  import axios from 'axios';

  export default {
    components: {
      SizeSelector,
    },
    data() {
      return {
        boards: [], // Holds the fetched board data
      };
    },
    methods: {
      async load() {
        try {
          //const response = await axios.get('http://api-dad-group-5-172.22.21.101.sslip.io/api/boards');
          const response = await axios.get('http://localhost:8081/api/boards');
          this.boards = response.data; // Set the response data
          console.log('Boards:', this.boards); // Check the fetched data in the console
        } catch (error) {
          console.error('Error fetching boards:', error.message);
          console.error('Error details:', error.response || error.config);
        }
      },
      startGame() {
        this.$router.push('/game'); // Navigate to the size selection page
      },
      home(){
        this.$router.push('dashboard');
      }
    },
    mounted() {
      this.load(); // Fetch the boards when the component is mounted
    },
  };
  </script>
  -->
<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Header Section -->


    <!-- Main Content Section -->
    <main class="flex-1 w-full max-w-4xl px-6 py-8 mx-auto">
      <div class="grid grid-cols-1 gap-6">
        <!-- Brain Coins Balance Component -->
        
        <div class="bg-white p-6 mt-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
          <BrainCoinsBalance />
          
          <div class="mt-4 grid grid-cols-1">
            <button
                @click="startGame"
                class="w-auto lg:w-full py-3 bg-blue-700 text-white rounded-md hover:bg-blue-600 transition duration-200 my-2"
              >
                Play SinglePlayer
              </button>
              <button
                @click="startGame"
                class="w-auto lg:w-full py-3  bg-blue-700 text-white rounded-md hover:bg-blue-600 transition duration-200"
              >
                Join Lobby
              </button>
          </div>
          
        </div>
        <!-- Logout Card -->
        <div class="transition w-full duration-300 flex justify-end">
          <button
              @click="logout"
              class="py-3 px-10 w-full sm:w-auto bg-red-500 text-white rounded-md hover:bg-red-700 transition duration-200"
          >
            Logout
          </button>
        </div>
      </div>
    </main>

    <!-- Footer Section -->
  </div>
</template>

<script>
import BrainCoinsBalance from '@/components/BrainCoinsBalance.vue'; // Importando o componente
import { useAuthStore } from '@/stores/auth';
import PlayGame from "@/components/PlayGame.vue"; // Importando a store de autenticação
import SizeSelector from '@/components/SizeSelector.vue';
import axios from 'axios';

export default {
  components: {
    PlayGame,
    BrainCoinsBalance,
    SizeSelector,  // Registrando o componente
  },
  data() {
    return {
      user: null,
      boards: [], // Holds the fetched board data
    };
  },
  created() {
    const authStore = useAuthStore();

    // Verificar se o usuário está autenticado através da store
    if (authStore.user) {
      this.user = authStore.user;
    } else {
      this.$router.push('/login');
    }
  },
  methods: {
    startGame() {
      // Redireciona para a página de jogo
      this.$router.push('/game');
    },
    async logout() {
      const authStore = useAuthStore();

      try {
        // Realiza o logout chamando a função da store
        await authStore.logout();

        // Redireciona para a página de login
        this.$router.push('/login');
      } catch (error) {
        console.error('Logout error:', error);
        alert('An error occurred while logging out. Please try again.');
      }
    },
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
        this.$router.push('/size'); // Navigate to the size selection page
      },
      home(){
        this.$router.push('dashboard');
      },
  }
};
</script>

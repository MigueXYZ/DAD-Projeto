<template>
    <div class="bg-blue-600 rounded-lg ">
      <div class="text-l lg:text-xl font-semibold bg-blue-600 ">
        {{ brainCoinsBalance }} Brain Coins
      </div>
    </div>
  </template>
  
  <script>
  import { useAuthStore } from '@/stores/auth'; // Importando a store de autenticação
  
  export default {
    computed: {
      // Obtendo o saldo de brain_coins da store
      brainCoinsBalance() {
        const authStore = useAuthStore();
        return authStore.user ? authStore.user.brain_coins_balance : 0;
      }
    }
  };
  </script>
  
  <style scoped>
  /* Estilos opcionais para o componente */
  </style>
  
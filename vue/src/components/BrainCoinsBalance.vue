<template>
  <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
    <p class="text-gray-700">You currently have:</p>
    <div class="text-3xl font-semibold text-blue-600 mt-2">
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

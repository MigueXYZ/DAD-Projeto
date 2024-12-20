<template>
  <div class="p-4 bg-gray-100 min-h-screen">
    <h1 class="text-xl font-bold mb-4">Transactions</h1>

    <!-- Mensagens de erro ou sucesso -->
    <div v-if="error" class="text-sm text-red-500 mb-4">{{ error }}</div>
    <div v-if="success" class="text-sm text-green-500 mb-4">{{ success }}</div>

    <!-- Tabela de transações -->
    <div v-if="transactions.length" class="overflow-x-auto">
      <table class="min-w-full bg-white shadow-md rounded-lg">
        <thead class="bg-gray-200 text-gray-700">
        <tr>
          <th class="px-4 py-2 text-left">Date</th>
          <th class="px-4 py-2 text-left">Game ID</th>
          <th class="px-4 py-2 text-left">Type</th>
          <th class="px-4 py-2 text-left">Euros</th>
          <th class="px-4 py-2 text-left">Brain Coins</th>
          <th class="px-4 py-2 text-left">Payment Type</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-100">
          <td class="border-t px-4 py-2">{{ transaction.transaction_datetime }}</td>
          <td class="border-t px-4 py-2">{{ transaction.game_id || 'N/A' }}</td>
          <td class="border-t px-4 py-2">{{ transaction.type }}</td>
          <td class="border-t px-4 py-2">{{ transaction.euros || 'N/A' }}</td>
          <td class="border-t px-4 py-2">{{ transaction.brain_coins }}</td>
          <td class="border-t px-4 py-2">{{ transaction.payment_type || 'N/A' }}</td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginação -->
    <div class="flex justify-center items-center space-x-4 mt-6">
      <button
          @click="fetchTransactions(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
      >
        Previous
      </button>
      <span>Page {{ currentPage }} of {{ totalPages }}</span>
      <button
          @click="fetchTransactions(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import {ref, onMounted} from 'vue';
import axios from 'axios';

// Estado
const transactions = ref([]);
const error = ref('');
const success = ref('');
const currentPage = ref(1); // Página atual
const totalPages = ref(1); // Total de páginas
const pageSize = ref(10); // Limite de itens por página
const authStore = useAuthStore();

// Função para buscar transações com paginação
const fetchTransactions = async (page = 1) => {
  try {
    error.value = '';
    const getAll = '/transactions'
    const getMine = '/transactions/me'
    const response = await axios.get(authStore.isAdmin?getAll:getMine, {
      params: {
        page,
        limit: pageSize.value,
      },
    });

    const {data, meta} = response.data; // Certifique-se de que sua API retorna esses campos
    transactions.value = data;
    currentPage.value = meta.currentPage;
    totalPages.value = meta.totalPages;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch transactions.';
  }
};

// Carrega a primeira página ao montar o componente
onMounted(() => fetchTransactions());
</script>

<style scoped>
/* Estilos personalizados, se necessário */
</style>

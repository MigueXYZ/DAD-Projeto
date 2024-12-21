<template>
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 text-white p-6 sticky top-0 h-screen">
      <h2 class="text-2xl font-semibold mb-6"> Statistics</h2>
      <ul>
        <li class="mb-4">
          <a href="#utilizadoes" class="text-lg hover:text-gray-400">Utilizadores</a>
        </li>
        <li class="mb-4">
          <a href="#" class="text-lg hover:text-gray-400">Overview</a>
        </li>
        <li class="mb-4">
          <a href="#brainCoins" class="text-lg hover:text-gray-400">Brain Coins</a>
        </li>
        <li class="mb-4">
          <a href="#games" class="text-lg hover:text-gray-400">Games</a>
        </li>
        <li class="mb-4">
          <a href="#transactions" class="text-lg hover:text-gray-400">Transactions</a>
        </li>
      </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 w-full max-w-4xl px-6 py-8 mx-auto">
      <div class="grid grid-cols-1 gap-6 bg-gray-100">
        <!-- Utilizadores Section -->
        <div class="bg-white p-6 mt-8 rounded-lg shadow-md hover:shadow-lg transition duration-300" id="utilizadores">
          <h1 class="text-center text-xl font-bold">Utilizadores</h1>
          <Utilizadores />
        </div>

        <!-- BrainCoins Section -->
        <div class="bg-white p-6 mt-8 rounded-lg shadow-md hover:shadow-lg transition duration-300" id="brainCoins">
          <h1 class="text-center text-xl font-bold">Brain Coins Data</h1>
          <BrainCoins />
        </div>

        <!-- Games Section -->
        <div class="bg-white p-6 mt-8 rounded-lg shadow-md hover:shadow-lg transition duration-300" id="games">
          <h1 class="text-center text-xl font-bold">Games</h1>
          <Games />
        </div>

        <!-- Transactions Section -->
        <div class="bg-white p-6 mt-8 rounded-lg shadow-md hover:shadow-lg transition duration-300" id="transactions">
          <h1 class="text-center text-xl font-bold">Transactions</h1>
          <Transactions />
        </div>
      </div>
    </main>
  </div>
</template>


<script setup>
import BrainCoins from "@/components/statistics/BrainCoins.vue";
import Games from "@/components/statistics/Games.vue";
import Transactions from "@/components/statistics/Transactions.vue";
import Utilizadores from "@/components/statistics/Utilizadores.vue";
import { ref, onMounted } from 'vue';
</script>

<style scoped>
/* Optional: Additional custom styles */
</style>

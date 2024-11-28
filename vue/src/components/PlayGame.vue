<template>
  <!-- Play Game Card -->
  <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300">
    <h2 class="text-gray-900 text-xl font-semibold mb-4">Play Game</h2>
    <p class="mb-4 text-gray-700">Start a new memory game.</p>

    <!-- Botão desabilitado se o saldo de brain_coins for 0 -->
    <button
        
        @click="startGame"
        class="w-full py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200"
        
    >
      Start Game
    </button>

    <!-- Mensagem se o saldo de brain_coins for 0 -->
  </div>
</template>

<script>
  export default{
    methods: {
      startGame(){
        this.$router.push('/size');
      }
    }
    
  };
</script>

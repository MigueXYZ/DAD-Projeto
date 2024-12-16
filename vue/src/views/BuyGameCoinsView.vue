<template>
  <div class="p-4 bg-gray-100 min-h-screen">
    <h1 class="text-xl font-bold mb-4">Comprar Brain Coins</h1>

    <!-- Loading Indicator -->
    <div v-if="isLoading" class="text-center text-gray-500">Carregando...</div>

    <!-- Formulário de Compra de Brain Coins -->
    <div v-else class="space-y-4">
      <form @submit.prevent="buyBrainCoins">
        <label for="brain_coins" class="block text-sm font-medium text-gray-700">Quantidade de Brain Coins</label>
        <input
            v-model="quantity"
            id="brain_coins"
            type="number"
            min="1"
            required
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm sm:text-sm py-3"
        />

        <label for="payment_reference" class="block text-sm font-medium text-gray-700 mt-4">Referência de Pagamento (opcional)</label>
        <input
            v-model="paymentReference"
            id="payment_reference"
            type="text"
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm sm:text-sm py-3"
        />

        <button
            type="submit"
            class="w-full py-2 px-4 bg-indigo-600 text-white font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-4"
        >
          Comprar Brain Coins
        </button>
      </form>

      <p v-if="error" class="text-sm text-red-500 mt-2">{{ error }}</p>
      <p v-if="success" class="text-sm text-green-500 mt-2">Compra realizada com sucesso!</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/components/ui/toast/use-toast';

const { toast } = useToast();
const authStore = useAuthStore();

const quantity = ref(1); // Quantidade de Brain Coins a ser comprada
const paymentReference = ref(''); // Referência de pagamento opcional
const error = ref('');
const success = ref(false);
const isLoading = ref(false);

// Lógica para realizar a compra de Brain Coins
const buyBrainCoins = async () => {
  try {
    isLoading.value = true;
    error.value = ''; // Resetando erro

    // Chamada para a store ou API para realizar a compra
    const response = await authStore.buyBrainCoins(quantity.value, paymentReference.value);

    if (response.success) {
      success.value = true;
      toast({
        description: 'Compra realizada com sucesso!',
      });
    } else {
      throw new Error(response.message || 'Erro ao realizar a compra');
    }
  } catch (err) {
    error.value = err.message || 'Erro ao realizar a compra';
    toast({
      description: error.value,
      variant: 'destructive',
    });
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* Opcional: estilo adicional */
</style>

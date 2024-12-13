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
import { useToast } from '@/components/ui/toast/use-toast';
import { ToastAction } from '@/components/ui/toast';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from "axios";

export default {
  setup() {
    // Obtendo a store de autenticação
    const authStore = useAuthStore();
    const brainCoinsBalance = ref(0);
    const { toast } = useToast();

    const updateBrainCoins = async () => {
      try {
        // Obtém o saldo de brain_coins atual
        const previousBalance = authStore.user.brain_coins_balance;
        console.log('Previous balance:', previousBalance);

        // Faz uma chamada à API para atualizar os dados do usuário
        const response = await axios.get(`/users/me`);
        if (response.data && response.data.data) {
          authStore.setUser(response.data.data);
          brainCoinsBalance.value = authStore.user.brain_coins_balance;
          console.log('Updated balance:', brainCoinsBalance.value);
          if (previousBalance !== brainCoinsBalance.value) {
            toast({
              description: 'Brain Coins balance updated successfully.',
            });
          }
        } else {
          console.error('Invalid response data:', response.data);
        }
      } catch (error) {
        console.error('Error updating Brain Coins:', error);
        toast({
          description: 'Failed to update Brain Coins balance.',
          action: ToastAction.RETRY,
        });
      }
    };

    // Atualizando o saldo de brain_coins a cada minuto
    let intervalId = null;
    onMounted(() => {
      updateBrainCoins(); // Atualização inicial
      intervalId = setInterval(updateBrainCoins, 60000); // Atualiza a cada 60 segundos
    });

    onUnmounted(() => {
      if (intervalId) {
        clearInterval(intervalId); // Limpa o intervalo ao desmontar o componente
      }
    });

    return {
      brainCoinsBalance
    };
  }
};
</script>

<template>
  <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 flex items-center">
    <!-- Informações de saldo -->
    <div class="flex-1">
      <p class="text-gray-700">You currently have:</p>
      <div class="text-3xl font-semibold text-blue-600 mt-2">
        {{ brainCoinsBalance }} Brain Coins
      </div>
    </div>

    <!-- Botões à direita -->
    <div class="flex flex-col space-y-4">
      <button
          @click="goToTransactions"
          class="py-2 px-4 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300"
      >
        View Transactions
      </button>
      <button
          @click="goToPayments"
          class="py-2 px-4 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300"
      >
        Comprar Brain Coins
      </button>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/components/ui/toast/use-toast';
import { ToastAction } from '@/components/ui/toast';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from "axios";
import { useRouter } from 'vue-router';

export default {
  setup() {
    const authStore = useAuthStore();
    const brainCoinsBalance = ref(0);
    const { toast } = useToast();
    const router = useRouter();

    const updateBrainCoins = async () => {
      try {
        const previousBalance = authStore.user.brain_coins_balance;
        const response = await axios.get(`/users/me`);
        if (response.data && response.data.data) {
          authStore.setUser(response.data.data);
          brainCoinsBalance.value = authStore.user.brain_coins_balance;
          if (previousBalance !== brainCoinsBalance.value) {
            toast({description: 'Brain Coins balance updated successfully.'});
          }
        }
      } catch (error) {
        toast({description: 'Failed to update Brain Coins balance.', action: ToastAction.RETRY});
      }
    };

    const goToTransactions = () => {
      router.push('/transactions/me');
    };

    const goToPayments = () => {
      router.push('/buy-coins');
    };

    let intervalId = null;
    onMounted(() => {
      updateBrainCoins();
      intervalId = setInterval(updateBrainCoins, 60000);
    });

    onUnmounted(() => {
      if (intervalId) clearInterval(intervalId);
    });

    return {
      brainCoinsBalance,
      goToTransactions,
      goToPayments,
    };
  },
};
</script>

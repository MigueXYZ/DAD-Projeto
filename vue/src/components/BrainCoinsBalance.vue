<template>
  <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 flex items-center">
    <!-- Informações de saldo -->
    <div v-if="!isAdmin" class="flex-1">
      <p class="text-gray-700">You currently have:</p>
      <div class="text-3xl font-semibold text-blue-600 mt-2">
        {{ brainCoinsBalance }} Brain Coins
      </div>
    </div>

    <!-- Botões à direita -->
    <div :class="isAdmin ? ' w-full grid grid-cols-3 gap-x-4':'flex flex-col grid grid-cols-1 gap-y-4'">
      <button
          @click="goToTransactions"
          :class="isAdmin ? 'py-2 px-4 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300'
           : 'py-2 px-4 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300' "
      >
        Transaction List
      </button>
      <button v-if="isAdmin"
          @click="goToHistory"
          class="py-2 px-4 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300"
      >
        Game List
      </button>
      <button v-if="isAdmin"
          @click="goToUsers"
          class="py-2 px-4 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300"
      >
        User List
      </button>
      <button v-if="!isAdmin"
          @click="goToPayments"
          class="py-2 px-4 bg-blue-600 text-white font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300"
      >
        Comprar Brain Coins
      </button>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/components/ui/toast/use-toast';
import { ToastAction } from '@/components/ui/toast';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from "axios";
import { useRouter } from 'vue-router';


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

    const goToHistory = () => {
      router.push('/history');
    };

    const goToUsers = () => {
      router.push('/users');
    };

    const isAdmin = authStore.isAdmin;

    let intervalId = null;
    onMounted(() => {
      updateBrainCoins();
      intervalId = setInterval(updateBrainCoins, 60000);
    });

    onUnmounted(() => {
      if (intervalId) clearInterval(intervalId);
    });

</script>

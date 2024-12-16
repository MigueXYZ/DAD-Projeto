<template>
  <div class="p-4 bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="space-y-6 w-full max-w-md bg-white p-6 rounded-lg shadow-lg">
      <h1 class="text-xl font-bold text-center">Comprar Brain Coins</h1>

      <!-- Seleção de valor -->
      <div>
        <label for="amount" class="block text-sm font-medium text-gray-700">Valor em euros (€)</label>
        <input
            id="amount"
            type="range"
            v-model="euros"
            min="1"
            max="50"
            step="1"
            class="mt-2 w-full accent-indigo-500"
        />
        <p class="mt-2 text-sm text-gray-600 text-center">
          <span class="text-indigo-600 font-medium">{{ euros }}€</span> =
          <span class="text-green-600 font-medium">{{ brainCoins }} Brain Coins</span>
        </p>
      </div>

      <!-- Método de pagamento -->
      <div>
        <label for="paymentMethod" class="block text-sm font-medium text-gray-700">Método de Pagamento</label>
        <select
            id="paymentMethod"
            v-model="paymentMethod"
            class="my-1 p-2 border p w-full md:w-auto rounded-sm bg-white"
        >
          <option value="MBWAY">MBWay</option>
          <option value="PAYPAL">PayPal</option>
          <option value="IBAN">IBAN</option>
          <option value="MB">Multibanco</option>
          <option value="VISA">VISA</option>
        </select>
      </div>

      <!-- Campo de referência -->
      <div>
        <label for="reference" class="block text-sm font-medium text-gray-700">Referência</label>
        <input
            id="reference"
            type="text"
            v-model="reference"
            placeholder="Insira a referência"
            class="mt-2 p-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>

      <!-- Botão de compra -->
      <button
          @click="processPurchase"
          :disabled="!canPurchase"
          class="w-full py-2 px-4 bg-indigo-600 text-white font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Comprar {{ brainCoins }} Brain Coins
      </button>

      <!-- Feedback -->
      <p v-if="error" class="text-sm text-red-500 mt-2">{{ error }}</p>
      <p v-if="success" class="text-sm text-green-500 mt-2">Compra realizada com sucesso!</p>
    </div>
  </div>
</template>


<script setup>
import { ref, computed } from "vue";
import axios from "axios";
//import toast
import { useToast } from '@/components/ui/toast/use-toast';
import { ToastAction } from '@/components/ui/toast';
import { useAuthStore } from '@/stores/auth';

const euros = ref(1);
const paymentMethod = ref("MBWAY");
const reference = ref("");
const error = ref("");
const success = ref(false);

const { toast } = useToast();
const authStore = useAuthStore();

// Computed para calcular Brain Coins
const brainCoins = computed(() => euros.value * 10);

// Verifica se os campos estão válidos
const canPurchase = computed(() => euros.value > 0 && reference.value.trim() !== "");

const processPurchase = async () => {
  if (!canPurchase.value) {
    toast({description: 'Por favor, preencha todos os campos.', variant: 'destructive'});
  }

  error.value = "";

  //send a request to the payment gateway at https://dad-202425-payments-api.vercel.app/api/debit
  //with the following payload:
  // {
  //   value: euros.value,
  //   type: paymentMethod.value,
  //   reference: reference.value
  // }
  // parse euros to number

  let response;

  try{
    response = await axios.post("https://dad-202425-payments-api.vercel.app/api/debit", {
      value: Number(euros.value),
      type: paymentMethod.value,
      reference: reference.value
    });
  }catch (error){
    toast({description: 'Erro ao processar a compra.', variant: 'destructive'});
    return;
  }

  if (response.status === 201) {
    success.value = true;
    toast({description: 'Compra realizada com sucesso!'});
    //send a request to the backend to update the user's brain coins balance
    //with the following payload:
    // {
    //   transaction_datetime: new Date().toISOString(),
    //   type: "P",
    //   euros: euros.value,
    //   brain_coins: brainCoins.value,
    //   payment_type: paymentMethod.value,
    //   reference: reference.value
    //   user_id: authStore.user.id
    // }

    response = await axios.post("/transactions", {
      transaction_datetime: new Date().toISOString(),
      type: "P",
      euros: euros.value,
      brain_coins: brainCoins.value,
      payment_type: paymentMethod.value,
      reference: reference.value,
      user_id: authStore.user.id
    });

    if (response.status === 201) {
      //update the user's brain coins balance
      authStore.user.brain_coins_balance += brainCoins.value;
    } else {
      toast({description: 'Erro ao processar a compra.', variant: 'destructive'});
    }

  } else {
    toast({description: 'Erro ao processar a compra.', variant: 'destructive'});
  }

  console.log("Compra realizada:", {
    euros: euros.value,
    brainCoins: brainCoins.value,
    paymentMethod: paymentMethod.value,
    reference: reference.value,
  });
};
</script>

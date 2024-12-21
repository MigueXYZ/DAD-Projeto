<template>
  <div class="flex flex-col items-center justify-center">
    <div class="grid-cols-1 grid text-center py-2">
      <h1>Total Transactions: {{ transactionsTotal }}</h1>
    </div>

    <!-- Container for the pie charts -->
    <div class="flex space-x-4">
      <!-- Render Transactions Per Payment Method in a Pie Chart -->
      <div v-if="transactionsPerPaymentMethod.length > 0" class="w-12 text-center">
        <h2 class="font-bold">Transactions Per Payment Method</h2>
        <Pie :data="transactionsPerPaymentMethodChartData" :options="chartOptions" />
      </div>
      <p v-else>No data available for transactions per Payment Method.</p>

      <!-- Render Transactions Per Type in a Pie Chart -->
      <div v-if="transactionsPerType.length > 0" class="w-12 text-center">
        <h2 class="font-bold">Transactions Per Type</h2>
        <Pie :data="transactionsPerTypeChartData" :options="chartOptions" />
      </div>
      <p v-else>No data available for transactions per Type.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Pie } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale } from 'chart.js';

// Register necessary chart components
ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale);

const transactionsTotal = ref(0);
const transactionsPerPaymentMethod = ref([]);
const transactionsPerType = ref([]);

const fetchTransactionsTotal = async () => {
  const response = await axios.get('/transactions/total');
  transactionsTotal.value = response.data[0].total_transactions;
};

const fetchTransactionsPerPaymentMethod = async () => {
  const response = await axios.get('/transactions/total/payment_method');
  transactionsPerPaymentMethod.value = response.data;
};

const fetchTransactionsPerType = async () => {
  const response = await axios.get('/transactions/total/type');
  transactionsPerType.value = response.data;
};

onMounted(() => {
  fetchTransactionsTotal();
  fetchTransactionsPerPaymentMethod();
  fetchTransactionsPerType();
});

// Pie chart options
const chartOptions = {
  responsive: true,
  plugins: {
    title: {
      display: true,
      text: 'Transactions Distribution'
    },
    tooltip: {
      callbacks: {
        label: (tooltipItem) => {
          return `${tooltipItem.label}: ${tooltipItem.raw} transactions`;
        }
      }
    }
  }
};

// Pie chart data for Transactions Per Payment Method
const transactionsPerPaymentMethodChartData = computed(() => {
  return {
    labels: transactionsPerPaymentMethod.value.map(paymentMethod => paymentMethod.payment_type),
    datasets: [{
      data: transactionsPerPaymentMethod.value.map(paymentMethod => paymentMethod.total_transactions),
      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#FF5733'], // Colors for each segment
      hoverOffset: 4
    }]
  };
});

// Pie chart data for Transactions Per Type
const transactionsPerTypeChartData = computed(() => {
  return {
    labels: transactionsPerType.value.map(transactionType => transactionType.type ==='P' ? 'Purchase' : transactionType.type==='B' ? 'Bonus' : 'Internal'),
    datasets: [{
      data: transactionsPerType.value.map(transactionType => transactionType.total_transactions),
      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#FF5733'], // Colors for each segment
      hoverOffset: 4
    }]
  };
});
</script>

<style scoped>
/* Optional: Add custom styling for your pie charts */
.w-12 {
  width: 48%;
  margin-bottom: 2rem;
}

.flex {
  display: flex;
}

.space-x-4 {
  gap: 16px;
}
</style>

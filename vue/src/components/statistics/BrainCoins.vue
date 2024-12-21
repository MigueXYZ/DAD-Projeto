<template>
  <div class="flex flex-col items-center justify-center">
    <!-- Dropdowns para selecionar duração e tipo -->
    <div class="grid-cols-1 grid text-center py-2">
      <p class="text-lg">Total Brain Coins: {{ totalBrainCoins }}</p>
      <p class="text-lg">Total Active Brain Coins: {{ totalActiveBrainCoins }}</p>
    </div>
    <div class="flex justify-center gap-5 mb-5">
      <!-- Dropdown de Duração, com opções baseadas no tipo selecionado -->
      <select v-if="selectedType === 'day'" v-model="selectedDuration" @change="fetchBrainCoinsPerDay" class="p-2 text-lg">
        <option value="7">7</option>
        <option value="30">30</option>
        <option value="90">90</option>
      </select>
      <select v-if="selectedType === 'month'" v-model="selectedDuration" @change="fetchBrainCoinsPerDay" class="p-2 text-lg">
        <option value="6">6</option>
        <option value="12">12</option>
      </select>

      <!-- Dropdown para selecionar o tipo (Dia/Mês) -->
      <select v-model="selectedType" @change="onTypeChange" class="p-2 text-lg">
        <option value="day">Days</option>
        <option value="month">Months</option>
      </select>
    </div>

    <!-- Exibir gráfico se os dados estiverem disponíveis -->
    <div class="w-full h-96"> <!-- Altura fixa para o gráfico -->
      <Line v-if="chartData && chartData.labels && chartData.labels.length > 0" :data="chartData" :options="chartOptions" class="w-full" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Line } from 'vue-chartjs'; // Importar gráfico Line do vue-chartjs
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement } from 'chart.js'; // Importar componentes necessários do Chart.js
import axios from 'axios';

// Registrar os componentes do Chart.js
ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement);

const chartData = ref();
const selectedDuration = ref(7); // Duração padrão de 7 dias
const selectedType = ref('day'); // Tipo padrão (Dia)
const previousType = ref('day'); // Armazena o valor anterior de selectedType
const totalBrainCoins = ref(0);
const totalActiveBrainCoins = ref(0);

const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    title: {
      display: true,
      text: 'Brain Coins',
    },
  },
  scales: {
    x: {
      type: 'category',
      title: {
        display: true,
        text: selectedType.value === 'day' ? 'Date' : 'Month',
      },
    },
    y: {
      type: 'linear',
      title: {
        display: true,
        text: 'Brain Coins',
      },
      ticks: {
        beginAtZero: true,
      }
    },
  },
});

// Função para buscar os dados do gráfico
const fetchBrainCoinsPerDay = async () => {
  try {
    const response = await axios.get("/brain_coins", {
      params: {
        type: selectedType.value,  // 'day' ou 'month'
        duration: selectedDuration.value,
      }
    });
    const data = response.data;

    // Formatar os dados para exibição no gráfico
    if (selectedType.value === 'month') {
      // Para o tipo 'month', formatar o mês como nome
      chartData.value = {
        labels: data.map(item => getMonthName(item.month)), // Eixo X (Meses)
        datasets: [{
          label: 'Brain Coins',
          data: data.map(item => item.brain_coins), // Eixo Y (Valores)
          borderColor: '#2562e9',
          backgroundColor: '#fdfdfd',
          tension: 0.1,
        }],
      };
    } else {
      // Para o tipo 'day', usar as datas (já está implementado)
      chartData.value = {
        labels: data.map(item => item.date), // Eixo X (Data)
        datasets: [{
          label: 'Brain Coins',
          data: data.map(item => item.brain_coins), // Eixo Y (Valores)
          borderColor: '#2562e9',
          backgroundColor: '#fdfdfd',
          tension: 0.1,
        }],
      };
    }
  } catch (error) {
    console.error('Error fetching brain coins data:', error);
  }
};

// Função para obter o nome do mês
const getMonthName = (monthNumber) => {
  const months = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
  ];
  return months[monthNumber - 1]; // Ajuste para começar do mês 1
}

// Função chamada quando o tipo é alterado
const onTypeChange = () => {
  if (selectedType.value === 'month' && previousType.value === 'day') {
    // Se o tipo mudou de "day" para "month", atualizamos a duração para um valor compatível
    selectedDuration.value = 6; // ou 12, dependendo do que você deseja
  } else if (selectedType.value === 'day' && previousType.value === 'month') {
    // Se o tipo mudou de "month" para "day", ajustamos para o valor mais comum, como 7
    selectedDuration.value = 7;
  }

  // Armazenar o valor anterior de selectedType
  previousType.value = selectedType.value;

  // Buscar os dados após a mudança
  fetchBrainCoinsPerDay();
};

const fetchBrainCoinsTotal = async () => {
  const response = await axios.get("/brain_coins", {
    params: {
      type: 'total',
    }
  });
  totalBrainCoins.value = response.data[0].brain_coins;
}

const fetchBrainCoinsActive = async () => {
  const response = await axios.get("/brain_coins/active");
  totalActiveBrainCoins.value = response.data.brain_coins;
}



// Buscar dados quando o componente for montado
onMounted(() => {
  fetchBrainCoinsPerDay();
  fetchBrainCoinsTotal();
  fetchBrainCoinsActive();
});
</script>

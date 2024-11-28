<template>
  <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">


    <main class="flex-1 w-full max-w-md px-4 py-8">
      <h2 class="text-xl text-gray-900 font-bold text-center mb-4">Login</h2>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-4 bg-white p-6 rounded-lg shadow-md">
        <div class="form-group">
          <label for="email" class="block text-gray-700">Email</label>
          <input
              type="email"
              id="email"
              v-model="credentials.email"
              required
              class="w-full p-3 border border-gray-300 text-gray-900 rounded-md"
              placeholder="Enter your email"
              :class="{ 'border-red-500': !isValidEmail }"
              @blur="validateEmail"
          />
          <p v-if="!isValidEmail" class="text-red-500 text-sm">Please enter a valid email.</p>
        </div>

        <div class="form-group">
          <label for="password" class="block text-gray-700">Password</label>
          <input
              type="password"
              id="password"
              v-model="credentials.password"
              required
              class="w-full p-3 border border-gray-300 text-gray-900 rounded-md"
              placeholder="Enter your password"
          />
        </div>

        <button
            type="submit"
            class="w-full py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200"
        >
          Login
        </button>

        <p v-if="errorMessage" class="text-center text-red-500 mt-3">{{ errorMessage }}</p>

        <!-- Play Anyways Button -->
        <button
            type="button"
            @click="playAsGuest"
            class="w-full py-3 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition duration-200 mt-4"
        >
          Play Anyways
        </button>
      </form>
    </main>
  </div>
</template>

<script>
import { useAuthStore } from '@/stores/auth'; // Importa a store de autenticação

export default {
  data() {
    return {
      credentials: {
        email: '',
        password: '',
      },
      isValidEmail: true,
      errorMessage: '',
    };
  },
  methods: {
    validateEmail() {
      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      this.isValidEmail = emailPattern.test(this.credentials.email);
    },
    async handleLogin() {
      if (!this.isValidEmail) {
        this.errorMessage = "Please enter a valid email.";
        return;
      }

      // Check if credentials are filled
      if (!this.credentials.email || !this.credentials.password) {
        this.errorMessage = "Email and password are required.";
        return;
      }

      const authStore = useAuthStore(); // Acessa a store de autenticação

      try {
        // Chama a função de login da store
        await authStore.login(this.credentials);

        // Se o login for bem-sucedido, redireciona para o dashboard
        this.$router.push({name: 'dashboard'});
      } catch (e) {
        console.error('Login failed:', e);
        this.errorMessage = e.response?.data?.message || 'Login failed. Please try again.';
      }
    },
    playAsGuest() {
      this.$router.push('/size');
    },
  },
};
</script>


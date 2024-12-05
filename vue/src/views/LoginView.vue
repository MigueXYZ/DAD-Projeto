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

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth'; // Import authentication store
import { useRouter } from 'vue-router';


const credentials = ref({
  email: '',
  password: '',
}); // Reactive object for credentials
const isValidEmail = ref(true); // Reactive variable for email validation state
const errorMessage = ref(''); // Reactive variable for error messages
const authStore = useAuthStore(); // Access authentication store
const router = useRouter(); // Access router instance

// Validate email format
const validateEmail = () => {
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  isValidEmail.value = emailPattern.test(credentials.value.email);
};

// Handle login
const handleLogin = async () => {
  validateEmail(); // Validate email before proceeding

  if (!isValidEmail.value) {
    errorMessage.value = 'Please enter a valid email.';
    return;
  }

  if (!credentials.value.email || !credentials.value.password) {
    errorMessage.value = 'Email and password are required.';
    return;
  }

  try {
    // Call login function from the auth store
    await authStore.login(credentials.value);

    // Redirect to dashboard if login is successful
    await router.push({ name: 'dashboard' });
  } catch (e) {
    console.error('Login failed:', e);
    errorMessage.value =
        e.response?.data?.message || 'Login failed. Please try again.';
  }
};

// Navigate to play as a guest
const playAsGuest = () => {
  authStore.anonymousLogin(); // Chama o método de login anônimo
  router.push('/dashboard');
};


</script>

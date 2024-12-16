<template>
  <div class="p-4 bg-gray-100 min-h-screen">
    <h1 class="text-xl font-bold mb-4">Register</h1>

    <div v-if="error" class="text-sm text-red-500 mb-4">{{ error }}</div>
    <div v-if="success" class="text-sm text-green-500 mb-4">{{ success }}</div>

    <form @submit.prevent="register" class="space-y-4">
      <!-- Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name:</label>
        <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3"
        />
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
        <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3"
        />
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
        <input
            id="password"
            v-model="form.password"
            type="password"
            required
            minlength="6"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3"
        />
      </div>

      <!-- Confirm Password -->
      <div>
        <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password:</label>
        <input
            id="confirmPassword"
            v-model="form.confirmPassword"
            type="password"
            required
            minlength="6"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3"
        />
      </div>

      <!-- Nickname -->
      <div>
        <label for="nickname" class="block text-sm font-medium text-gray-700">Nickname:</label>
        <input
            id="nickname"
            v-model="form.nickname"
            type="text"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3"
        />
      </div>

      <!-- Avatar Upload -->
      <div>
        <label for="photo_filename" class="block text-sm font-medium text-gray-700">Avatar:</label>
        <input
            id="photo_filename"
            type="file"
            accept="image/*"
            @change="handlePhotoChange"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm py-3"
        />
      </div>

      <!-- Avatar Preview -->
      <div class="text-center">
        <img
            :src="photoPreview"
            alt="Avatar Preview"
            class="w-20 h-20 rounded-full mx-auto"
        />
      </div>

      <button
          type="submit"
          class="w-full py-2 px-4 bg-indigo-600 text-white font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Register
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useToast } from '@/components/ui/toast/use-toast';

const router = useRouter();
const { toast } = useToast();

const form = ref({
  name: '',
  email: '',
  password: '',
  confirmPassword: '',
  nickname: '',
  photo_filename: '',
});

const photoPreview = ref('');
const error = ref('');
const success = ref('');

const handlePhotoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onloadend = () => {
      photoPreview.value = reader.result;
    };
    reader.readAsDataURL(file);
    form.value.photo_filename = file;
  }
};

const register = async () => {
  try {
    if (form.value.password !== form.value.confirmPassword) {
      error.value = 'Passwords do not match.';
      toast({ description: error.value, variant: 'destructive' });
      return;
    }

    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('email', form.value.email);
    formData.append('password', form.value.password);
    formData.append('nickname', form.value.nickname);

    // Adiciona o tipo ao formulário
    formData.append('type', 'P');

    if (form.value.photo_filename) {
      const avatarData = new FormData();
      avatarData.append('photo_filename', form.value.photo_filename); // Apenas a foto é enviada aqui

      const photoResponse = await axios.post('/upload-avatar', avatarData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      formData.append('photo_filename', photoResponse.data.filename); // Insere o nome do arquivo retornado
    }

    const response = await axios.post('/users', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    success.value = 'Registration successful!';
    toast({ description: success.value });

    // Redireciona após sucesso
    router.push('/login');
  } catch (err) {
    error.value = err.response?.data?.message || 'Registration failed.';
    toast({ description: error.value, variant: 'destructive' });
  }
};

</script>

<style scoped>
/* Add custom styles if needed */
</style>

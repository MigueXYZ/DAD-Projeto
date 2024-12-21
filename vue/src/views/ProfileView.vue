<template>
  <div class="p-4 bg-gray-100 min-h-screen">
    <h1 class="text-xl font-bold mb-4">User Profile</h1>

    <div v-if="isLoading" class="text-center text-gray-500">Loading...</div>

    <div v-else class="space-y-4">
      <div>
        <label for="nickname" class="block text-sm font-medium text-gray-700">Nickname:</label>
        <input
            id="nickname"
            v-model="profile.nickname"
            type="text"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3"
        />
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
        <input
            id="email"
            v-model="profile.email"
            type="email"
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm sm:text-sm py-3"
        />
      </div>

      <!-- Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name:</label>
        <input
            id="name"
            v-model="profile.name"
            type="text"
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm sm:text-sm py-3"
        />
      </div>

      <!-- Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
        <input
            id="password"
            v-model="profile.password"
            type="password"
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm sm:text-sm py-3"
        />
      </div>

      <!-- Confirm Password -->
      <div>
        <label for="confirm-password" class="block text-sm font-medium text-gray-700">Confirm Password:</label>
        <input
            id="confirm-password"
            v-model="profile.confirmPassword"
            type="password"
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm sm:text-sm py-3"
        />
      </div>

      <!-- photo_filename Upload -->
      <div>
        <label for="photo_filename" class="block text-sm font-medium text-gray-700">Avatar:</label>
        <input
            id="photo_filename"
            type="file"
            accept="image/*"
            @change="handlephoto_filenameChange"
            class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm sm:text-sm py-3"
        />
      </div>

      <!-- photo_filename Image Preview -->
      <div class="text-center">
        <img
            :src="photo_filenamePreview"
            alt="photo_filename Preview"
            class="w-20 h-20 rounded-full mx-auto"
        />
      </div>

      <button
          @click="updateProfile"
          class="w-full py-2 px-4 bg-indigo-600 text-white font-medium rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        Save Changes
      </button>

      <p v-if="error" class="text-sm text-red-500 mt-2">{{ error }}</p>
      <p v-if="success" class="text-sm text-green-500 mt-2">Profile updated successfully!</p>

      <hr class="my-4">

      <!-- Toggle that changes the user's notifications preference -->

      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700">Notifications</span>
        <input
            id="notifications"
            type="checkbox"
            v-model="authStore.notifications"
            class="form-checkbox h-5 w-5 text-indigo-600 transition duration-150 ease-in-out"
        />
      </div>

      <button
          @click="logout"
          class="w-full py-2 px-4 bg-red-600 text-white font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
      >
        Log Out
      </button>

      <div
          v-if="!isAdmin"
          class="mt-4">
        <label for="confirm-delete" class="block text-sm font-medium text-gray-700">Type "DELETE" to remove your account:</label>
        <input

            id="confirm-delete"
            v-model="deleteConfirmation"
            type="text"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm py-3"
        />
      </div>

      <button
          v-if="!isAdmin"
          @click="deleteAccount"
          :disabled="deleteConfirmation !== 'DELETE'"
          class="w-full py-2 px-4 bg-red-600 text-white font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Delete Account
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import { useToast } from '@/components/ui/toast/use-toast';
import { useRouter } from 'vue-router';

import photo_filenameNone from '@/assets/avatar-none.png';

const authStore = useAuthStore();
const router = useRouter();
const { toast } = useToast();

const profile = ref({
  nickname: '',
  email: '',
  name: '',
  password: '',
  confirmPassword: '',
  photo_filename: '',
  photo_filename_src: '',
});

const photo_filenamePreview = ref(photo_filenameNone);
const deleteConfirmation = ref('');
const error = ref('');
const success = ref(false);
const isLoading = ref(true);

// Fetch profile using authStore
const fetchProfile = async () => {
  try {
    const apiDomain = import.meta.env.VITE_API_DOMAIN;
    isLoading.value = true;
    const user = authStore.user;
    if (user) {
      profile.value = {
        nickname: user.nickname || '',
        email: user.email || '',
        name: user.name || '',
        photo_filename: user.photo_filename || '',
        photo_filename_src: '',
      };
      if (profile.value.photo_filename) {
        profile.value.photo_filename_src = `http://${apiDomain}/storage/photos/${profile.value.photo_filename}`;
        photo_filenamePreview.value = profile.value.photo_filename_src;
      } else {
        photo_filenamePreview.value = photo_filenameNone;
      }
    }
  } catch (err) {
    console.error('Error fetching profile:', err);
    toast({
      description: 'Failed to fetch profile data.',
    });
  } finally {
    isLoading.value = false;
  }
};

// Handle photo_filename file change
const handlephoto_filenameChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onloadend = () => {
      photo_filenamePreview.value = reader.result;
    };
    reader.readAsDataURL(file);
    profile.value.photo_filename = file; // Store the file for upload
  }
};

// Update profile method
const updateProfile = async () => {
  try {
    const updatedProfile = { ...profile.value };

    // Remove the photo_filename preview (it's not needed for submission)
    delete updatedProfile.photo_filename_src;

    // Check if the password and confirm password match
    if (updatedProfile.password !== updatedProfile.confirmPassword) {
      error.value = 'Passwords do not match.';
      toast({
        description: error.value,
        variant: 'destructive',
      });
      return;
    }

    // Remove the confirm password property before sending the request
    delete updatedProfile.confirmPassword;

    // Handle photo_filename upload if photo_filename has changed
    if (profile.value.photo_filename && profile.value.photo_filename !== profile.value.photo_filename_src) {
      const formData = new FormData();
      formData.append('photo_filename', profile.value.photo_filename);

      const uploadResponse = await axios.post('/upload-avatar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      updatedProfile.photo_filename = uploadResponse.data.filename;
      console.log('photo_filename uploaded:', updatedProfile.photo_filename);
    }


    // Now update the profile data (this will send both updated profile data and photo_filename filename if it exists)
    const response = await axios.patch('/users/me', updatedProfile);
    authStore.setUser(response.data.data);

    success.value = true;
    toast({
      description: 'Profile updated successfully!',
    });
  } catch (err) {
    error.value = 'Failed to update profile.';
    toast({
      description: err.response?.data?.message || error.value,
      variant: 'destructive',
    });
  }
};


// Log out method
const logout = async () => {
  try {
    await authStore.logout();
    await router.push('/login');
  } catch (err) {
    console.error('Error logging out:', err);
  }
};

// Delete account method
const deleteAccount = async () => {
  try {
    await authStore.deleteMyAccount();
    await router.push('/login');
  } catch (err) {
    console.error('Error deleting account:', err);
  }
};

// Check if the user is an admin
const isAdmin = authStore.isAdmin;

onMounted(() => {
  console.log((authStore.user.type));
  fetchProfile();
});
</script>

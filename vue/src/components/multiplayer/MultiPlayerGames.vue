<script setup>
import Lobby from './Lobby.vue'
import Games from './Games.vue'
import Chat from '@/components/chat/Chat.vue'
import { useAuthStore } from '@/stores/auth'
import { onMounted } from 'vue'

const storeAuth = useAuthStore()
</script>
<template>
  <div class="flex flex-wrap justify-start space-x-4">
    <Chat class="w-72"></Chat>
    <Lobby v-if="storeAuth.isLoggedIn" class="w-72"></Lobby>
    <Games v-if="storeAuth.isLoggedIn" class="flex-1 min-w-96"></Games>
  </div>
</template>
<template>
  <div>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <div class="bg-white p-6 mt-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
        <h1 class="text-center text-xl font-bold">Total Users</h1>
        <p class="text-center text-4xl font-bold text-blue-500">{{ totalUsers }}</p>
      </div>
      <div class="bg-white p-6 mt-8 rounded-lg shadow-md hover:shadow-lg transition duration-300">
        <h1 class="text-center text-xl font-bold">Total Active Users</h1>
        <p class="text-center text-4xl font-bold text-green-500">{{ totalActiveUsers }}</p>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import axios  from "axios";

const totalUsers = ref(0)
const totalActiveUsers = ref(0)

const fetchTotalUsers = async () => {
  const response = await axios.get("/users/total")
  totalUsers.value = response.data.num_users
}

const fetchTotalActiveUsers = async () => {
  const response = await axios.get("/users/total/active")
  totalActiveUsers.value = response.data.num_users
}

onMounted(()=>{
  fetchTotalUsers()
  fetchTotalActiveUsers()
})
</script>
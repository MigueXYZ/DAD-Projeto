<script setup>
import { ref, onMounted } from 'vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import ListGamesLobby from './ListGamesLobby.vue'
import { useLobbyStore } from '@/stores/lobby'
import { useAuthStore } from "@/stores/auth.js"
import SelectDialog from '@/components/SelectDialog.vue'
import axios from 'axios'

const storeLobby = useLobbyStore()
const storeAuth = useAuthStore()
const boards = ref([])  // Holds the list of boards fetched from the server
const selectedBoard = ref(null) // Stores the selected board
const isDialogOpen = ref(false)  // Controls if the dialog is open

// Fetch the boards when the user clicks "New Game"
const addGame = async () => {
  const response = await axios.get('/boards')
  boards.value = response.data.data
  await storeLobby.addGame(boards.value[0]);
}


onMounted(() => {
  storeLobby.fetchGames()
  console.log('storeLobby.games', storeLobby.games)
})

</script>

<template>
  <Card class="my-8 py-2 px-1">
    <CardHeader class="pb-0">
      <CardTitle>Lobby</CardTitle>
      <CardDescription>{{ storeLobby.totalGames == 1 ? '1 game' : storeLobby.totalGames + ' games'}} waiting.</CardDescription>
    </CardHeader>
    <CardContent class="p-4">
      <div class="py-2">
        <Button v-if="storeAuth.user.brain_coins_balance > 5" @click="addGame">
          New Game
        </Button>
      </div>
      <div v-if="storeLobby.totalGames > 0">
        <span>Eu estou a ficar maluco</span>
        <ListGamesLobby/>
      </div>
      <div v-else>
        <h2 class="text-xl">The lobby is empty!</h2>
      </div>
    </CardContent>
  </Card>
</template>

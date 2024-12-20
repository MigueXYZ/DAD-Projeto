<script setup>
import { inject, onMounted, ref, computed } from 'vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import Board from '@/components/multiplayer/Board.vue'
import { useAuthStore } from '@/stores/auth'
import { useGameStore } from '@/stores/game'

// Stores
const storeGames = useGameStore()
const storeAuth = useAuthStore()

// Props
const props = defineProps({
  game: {
    type: Object,
    required: true
  }
})

const alertDialog = inject('alertDialog')

// Computed Properties
const opponentName = computed(() => {
  return storeGames.playerNumberOfCurrentUser(props.game) === 1
      ? storeAuth.getFirstLastName(props.game.player2.nickname)
      : storeAuth.getFirstLastName(props.game.player1.nickname)
})

const gameEnded = computed(() => props.game.gameStatus > 0)

const currentUserTurn = computed(() => {
  if (gameEnded.value) return false
  return props.game.currentPlayer === storeGames.playerNumberOfCurrentUser(props.game)
})

const cardBgColor = computed(() => {
  switch (props.game.gameStatus) {
    case null:
    case 0:
      return 'bg-white'
    case 1:
    case 2:
      return storeGames.playerNumberOfCurrentUser(props.game) === props.game.gameStatus
          ? 'bg-green-100'
          : 'bg-red-100'
    case 3:
      return 'bg-blue-100'
    default:
      return 'bg-slate-100'
  }
})

const statusMessageColor = computed(() => {
  switch (props.game.gameStatus) {
    case null:
    case 0:
      return currentUserTurn.value ? 'text-green-400' : 'text-slate-400'
    case 1:
    case 2:
      return storeGames.playerNumberOfCurrentUser(props.game) === props.game.gameStatus
          ? 'text-green-900'
          : 'text-red-900'
    case 3:
      return 'text-blue-900'
    default:
      return 'text-slate-800'
  }
})


const buttonClasses = computed(() => {
  return gameEnded.value
      ? 'bg-gray-700 text-gray-200 hover:text-gray-50'
      : 'bg-gray-300 text-gray-700 hover:text-gray-200'
})

const statusGameMessage = computed(() => {
  console.log('Game: ', props.game)
  switch (props.game.gameStatus) {
    case null:
    case 0:
      return currentUserTurn.value ? 'Your turn' : 'Opponent turn'
    case 1:
    case 2:
      console.log('Status ',storeGames.playerNumberOfCurrentUser(props.game), props.game.gameStatus)
      return storeGames.playerNumberOfCurrentUser(props.game) === props.game.gameStatus
          ? 'You won'
          : 'You lost'
    case 3:
      return 'Draw'
    default:
      return 'Not started!'
  }
})

// Methods
const playPieceOfBoard = (idx1, idx2) => {
  storeGames.play(props.game, idx1, idx2)
}

const clickCardButton = () => {
  if (gameEnded.value) {
    close()
  } else {
    alertDialog.value.open(
        quit,
        cancel,
        'Quit game',
        `Yes, I want to quit`,
        'Cancel',
        `Are you sure you want to quit the game #${props.game.id}? You'll lose the game!`
    )
  }
}

const close = () => {
  storeGames.close(props.game)
}

const cancel = () => {
}

const quit = () => {
  storeGames.quit(props.game)
}

</script>

<template>
  <Card class="relative grow mx-4 mt-8 pt-2 pb-4 px-1" :class="cardBgColor">
    <CardHeader class="pb-0">
      <Button @click="clickCardButton" class="absolute top-4 right-4" :class="buttonClasses">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
        {{ gameEnded ? 'Close' : 'Quit' }}
      </Button>
      <CardTitle>#{{ game.id }}</CardTitle>
      <CardDescription>
        <div class="text-base">
          <span class="font-bold">Opponent:</span> {{ opponentName }}
          {{ game.status === 'I' ? ' / Interrupted' : '' }}
        </div>
      </CardDescription>
    </CardHeader>
    <CardContent class="py-4 px-8">
      <h3 class="pt-0 text-2xl font-bold py-2" :class="statusMessageColor">
        {{ statusGameMessage }}
      </h3>
      <div>
        <Suspense v-if="game.player2">
          <template #default>
            <Board :game="game" @playPieceOfBoard="playPieceOfBoard" />
          </template>
          <template #fallback>
            <div>Loading...</div>
          </template>
        </Suspense>
      </div>
    </CardContent>
  </Card>
</template>

import {ref, computed, inject, reactive} from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import { useErrorStore } from '@/stores/error'
import { useAuthStore } from '@/stores/auth'
import { useGameStore } from '@/stores/game'
import {toast} from "@/components/ui/toast/index.js";


export const useLobbyStore = defineStore('lobby', () => {
    const storeAuth = useAuthStore()
    const storeError = useErrorStore()
    const socket = inject('socket')
    const authStore = useAuthStore()
    const gameStore = useGameStore()
    const games = ref([])
    const totalGames = computed(() => games.value.length)
    const loading = ref(true)

    const webSocketServerResponseHasError = (response) => {
        if (response.errorCode) {
            storeError.setErrorMessages(response.errorMessage, [], response.errorCode)
            return true
        }
        return false
    }

    // when the lobby changes on the server, it is updated on the client
    socket.on('lobbyChanged', async (lobbyGames) => {
        console.log('lobby games updated:', lobbyGames);
        games.value = [...new Map(lobbyGames.map((game) => [game.id, game])).values()]
        loading.value = false; // Ensure loading state is accurate

    })

    // fetch lobby games from the Websocket server
    const fetchGames = () => {
        storeError.resetMessages()
        loading.value = true // Start loading
        socket.emit('fetchGames', (response) => {
            if (webSocketServerResponseHasError(response)) {
                return
            }
            games.value = response
            loading.value = false // Stop loading after fetching games
        })
    }

    // add a game to the lobby
    const addGame = async (board) => {
        storeError.resetMessages()

        loading.value = true

        let newGame = {
            id: null,
            created_user_id: storeAuth.userId,
            type: 'M',
            status: 'PL',
            began_at: gameStore.getFormattedDate(),
            board_id: board.id,
            winner_user_id: null,
            ended_at: null,
            total_time: null,
            custom: null,
            total_turns_winner: null,
        }
            // Add the game to the DB
            const success = await  gameStore.createGame(newGame)

            if (!success) {
                toast({
                    description: 'Error creating game!',
                    variant: "destructive",
                })
            }

            //update the game with the new id
            newGame.id = gameStore.game.id

            games.value.push(newGame)
            // Add a transaction to the user's brain_coins
            await axios.post(`/users/me/brain_coins`, {
                user_id: authStore.user.id,
                transaction_datetime: new Date(),
                brain_coins: -5,
                type: 'I',
                game_id: gameStore.game.id,
            })

            toast({
                description: 'Game created! 5 brain_coins deducted!',
            })

            const board_size = board.board_cols * board.board_rows
            // Send the game to the server
            socket.emit('addGame', newGame.id, board_size, (response) => {
                if (response && response.errorCode) {
                    console.error('Error from server:', response.errorMessage)
                    storeError.setErrorMessages(response.errorMessage, [], response.errorCode)
                } else {
                    console.log('Game added successfully:', response)
                }
            })
    }


    // remove a game from the lobby
    const removeGame = async (id) => {
        storeError.resetMessages()

        loading.value = true

        socket.emit('removeGame', id, async (response) => {
            if (webSocketServerResponseHasError(response)) {
                return
            }
            await axios.post(`/users/me/brain_coins`, {
                user_id: authStore.user.id,
                transaction_datetime: new Date(),
                brain_coins: 5,
                type: 'I',
                game_id: id,
            })
            toast({
                description: 'Game removed! 5 brain_coins added!',
            });
        })
    }

    // join a game of the lobby
    const joinGame = async (id) => {
        storeError.resetMessages()
        console.log('join game:', id)
        socket.emit('joinGame', id, async (response) => {
            // callback executed after the join is complete
            if (webSocketServerResponseHasError(response)) {
                return
            }

            //fetch playing games
            gameStore.fetchPlayingGames();

            //send to the db the players who are playing said game
            const reply = await axios.post(`/games/${id}/players`, {
                player1_id: response.player1.id,
                player2_id: response.player2.id,
            })

            // Start the game
            const gameF = await gameStore.fetchGame(id)

            gameF.player1 = response.player1
            gameF.player2 = response.player2
            gameF.player1SocketId = response.player1SocketId
            gameF.player2SocketId = response.player2SocketId
            gameF.currentPlayer = 1
            gameF.gameStatus = 0
            gameF.board_size = response.board_size

            socket.emit('startGame', gameF, (startedGame) => {
                console.log('Game has started', startedGame)
            })
        })
    }

    // Whether the current user can remove a specific game from the lobby
    const canRemoveGame = (game) => {
        return game.player1?.id === storeAuth.userId
    }

    // Whether the current user can join a specific game from the lobby
    const canJoinGame = (game) => {
        return storeAuth.user && game.player1?.id !== storeAuth.userId
    }

    return {
        games, totalGames, fetchGames, addGame, joinGame, canJoinGame, removeGame, canRemoveGame, loading
    }
})

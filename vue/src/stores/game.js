import { defineStore } from 'pinia';
import { ref, computed, inject } from 'vue';
import axios from 'axios';
import { useAuthStore } from './auth';
import { useToast } from '@/components/ui/toast/use-toast';
import { useErrorStore } from '@/stores/error';

export const useGameStore = defineStore('game', () => {
    const authStore = useAuthStore();
    const errorStore = useErrorStore();
    const { toast } = useToast();
    const socket = inject('socket');

    const games = ref([]);
    const game = ref(null);

    const totalGames = computed(() => games.value.length);

    // Function to validate the date format
    const isValidDate = (dateStr) => {
        const dateRegex = /^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])\s([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/;
        if (!dateRegex.test(dateStr)) {
            return false;
        }

        const [datePart, timePart] = dateStr.split(' ');
        const [year, month, day] = datePart.split('-').map(Number);
        const [hours, minutes, seconds] = timePart.split(':').map(Number);

        const date = new Date(year, month - 1, day, hours, minutes, seconds);
        return (
            date.getFullYear() === year &&
            date.getMonth() === month - 1 &&
            date.getDate() === day &&
            date.getHours() === hours &&
            date.getMinutes() === minutes &&
            date.getSeconds() === seconds
        );
    };

    // Create a new game
    const createGame = async (gameData) => {
        if (!isValidDate(gameData.began_at)) {
            console.error('Invalid date format. Expected "YYYY-MM-DD HH:MM:SS".');
            return false;
        }

        const newGame = {
            id: null,
            created_user_id: authStore.userId,
            type: gameData.type,
            status: gameData.status,
            began_at: gameData.began_at,
            board_id: gameData.board_id,
            winner_user_id: null,
            ended_at: null,
            total_time: null,
            custom: gameData.custom || null,
            total_turns_winner: null,
        };

        try {
            const response = await axios.post('/games', newGame);
            newGame.id = response.data.data.id;
            game.value = JSON.parse(JSON.stringify(newGame));
            games.value.push(newGame);
            localStorage.setItem('game', JSON.stringify(game.value));
            return true;
        } catch (error) {
            console.error('Error creating game:', error.response?.data?.message || error.message);
            return false;
        }
    };

    const updateGame = (updatedGame) => {
        const index = games.value.findIndex((g) => g.id === updatedGame.id);
        if (index !== -1) {
            games.value[index] = { ...updatedGame };
        }
    };

    const fetchPlayingGames = async () => {
        errorStore.resetMessages();
        socket.emit('fetchPlayingGames', (response) => {
            if (response.errorCode) {
                errorStore.setErrorMessages(response.errorMessage, [], response.errorCode);
                return;
            }
            console.log('fetchPlayingGames response:', response);
            games.value = response;
        });
    };

    const calculateTotalTime = () => {
        const now = new Date();
        const beganAt = new Date(game.value.began_at);
        const diff = now - beganAt;
        const seconds = (diff / 1000).toFixed(2);
        game.value.total_time = parseFloat(seconds);
        game.value.ended_at = getFormattedDate();
    };

    const getFormattedDate = () => {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    };

    const playPieceOfBoard = (gameL, idx) => {
        errorStore.resetMessages();
        //send play to server
        socket.emit('play', {
            index: idx,
            gameId: gameL.id,
        },
        (response) => {
            if(response.errorCode){
                errorStore.setErrorMessages(response.errorMessage, [], response.errorCode);
                return;
            }
        });
        return;
    };

    const quit = (game) => {
        errorStore.resetMessages();
        socket.emit('quitGame', game.id, (response) => {
            if (response.errorCode) {
                errorStore.setErrorMessages(response.errorMessage, [], response.errorCode);
                toast({
                    title: 'Error quitting game',
                    description: response.errorMessage,
                    variant: 'destructive',
                })
                return;
            }
            games.value = games.value.filter((g) => g.id !== game.id);
        });
    };

    const deleteGame = async () => {
        if (!game.value) {
            console.warn('No game to delete.');
            return false;
        }
        try {
            await axios.delete(`/games/${game.value.id}`);
            games.value = games.value.filter((g) => g.id !== game.value.id);
            game.value = null;
            localStorage.removeItem('game');
            return true;
        } catch (error) {
            console.error('Error deleting game:', error.response?.data?.message || error.message);
            return false;
        }
    };

    socket.on('gameStarted', (newGame) => {
        console.log('Game started:', newGame);
        fetchPlayingGames();
        toast({
            title: 'Game Started',
            description: `Game #${newGame.id} has started!`,
        });

    });

    socket.on('gameEnded', (endedGame) => {
        console.log('Game ended:', endedGame);
        updateGame(endedGame);
        toast({
            title: 'Game Ended',
            description: `Game #${endedGame.id} has ended!`,
        });
    });

    socket.on('gameChanged', (changedGame) => {
        updateGame(changedGame);
    });

    const restoreGame = async function() {
        let storedGame = localStorage.getItem('game');
        if(storedGame){
            try{
                game.value = JSON.parse(storedGame);
                return true
            }
            catch{
                clearGame()
                return false
            }
        }
    }

    const clearGame = function(){
        //if logged in, delete from server
        if(authStore.isLoggedIn){
            try{
                if(game.value && game.value.status === 'P'){
                    axios.delete(`/games/${game.value.id}`)
                        .catch(error => {
                            console.error('Error deleting game:', error.response?.data?.message || error.message);
                            toast({
                                title: 'Error',
                                description: 'An error occurred while deleting the game.',
                                variant:'destructive',
                            })
                        })
                }
            }catch (error) {
                console.error('Error deleting game:', error.response?.data?.message || error.message);
                toast({
                    title: 'Error',
                    description: 'An error occurred while deleting the game.',
                    variant: 'destructive',
                })
            }
        }
        game.value = null
        localStorage.removeItem('game')
    }

    const updateGameDatabase = async function(){
        if(!game.value){
            console.warn('No game to update.');
            return false;
        }
        try{
            game.value.status='E'
            console.log('Update:'+game.value)

            await axios.patch(`/games/${game.value.id}`, game.value);

            const {data} = await axios.get(`/games/record/${game.value.id}`);
            console.log(data);
            if (data > 0){
                let tipo;
                let coins;
                switch(data) {
                    case 1:
                        tipo = 'Record Pessoal Batido!';
                        coins = 1;
                        break;
                    case 2:
                        tipo = 'Record Global Batido!';
                        coins = 1;
                        break;
                    case 3:
                        tipo = 'Record Pessoal e Global Batido!';
                        coins = 2;
                        break;
                    default:
                        tipo = 'Record Batido!';
                        coins = 1;
                        break;
                }
                console.log('Id do player: '+authStore.user.id);
                await axios.post('/users/me/brain_coins', {
                    user_id: authStore.user.id,
                    transaction_datetime: new Date(),
                    brain_coins: coins,
                    type:'I',
                    game_id: game.value.id,
                });
                toast({
                    description: tipo,
                })
                toast({
                    description: 'Você ganhou '+ coins + ' moedas!',
                })

            }

            return true;
        }
        catch(error){
            console.error('Error updating game:', error.response?.data?.message || error.message);
            return false;
        }
    }

    const playerNumberOfCurrentUser = (gam) => {
        console.log('PlayerNumberOfCurrentUser:', authStore.userId)
        if (gam.player1?.id === authStore.userId) {
            return 1
        }
        if (gam.player2?.id === authStore.userId) {
            return 2
        }
        return null
    }
    const webSocketServerResponseHasError = (response) => {
        if (response.errorCode) {
            errorStore.setErrorMessages(response.errorMessage, [], response.errorCode)
            return true
        }
        return false
    }

    const close = (game) => {
        errorStore.resetMessages()
        socket.emit('closeGame', game.id, (response) => {
            if (webSocketServerResponseHasError(response)) {
                return
            }
            removeGameFromList(game)
        })
    }

    const removeGameFromList = (game) => {
        games.value = games.value.filter((g) => g.id !== game.id)
    }

    const setGames = (newGames) => {
        games.value = newGames
    }

    const fetchGame = async (id) => {
        errorStore.resetMessages()
        try {
            const { data } = await axios.get(`/games/${id}`)
            return data.data
        } catch (error) {
            console.error('Error fetching game:', error.response?.data?.message || error.message)
            return null
        }
    }

    return {
        game,
        games,
        totalGames,
        createGame,
        setGames,
        clearGame,
        updateGame,
        deleteGame,
        fetchPlayingGames,
        updateGameDatabase,
        calculateTotalTime,
        getFormattedDate,
        restoreGame,
        playerNumberOfCurrentUser,
        fetchGame,
        playPieceOfBoard,
        close,
        quit,
    };
});

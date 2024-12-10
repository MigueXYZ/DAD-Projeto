import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from './auth';

export const useGameStore = defineStore('game', () => {
    const authStore = useAuthStore();
    const game = ref(null); // Armazena um único jogo

    // Função para validar o formato da data e a data em si
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

    const createGame = async (gameData) => {
        if (!isValidDate(gameData.began_at)) {
            console.error('Formato de data inválido. Deve ser "YYYY-MM-DD HH:MM:SS".');
            return false;
        }

        console.log('userIdValue:', authStore.userId);

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
        };

        if (authStore.isLoggedIn) {
            if (authStore.userId) {
                try {
                    const response = await axios.post('/games', newGame);
                    newGame.id = response.data.data.id; // Atualiza o ID com o fornecido pelo servidor
                    game.value = JSON.parse(JSON.stringify(newGame));
                    console.log("GAME", game.value);
                    console.log('Storing game:', JSON.stringify(game.value));
                    localStorage.setItem('game', JSON.stringify(game.value));
                    return true;
                } catch (error) {
                    console.error('Erro ao criar jogo:', error.response?.data?.message || error.message);
                    return false;
                }
            }
        } else {
            try {
                // Set newGame.id and assign to game.value before storing
                newGame.id = 0; // A placeholder ID
                game.value = JSON.parse(JSON.stringify(newGame)); // Properly set the game value
                console.log("GAME (offline)", game.value);
                localStorage.setItem('game', JSON.stringify(game.value)); // Now this won't break
                return true;
            } catch (error) {
                console.error('Erro ao criar jogo:', error.message);
                return false;
            }
        }

    };

    const calculateTotalTime = () => {
        const now = new Date();
        const beganAt = new Date(game.value.began_at);
        const diff = now - beganAt;
        const seconds = Math.floor(diff / 1000);
        game.value.total_time = seconds;
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
        const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;  // Ajuste no formato aqui
        return formattedDate;
    }

    //Update game
    const updateGame = async () => {
        // Atualiza o status do jogo
        game.value.status = 'E';
        console.log('Update '+ game.value);

        try {
            // Verifica se o ID do jogo está presente
            if (!game.value.id) {
                console.error('ID do jogo não encontrado');
                return false;
            }

            // Envia a requisição PUT para atualizar o jogo
            await axios.put(`/games/${game.value.id}`, game.value);

            // Retorna verdadeiro se a atualização for bem-sucedida
            return true;
        } catch (error) {
            // Exibe mensagem de erro, se houver
            console.error('Erro ao atualizar jogo:', error.response?.data?.message || error.message);
            return false;
        }
    };



    // Função para remover o jogo
    const deleteGame = async () => {
        if (!game.value) {
            console.warn('Nenhum jogo encontrado para excluir.');
            return false;
        }

        try {
            if (isUserLoggedIn.value) {
                await axios.delete(`/games/${game.value.id}`);
            }
            clearGame();
            return true;
        } catch (error) {
            console.error('Erro ao remover jogo:', error.response?.data?.message || error.message);
            return false;
        }
    };

    const gameStarted = computed(() => game.value?.began_at);

    const clearGame = () => {
        if (game.value) {
            game.value = null;
            localStorage.removeItem('game');
        }
    }

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

    return {
        game,
        gameStarted,
        calculateTotalTime,
        getFormattedDate,
        createGame,
        clearGame,
        updateGame,
        restoreGame,
        deleteGame,
    };
});

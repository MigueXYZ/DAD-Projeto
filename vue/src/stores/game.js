import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from './auth';

export const useGameStore = defineStore('game', () => {
    const authStore = useAuthStore();
    const isUserLoggedIn = computed(() => authStore.isLoggedIn); // Verifica se o utilizador está autenticado
    const game = ref(null); // Armazena um único jogo
    const userId = computed(() => authStore.userId); // Obtém o ID do utilizador autenticado

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
        if (!isUserLoggedIn.value) {
            console.error('Usuário não está autenticado');
            return false;
        }

        const userIdValue = userId.value;
        if (!userIdValue) {
            console.error('ID do usuário não encontrado');
            return false;
        }

        if (!isValidDate(gameData.began_at)) {
            console.error('Formato de data inválido. Deve ser "YYYY-MM-DD HH:MM:SS".');
            return false;
        }

        console.log('userIdValue:', userIdValue);

        const newGame = {
            id: null,
            created_user_id: userIdValue,
            type: gameData.type,
            status: gameData.status,
            began_at: gameData.began_at,
            board_id: gameData.board_id,
            winner_user_id: null,
            ended_at: null,
            total_time: null,
            custom: gameData.custom || null,
        };

        try {
            const response = await axios.post('/games', newGame);
            newGame.id = response.data.id; // Atualiza o ID com o fornecido pelo servidor
            game.value = JSON.parse(JSON.stringify(newGame));
            console.log("GAME",game.value)
            console.log('Storing game:', JSON.stringify(game.value));
            localStorage.setItem('game', JSON.stringify(game.value));
            return true;
        } catch (error) {
            console.error('Erro ao criar jogo:', error.response?.data?.message || error.message);
            return false;
        }
    };

    // Função para buscar o jogo do servidor (apenas se autenticado)


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
    const clearGame = () => {
        game.value = null;
        localStorage.removeItem('game');
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
        createGame,
        clearGame,
        restoreGame,
        deleteGame,
    };
});

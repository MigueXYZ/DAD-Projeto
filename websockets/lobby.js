exports.createLobby = () => {
    const games = new Map();

    const addGame = (user,socketId, game_id, board) => {
        const game = {
            id:game_id,
            created_at: Date.now(),
            board_size: board,
            player1: user,
            player1SocketId: socketId,
        }
        games.set(game_id,game);
        return game;
    };

    const removeGame = (id) => {
        games.delete(id);
        return games;
    };

    const existsGame = (id) => {
        return games.has(id);
    };

    const getGame = (id) => {
        return games.get(id);
    };

    const getGames = () => {
        return [...games.values()];
    };

    const leaveLobby = (socketId) => {
        const gamesToDelete = [...games.values()].filter(game => game.player1SocketId === socketId);
        gamesToDelete.forEach(game => games.delete(game.id));
        return getGames();
    }

    return {
        addGame,
        removeGame,
        existsGame,
        getGame,
        getGames,
        leaveLobby,
    };
}
exports.createGameEngine = () => {
    const initGame = (gameFromDB) => {
        gameFromDB.gameStatus = null;
        // null -> game has not started yet
        // 0 -> game is running
        // 1 -> player 1 wins
        // 2 -> player 2 wins
        // 3 -> draw
        gameFromDB.currentPlayer = 1;
        gameFromDB.totalPairs = { p1: 0, p2: 0 };
        gameFromDB.turns = 0;
        gameFromDB.board = generateShuffledBoard(gameFromDB.board_size);
        gameFromDB.revealedCards = []; // Stores indices of currently revealed cards
        for (let i = 0; i < gameFromDB.board_size; i++) {
            gameFromDB.revealedCards.push(-1);
        }
        gameFromDB.lastPlayedCard = null; // Stores indices of last played cards
        return gameFromDB;
    };

    // Generate a shuffled board of card pairs
    const generateShuffledBoard = (boardSize) => {
        const board = [];
        for (let i = 0; i < boardSize / 2; i++) {
            board.push(i, i);
        }
        return shuffle(board);
    }

    // Shuffle an array
    const shuffle = (array) => {
        const shuffledArray = [...array];
        for (let i = shuffledArray.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [shuffledArray[i], shuffledArray[j]] = [shuffledArray[j], shuffledArray[i]];
        }
        return shuffledArray;
    }

    // End game
    const endGame = (game,player_id) => {
        if (game.player1.id == player_id){
            game.gameStatus = 2;
        }
        else if(game.player2.id == player_id){
            game.gameStatus = 1;
        }
        else{
            game.gameStatus = 3;
        }
    };


    // Check if the game has ended
    const isGameComplete = (game) => {
        return game.board.every((card) => card === null) || game.gameStatus > 0;
    }

    // Update the game status based on the scores or game completion
    const updateGameStatus = (game) => {
        if (isGameComplete(game)) {
            if (game.scores[1] > game.scores[2]) {
                game.gameStatus = 1;
            } else if (game.scores[2] > game.scores[1]) {
                game.gameStatus = 2;
            } else {
                game.gameStatus = 3;
            }
        } else {
            game.gameStatus = 0;
        }
    };

    // Play a card
    const play = (game, cardIndex, playerSocketId, callback) => {
        if(cardIndex < 0 || cardIndex > (game.board_size-1)){
            return {
                errorCode: 10,
                errorMessage: 'Invalid card index!',
            };
        }

        if(gameEnded(game)){
            return {
                errorCode: 11,
                errorMessage: 'Game has already ended!',
            };
        }

        // Check if it's the player's turn
        if (!isPlayerTurn(game, playerSocketId)) {
            return {
                errorCode: 12,
                errorMessage: 'It is not your turn!',
            };
        }

        // Check if the card is already revealed
        if (game.revealedCards[cardIndex] !== -1) {
            return {
                errorCode: 13,
                errorMessage: 'Card is already revealed!',
            };
        }


        // Reveal the card
        game.revealedCards[cardIndex] = game.board[cardIndex]+1;

        // Check if two cards are revealed
        // If yes, check if they are a pair

        if(game.lastPlayedCard === null){
            game.lastPlayedCard = cardIndex;
            return true;
        }

        if(game.board[game.lastPlayedCard] !== game.board[cardIndex]){
            game.revealedCards[game.lastPlayedCard] = -1;
            game.revealedCards[cardIndex] = -1;
            game.lastPlayedCard = null;
            //change player
            game.currentPlayer = game.currentPlayer === 1 ? 2 : 1;

            return true;
        }

        if(game.board[game.lastPlayedCard] === game.board[cardIndex]){
            game.totalPairs[game.currentPlayer]++;
            game.lastPlayedCard = null;

            //Do not change player, they get to keep playing

            // Check if the game has ended
            if(isGameComplete(game)){
                updateGameStatus(game);
            }
            return true;
        }
    }

    // Check if it's the player's turn
    const isPlayerTurn = (game, playerSocketId) => {
        return (
            (game.currentPlayer === 1 && playerSocketId === game.player1SocketId) ||
            (game.currentPlayer === 2 && playerSocketId === game.player2SocketId)
        );
    };

    // Player quits the game
    const quit = (game, playerSocketId) => {
        if (gameEnded(game)) {
            return {
                errorCode: 11,
                errorMessage: 'Game has already ended!',
            };
        }

        game.gameStatus = playerSocketId === game.player1SocketId ? 2 : 1;
        return true;
    };

    // Check if the game has ended
    const gameEnded = (game) => game.gameStatus > 0;

    // Close the game
    const close = (game, playerSocketId) => {
        if (!isGameComplete(game)) {
            return {
                errorCode: 14,
                errorMessage: 'Cannot close a game that has not ended!',
            };
        }

        if (
            playerSocketId !== game.player1SocketId &&
            playerSocketId !== game.player2SocketId
        ) {
            return {
                errorCode: 10,
                errorMessage: 'You are not playing this game!',
            };
        }

        return true;
    };

    return {
        initGame,
        play,
        gameEnded,
        quit,
        close,
    };
};

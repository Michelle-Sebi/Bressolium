import { configureStore } from '@reduxjs/toolkit';
import authReducer      from './features/auth/authSlice';
import gameReducer      from './features/game/gameSlice';
import boardReducer     from './features/board/boardSlice';
import inventoryReducer from './features/inventory/inventorySlice';
import { bressoliumApi } from './services/bressoliumApi';

export const store = configureStore({
  reducer: {
    auth:      authReducer,
    game:      gameReducer,
    board:     boardReducer,
    inventory: inventoryReducer,
    [bressoliumApi.reducerPath]: bressoliumApi.reducer,
  },
  middleware: (getDefault) => getDefault().concat(bressoliumApi.middleware),
});

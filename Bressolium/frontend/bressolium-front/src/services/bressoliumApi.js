import { createApi } from '@reduxjs/toolkit/query/react';
import httpClient from '../lib/httpClient';

const axiosBaseQuery = () => async ({ url, method = 'GET', data }) => {
    try {
        const result = await httpClient({ url, method, data });
        return { data: result.data };
    } catch (err) {
        return {
            error: {
                status: err.response?.status,
                data:   err.response?.data ?? err.message,
            },
        };
    }
};

export const bressoliumApi = createApi({
    reducerPath: 'bressoliumApi',
    baseQuery:   axiosBaseQuery(),
    tagTypes:    ['Board', 'Sync'],
    endpoints: (builder) => ({

        getBoard: builder.query({
            query: (gameId) => ({ url: `/board/${gameId}` }),
            providesTags: ['Board'],
            transformResponse: (response) => {
                // Real API returns { success, data: tiles_array }; tests upsert { tiles: [...] } directly
                if (Array.isArray(response?.tiles)) return response;
                return { tiles: response.data ?? response };
            },
        }),

        exploreTile: builder.mutation({
            query: (tileId) => ({ url: `/tiles/${tileId}/explore`, method: 'POST' }),
            invalidatesTags: ['Board', 'Sync'],
        }),

        upgradeTile: builder.mutation({
            query: (tileId) => ({ url: `/tiles/${tileId}/upgrade`, method: 'POST' }),
            invalidatesTags: ['Board', 'Sync'],
        }),

        getSync: builder.query({
            query: (gameId) => ({ url: `/game/${gameId}/sync` }),
            providesTags: ['Sync'],
            transformResponse: (response) => response.data ?? response,
        }),

        vote: builder.mutation({
            query: ({ gameId, ...body }) => ({
                url:    `/game/${gameId}/vote`,
                method: 'POST',
                data:   body,
            }),
        }),

        closeRound: builder.mutation({
            query: (gameId) => ({
                url:    `/game/${gameId}/close-round`,
                method: 'POST',
            }),
            invalidatesTags: ['Sync'],
        }),

    }),
});

export const {
    useGetBoardQuery,
    useExploreTileMutation,
    useUpgradeTileMutation,
    useGetSyncQuery,
    useVoteMutation,
    useCloseRoundMutation,
} = bressoliumApi;

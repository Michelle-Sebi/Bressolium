import { useSelector } from 'react-redux';

export function useInventory() {
    const { materials, status, error } = useSelector((state) => state.inventory);
    return { materials, status, error };
}

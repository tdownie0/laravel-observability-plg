import Button from '@/components/ui/button/Button.vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export type Payment = {
    id: string;
    amount: number;
    status: 'pending' | 'processing' | 'success' | 'failed';
    email: string;
};

export const columns: ColumnDef<Payment>[] = [
    {
        accessorKey: 'status',
        header: 'Status',
        enableColumnFilter: true,
    },
    {
        accessorKey: 'email',
        header: 'Email',
        enableColumnFilter: true,
    },
    {
        accessorKey: 'amount',
        header: ({ column }) =>
            h(
                Button,
                {
                    variant: 'ghost',
                    size: 'sm',
                    class: 'text-right flex items-center gap-2 justify-self-end',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                {
                    default: () => [
                        'Amount',
                        h('span', { class: 'ml-2' }, column.getIsSorted() === 'asc' ? '▲' : column.getIsSorted() === 'desc' ? '▼' : '⇅'),
                    ],
                },
            ),
        enableSorting: true,
        cell: ({ row }) => {
            const amount = parseFloat(row.getValue('amount'));
            const formatted = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
            }).format(amount);
            return h('div', { class: 'text-right font-medium' }, formatted);
        },
    },
];

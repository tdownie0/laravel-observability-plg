<script lang="ts" setup>
import Button from '@/components/ui/button/Button.vue';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    ColumnFiltersState,
    FlexRender,
    SortingState,
    VisibilityState,
    getCoreRowModel,
    getFilteredRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { ref } from 'vue';
import { Payment, columns } from './Contacts/Columns';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contacts',
        href: '/contacts',
    },
];

const data: Payment[] = [
    // Example data, replace with your actual data source
    { id: '1', amount: 100, status: 'success', email: 'user@example.com' },
    { id: '2', amount: 50, status: 'pending', email: 'another@example.com' },
];

const sorting = ref<SortingState>([]);
const filtering = ref<ColumnFiltersState>([]);
const visibility = ref<VisibilityState>({});
const searchSelection = ref<string>('email');

const table = useVueTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return filtering.value;
        },
        get columnVisibility() {
            return visibility.value;
        },
    },
    onSortingChange: (updater) => {
        sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    },
    onColumnFiltersChange: (updater) => {
        filtering.value = typeof updater === 'function' ? updater(filtering.value) : updater;
    },
    onColumnVisibilityChange: (updater) => {
        visibility.value = typeof updater === 'function' ? updater(visibility.value) : updater;
    },
});
</script>

<template>
    <Head title="Contacts" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex items-center justify-end p-4">
            <Button size="lg" class="hover:cursor-pointer"> Add Contact </Button>
        </div>
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center py-4">
                <div class="flex items-center">
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline" class="ml-auto rounded-r-none"> Select </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Filter By</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuRadioGroup
                                :model-value="searchSelection"
                                @update:model-value="
                                    (val) => {
                                        table.getColumn(searchSelection)?.setFilterValue('');
                                        searchSelection = val;
                                    }
                                "
                            >
                                <DropdownMenuRadioItem
                                    v-for="column in table.getAllColumns().filter((column) => column.id !== 'amount')"
                                    :key="column.id"
                                    :value="column.id"
                                    class="capitalize"
                                >
                                    {{ column.id }}
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <Input
                        :placeholder="`Filter ${searchSelection}...`"
                        :value="table.getColumn(searchSelection)?.getFilterValue() ?? ''"
                        @update:model-value="(e) => table.getColumn(searchSelection)?.setFilterValue((e as string).trim())"
                        class="w-full max-w-sm rounded-l-none"
                    />
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" class="ml-auto"> Columns </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuCheckboxItem
                            v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                            :key="column.id"
                            class="capitalize"
                            :model-value="column.getIsVisible()"
                            @update:model-value="(value: boolean | undefined) => column.toggleVisibility(!!value)"
                        >
                            {{ column.id }}
                        </DropdownMenuCheckboxItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
            <div class="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 rounded-xl border md:min-h-min">
                <div className="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                <TableHead v-for="header in headerGroup.headers" :key="header.id">
                                    <template v-if="!header.isPlaceholder">
                                        <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </template>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow
                                    v-for="row in table.getRowModel().rows"
                                    :key="row.id"
                                    :data-state="row.getIsSelected() ? 'selected' : undefined"
                                >
                                    <TableCell
                                        v-for="cell in row.getVisibleCells()"
                                        :key="cell.id"
                                        :class="{
                                            'rounded bg-green-100 px-2 py-1 font-semibold text-green-700 dark:bg-green-900 dark:text-green-400':
                                                cell.column.id === 'status' && cell.getValue() === 'success',
                                            'rounded bg-yellow-100 px-2 py-1 font-semibold text-yellow-700 dark:bg-yellow-900 dark:text-yellow-400':
                                                cell.column.id === 'status' && cell.getValue() === 'pending',
                                        }"
                                    >
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <template v-else>
                                <TableRow>
                                    <TableCell :colspan="columns.length" class="h-24 text-center"> No results. </TableCell>
                                </TableRow>
                            </template>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

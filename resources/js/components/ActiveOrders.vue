<script setup>
import { ref, onMounted } from 'vue';

// Filter options sent as ?filter= to GET /api/orders.
const FILTERS = [
    { value: 'active', label: 'Active' },
    { value: 'all', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'assigned', label: 'Assigned' },
    { value: 'in_progress', label: 'In progress' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

const filter = ref('active');
const orders = ref([]);
const meta = ref(null);
const loading = ref(false);
const error = ref(null);
const rowState = ref({});

async function load(toPage = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await window.axios.get('/api/orders', {
            params: { filter: filter.value, page: toPage },
        });
        orders.value = data.data;
        meta.value = data.meta;
    } catch (e) {
        error.value = 'Failed to load orders.';
    } finally {
        loading.value = false;
    }
}

async function assign(order) {
    rowState.value[order.id] = { assigning: true, message: null, ok: false };
    try {
        const { data } = await window.axios.post(`/api/orders/${order.id}/assign`);
        order.status = 'assigned';
        order.status_label = 'assigned';
        order.driver_id = data.data.driver_id;
        rowState.value[order.id] = {
            assigning: false,
            ok: true,
            message: `Driver #${data.data.driver_id} · ${Math.round(data.data.distance_meters)} m`,
        };
    } catch (e) {
        const msg = e.response?.data?.error?.message ?? 'Assignment failed.';
        rowState.value[order.id] = { assigning: false, ok: false, message: msg };
    }
}

onMounted(() => load());
</script>

<template>
    <div class="mx-auto max-w-3xl p-6">
        <header class="mb-5 flex items-center justify-between gap-3">
            <h1 class="border-l-4 border-brand pl-3 text-xl font-bold uppercase tracking-wide text-ink">Orders</h1>
            <div class="flex items-end gap-3">
                <label class="flex flex-col text-xs text-gray-600">
                    Filter
                    <select v-model="filter" class="mt-1 w-40 rounded-md border border-gray-300 px-2 py-1.5 text-sm" @change="load(1)">
                        <option v-for="f in FILTERS" :key="f.value" :value="f.value">{{ f.label }}</option>
                    </select>
                </label>
                <button
                    class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    :disabled="loading"
                    @click="load(meta?.current_page ?? 1)"
                >
                    {{ loading ? 'Loading…' : 'Refresh' }}
                </button>
            </div>
        </header>

        <p v-if="error" class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>

        <p v-else-if="!loading && orders.length === 0" class="text-sm text-gray-500">No orders for this filter.</p>

        <ul v-else class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
            <li v-for="order in orders" :key="order.id" class="flex items-center justify-between gap-4 p-4">
                <div class="min-w-0">
                    <p class="font-medium text-gray-800">Order #{{ order.id }}</p>
                    <p class="truncate text-xs text-gray-500">{{ order.pickup.lat }}, {{ order.pickup.lng }}</p>
                    <p v-if="rowState[order.id]?.message"
                       :class="rowState[order.id].ok ? 'text-green-600' : 'text-red-600'"
                       class="mt-1 text-xs">
                        {{ rowState[order.id].message }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <span
                        class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="order.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700'"
                    >
                        {{ order.status_label }}
                    </span>
                    <button
                        v-if="order.status === 'pending'"
                        class="rounded-md bg-brand px-3 py-1.5 text-sm font-semibold text-ink hover:bg-brand-dark disabled:opacity-50"
                        :disabled="rowState[order.id]?.assigning"
                        @click="assign(order)"
                    >
                        {{ rowState[order.id]?.assigning ? 'Assigning…' : 'Assign' }}
                    </button>
                    <span v-else-if="order.driver_id" class="text-xs text-gray-400">Driver #{{ order.driver_id }}</span>
                </div>
            </li>
        </ul>

        <div v-if="meta && meta.last_page > 1" class="mt-4 flex items-center justify-between text-sm text-gray-600">
            <span>Page {{ meta.current_page }} of {{ meta.last_page }} · {{ meta.total }} total</span>
            <div class="flex gap-2">
                <button class="rounded-md border border-gray-300 px-3 py-1 disabled:opacity-40"
                        :disabled="meta.current_page <= 1 || loading" @click="load(meta.current_page - 1)">Prev</button>
                <button class="rounded-md border border-gray-300 px-3 py-1 disabled:opacity-40"
                        :disabled="meta.current_page >= meta.last_page || loading" @click="load(meta.current_page + 1)">Next</button>
            </div>
        </div>
    </div>
</template>

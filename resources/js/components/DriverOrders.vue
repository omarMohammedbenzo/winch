<script setup>
import { ref, onMounted } from 'vue';

const STATUSES = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'];

// Driver availability dot: green = available, yellow = busy, red = offline.
const DRIVER_DOT = {
    available: 'bg-green-500',
    busy: 'bg-yellow-400',
    offline: 'bg-red-500',
};
function dotClass(status) {
    return DRIVER_DOT[status] ?? 'bg-gray-300';
}

// --- Driver list + search (by name or phone) ---
const term = ref('');
const drivers = ref([]);
const driversMeta = ref(null);
const searching = ref(false);

// --- Orders for the selected driver ---
const selected = ref(null);
const status = ref('');
const perPage = ref(10);
const orders = ref([]);
const meta = ref(null);
const loading = ref(false);
const error = ref(null);

async function loadDrivers(toPage = 1) {
    searching.value = true;
    error.value = null;
    try {
        const { data } = await window.axios.get('/api/drivers', {
            params: { search: term.value || undefined, per_page: 10, page: toPage },
        });
        drivers.value = data.data;
        driversMeta.value = data.meta;
    } catch (e) {
        error.value = 'Driver search failed.';
    } finally {
        searching.value = false;
    }
}

function selectDriver(driver) {
    selected.value = driver;
    status.value = '';
    load(1);
}

async function load(toPage = 1) {
    if (!selected.value) return;
    loading.value = true;
    error.value = null;
    try {
        const { data } = await window.axios.get(`/api/drivers/${selected.value.id}/orders`, {
            params: { status: status.value || undefined, per_page: perPage.value, page: toPage },
        });
        orders.value = data.data;
        meta.value = data.meta;
    } catch (e) {
        error.value = e.response?.data?.error?.message ?? 'Failed to load orders.';
        orders.value = [];
        meta.value = null;
    } finally {
        loading.value = false;
    }
}

// Show the driver list immediately on open.
onMounted(() => loadDrivers());
</script>

<template>
    <div class="mx-auto max-w-3xl p-6">
        <h1 class="mb-4 text-xl font-semibold text-gray-800">Driver Orders</h1>

        <!-- Driver search -->
        <div class="mb-3 flex gap-2">
            <input
                v-model="term"
                type="text"
                placeholder="Search driver by name or phone…"
                class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm"
                @keyup.enter="loadDrivers(1)"
            >
            <button
                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:opacity-50"
                :disabled="searching"
                @click="loadDrivers(1)"
            >
                {{ searching ? 'Searching…' : 'Search' }}
            </button>
        </div>

        <p v-if="error" class="mb-3 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>

        <!-- Driver list (always visible) -->
        <p v-if="!searching && drivers.length === 0" class="text-sm text-gray-500">No drivers found.</p>

        <ul v-else class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
            <li
                v-for="d in drivers"
                :key="d.id"
                class="flex cursor-pointer items-center justify-between p-3 hover:bg-gray-50"
                :class="selected?.id === d.id ? 'bg-gray-50' : ''"
                @click="selectDriver(d)"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="inline-block h-2.5 w-2.5 shrink-0 rounded-full"
                        :class="dotClass(d.status)"
                        :title="d.status_label"
                    ></span>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ d.name }}</p>
                        <p class="text-xs text-gray-500">{{ d.phone }}</p>
                    </div>
                </div>
                <span class="text-xs text-gray-500">{{ d.status_label }}</span>
            </li>
        </ul>

        <!-- Driver list pagination -->
        <div v-if="driversMeta && driversMeta.last_page > 1" class="mt-3 flex items-center justify-between text-xs text-gray-500">
            <span>Page {{ driversMeta.current_page }} / {{ driversMeta.last_page }}</span>
            <div class="flex gap-2">
                <button class="rounded-md border border-gray-300 px-2.5 py-1 disabled:opacity-40"
                        :disabled="driversMeta.current_page <= 1 || searching" @click="loadDrivers(driversMeta.current_page - 1)">Prev</button>
                <button class="rounded-md border border-gray-300 px-2.5 py-1 disabled:opacity-40"
                        :disabled="driversMeta.current_page >= driversMeta.last_page || searching" @click="loadDrivers(driversMeta.current_page + 1)">Next</button>
            </div>
        </div>

        <!-- Selected driver's orders -->
        <template v-if="selected">
            <div class="mb-4 mt-6 flex flex-wrap items-end justify-between gap-3 border-t border-gray-100 pt-4">
                <div>
                    <p class="text-sm text-gray-500">Orders for</p>
                    <p class="font-semibold text-gray-800">{{ selected.name }} · {{ selected.phone }}</p>
                </div>
                <div class="flex items-end gap-3">
                    <label class="flex flex-col text-xs text-gray-600">
                        Status
                        <select v-model="status" class="mt-1 w-40 rounded-md border border-gray-300 px-2 py-1.5 text-sm" @change="load(1)">
                            <option value="">All</option>
                            <option v-for="s in STATUSES" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </label>
                    <label class="flex flex-col text-xs text-gray-600">
                        Per page
                        <select v-model.number="perPage" class="mt-1 w-24 rounded-md border border-gray-300 px-2 py-1.5 text-sm" @change="load(1)">
                            <option :value="5">5</option>
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                        </select>
                    </label>
                </div>
            </div>

            <p v-if="!loading && orders.length === 0" class="text-sm text-gray-500">No orders for this filter.</p>

            <ul v-else class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
                <li v-for="order in orders" :key="order.id" class="flex items-center justify-between p-4">
                    <div>
                        <p class="font-medium text-gray-800">Order #{{ order.id }}</p>
                        <p class="text-xs text-gray-500">{{ order.pickup.lat }}, {{ order.pickup.lng }}</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                        {{ order.status_label }}
                    </span>
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
        </template>
    </div>
</template>

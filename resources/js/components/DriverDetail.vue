<script setup>
import { ref, onMounted } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps({ driver: { type: Object, required: true } });
const emit = defineEmits(['back']);

const ORDER_STATUSES = ['pending', 'assigned', 'in_progress', 'completed', 'cancelled'];
const DOT = { available: 'bg-green-500', busy: 'bg-yellow-400', offline: 'bg-red-500' };
const dotClass = (s) => DOT[s] ?? 'bg-gray-300';

const status = ref('');
const perPage = ref(10);
const orders = ref([]);
const meta = ref(null);
const loading = ref(false);
const error = ref(null);

async function load(toPage = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await window.axios.get(`/api/drivers/${props.driver.id}/orders`, {
            params: { status: status.value || undefined, per_page: perPage.value, page: toPage },
        });
        orders.value = data.data;
        meta.value = data.meta;
    } catch (e) {
        error.value = e.response?.data?.error?.message ?? 'Failed to load orders.';
        toast.error(error.value);
        orders.value = [];
        meta.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(() => load());
</script>

<template>
    <div class="mx-auto max-w-3xl p-6">
        <button class="mb-4 text-sm text-gray-500 hover:text-gray-800" @click="emit('back')">← Back to drivers</button>

        <!-- Driver header -->
        <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4">
            <span class="inline-block h-3 w-3 rounded-full" :class="dotClass(driver.status)" :title="driver.status_label"></span>
            <div>
                <h1 class="text-lg font-semibold text-gray-800">{{ driver.name }}</h1>
                <p class="text-xs text-gray-500">{{ driver.phone }} · {{ driver.status_label }}</p>
            </div>
        </div>

        <!-- Order filters -->
        <div class="mb-4 flex items-end justify-end gap-3">
            <label class="flex flex-col text-xs text-gray-600">
                Status
                <select v-model="status" class="mt-1 w-40 rounded-md border border-gray-300 px-2 py-1.5 text-sm" @change="load(1)">
                    <option value="">All</option>
                    <option v-for="s in ORDER_STATUSES" :key="s" :value="s">{{ s }}</option>
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

        <p v-if="error" class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>

        <p v-else-if="!loading && orders.length === 0" class="text-sm text-gray-500">No orders for this filter.</p>

        <ul v-else class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
            <li v-for="order in orders" :key="order.id" class="flex items-center justify-between p-4">
                <div>
                    <p class="font-medium text-gray-800">Order #{{ order.id }}</p>
                    <p class="text-xs text-gray-500">{{ order.pickup.lat }}, {{ order.pickup.lng }}</p>
                </div>
                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ order.status_label }}</span>
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

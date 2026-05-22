<script setup>
import { ref, onMounted } from 'vue';

const emit = defineEmits(['select']);

const DRIVER_STATUSES = ['available', 'busy', 'offline'];
const DOT = { available: 'bg-green-500', busy: 'bg-yellow-400', offline: 'bg-red-500' };
const dotClass = (s) => DOT[s] ?? 'bg-gray-300';

const term = ref('');
const status = ref('');
const perPage = ref(10);
const drivers = ref([]);
const meta = ref(null);
const loading = ref(false);
const error = ref(null);

async function load(toPage = 1) {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await window.axios.get('/api/drivers', {
            params: {
                search: term.value || undefined,
                status: status.value || undefined,
                per_page: perPage.value,
                page: toPage,
            },
        });
        drivers.value = data.data;
        meta.value = data.meta;
    } catch (e) {
        error.value = 'Driver search failed.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => load());
</script>

<template>
    <div class="mx-auto max-w-3xl p-6">
        <h1 class="mb-4 border-l-4 border-brand pl-3 text-xl font-bold uppercase tracking-wide text-ink">Drivers</h1>

        <!-- Search + filters, all on one row -->
        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div class="flex flex-1 gap-2">
                <input
                    v-model="term"
                    type="text"
                    placeholder="Search by name or phone…"
                    class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm"
                    @keyup.enter="load(1)"
                >
                <button
                    class="rounded-md bg-brand px-4 py-2 text-sm font-semibold text-ink hover:bg-brand-dark disabled:opacity-50"
                    :disabled="loading"
                    @click="load(1)"
                >
                    {{ loading ? '…' : 'Search' }}
                </button>
            </div>

            <label class="flex flex-col text-xs text-gray-600">
                Status
                <select v-model="status" class="mt-1 w-36 rounded-md border border-gray-300 px-2 py-1.5 text-sm" @change="load(1)">
                    <option value="">All</option>
                    <option v-for="s in DRIVER_STATUSES" :key="s" :value="s">{{ s }}</option>
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

        <p v-if="error" class="mb-3 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ error }}</p>

        <p v-if="!loading && drivers.length === 0" class="text-sm text-gray-500">No drivers found.</p>

        <ul v-else class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white">
            <li
                v-for="d in drivers"
                :key="d.id"
                class="flex cursor-pointer items-center justify-between p-3 hover:bg-gray-50"
                @click="emit('select', d)"
            >
                <div class="flex items-center gap-3">
                    <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full" :class="dotClass(d.status)" :title="d.status_label"></span>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ d.name }}</p>
                        <p class="text-xs text-gray-500">{{ d.phone }}</p>
                    </div>
                </div>
                <span class="text-xs text-gray-500">{{ d.status_label }} ›</span>
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

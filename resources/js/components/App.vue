<script setup>
import { ref } from 'vue';
import ActiveOrders from './ActiveOrders.vue';
import DriverOrders from './DriverOrders.vue';

const tabs = [
    { key: 'active', label: 'Orders', component: ActiveOrders },
    { key: 'driver', label: 'Driver', component: DriverOrders },
];
const current = ref('active');
</script>

<template>
    <div class="min-h-screen">
        <nav class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-3xl gap-1 px-6">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    class="border-b-2 px-4 py-3 text-sm font-medium"
                    :class="current === tab.key
                        ? 'border-gray-900 text-gray-900'
                        : 'border-transparent text-gray-500 hover:text-gray-700'"
                    @click="current = tab.key"
                >
                    {{ tab.label }}
                </button>
            </div>
        </nav>

        <component :is="tabs.find(t => t.key === current).component" />
    </div>
</template>

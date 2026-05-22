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
        <!-- Brand bar: ink-black with the WINCH logo -->
        <header class="bg-ink">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-3">
                <img :src="'/imgs/Logo.svg'" alt="WINCH" class="h-7 w-auto" />
                <span class="text-xs font-semibold uppercase tracking-widest text-brand">Order Assignment System</span>
            </div>
        </header>

        <nav class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-3xl gap-1 px-6">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    class="border-b-2 px-4 py-3 text-sm font-semibold uppercase tracking-wide transition-colors"
                    :class="current === tab.key
                        ? 'border-brand text-ink'
                        : 'border-transparent text-gray-400 hover:text-gray-700'"
                    @click="current = tab.key"
                >
                    {{ tab.label }}
                </button>
            </div>
        </nav>

        <component :is="tabs.find(t => t.key === current).component" />
    </div>
</template>

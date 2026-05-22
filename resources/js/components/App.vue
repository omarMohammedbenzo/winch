<script setup>
import { ref } from 'vue';
import ActiveOrders from './ActiveOrders.vue';
import DriverOrders from './DriverOrders.vue';
import ScalingDecision from './ScalingDecision.vue';

const tabs = [
    { key: 'active', label: 'Orders', component: ActiveOrders },
    { key: 'driver', label: 'Driver', component: DriverOrders },
    { key: 'scaling', label: 'Scaling', component: ScalingDecision },
];
const current = ref('active');
</script>

<template>
    <div class="flex min-h-screen flex-col">
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

        <main class="flex-1">
            <component :is="tabs.find(t => t.key === current).component" />
        </main>

        <footer class="bg-ink">
            <div class="mx-auto flex max-w-3xl items-center justify-center gap-1 px-6 py-4 text-xs text-gray-400">
                <span>Made by</span>
                <a
                    href="https://www.linkedin.com/in/omar-m-227b10253/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-semibold text-brand hover:underline"
                >
                    Omar Mohamed
                </a>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import DriverList from './DriverList.vue';
import DriverDetail from './DriverDetail.vue';

// Simple two-screen flow without a router: list → detail and back.
const selected = ref(null);
</script>

<template>
    <DriverDetail v-if="selected" :driver="selected" @back="selected = null" />
    <DriverList v-else @select="(d) => (selected = d)" />
</template>

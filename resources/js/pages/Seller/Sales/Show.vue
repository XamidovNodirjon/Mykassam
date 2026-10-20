<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/PageHeader.vue';
import SaleDetails from '@/components/SaleDetails.vue';
import { index } from '@/routes/seller/sales';
import type { Sale } from '@/types';

defineProps<{
    sale: Sale;
}>();
</script>

<template>
    <Head :title="`Savdo #${sale.id}`" />

    <PageHeader :title="`Savdo #${sale.id}`" :back-href="index.url()" />

    <SaleDetails :sale="sale" />
</template>

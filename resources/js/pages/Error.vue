<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { home } from '@/routes';

const props = defineProps<{
    status: number;
}>();

const messages: Record<number, { title: string; description: string }> = {
    403: {
        title: 'Ruxsat yo‘q',
        description: 'Bu sahifa sizning hisobingiz uchun mo‘ljallanmagan.',
    },
    404: {
        title: 'Sahifa topilmadi',
        description: 'Siz qidirgan sahifa mavjud emas yoki o‘chirilgan.',
    },
    500: {
        title: 'Serverda xatolik',
        description:
            'Nimadir noto‘g‘ri ketdi. Birozdan so‘ng qayta urinib ko‘ring.',
    },
    503: {
        title: 'Texnik ishlar',
        description:
            'Tizimda texnik ishlar olib borilmoqda. Tez orada qaytamiz.',
    },
};

const message = computed(() => messages[props.status] ?? messages[500]);
</script>

<template>
    <Head :title="message.title" />

    <div
        class="flex flex-col items-center gap-4 rounded-[2rem] glass p-8 text-center"
    >
        <span
            class="flex size-14 items-center justify-center rounded-full bg-surface-muted text-ink-muted"
        >
            <AppIcon :name="status === 403 ? 'lock' : 'alert'" :size="28" />
        </span>
        <div>
            <p class="text-sm font-medium text-ink-muted">{{ status }}</p>
            <h1 class="mt-1 text-xl font-semibold text-ink">
                {{ message.title }}
            </h1>
            <p class="mt-2 text-sm text-ink-soft">{{ message.description }}</p>
        </div>
        <AppButton :href="home.url()" icon="home" block>
            Bosh sahifaga
        </AppButton>
    </div>
</template>

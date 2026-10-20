<script setup lang="ts">
import { computed } from 'vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import { confirmState, settleConfirm } from '@/composables/useConfirm';

const open = computed({
    get: () => confirmState.open,
    set: (value: boolean) => {
        if (!value) {
            settleConfirm(false);
        }
    },
});
</script>

<template>
    <AppModal v-model:open="open" :title="confirmState.title">
        <p v-if="confirmState.message" class="text-sm text-ink-soft">
            {{ confirmState.message }}
        </p>
        <div class="mt-6 grid grid-cols-2 gap-3">
            <AppButton variant="secondary" @click="settleConfirm(false)">
                Bekor qilish
            </AppButton>
            <AppButton
                :variant="confirmState.danger ? 'danger' : 'primary'"
                @click="settleConfirm(true)"
            >
                {{ confirmState.confirmText ?? 'Tasdiqlash' }}
            </AppButton>
        </div>
    </AppModal>
</template>

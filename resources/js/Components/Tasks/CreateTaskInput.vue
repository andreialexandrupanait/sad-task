<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    workspace: Object,
    space: Object,
    list: Object,
});

const emit = defineEmits(['created']);

const title = ref('');
const isLoading = ref(false);
const isFocused = ref(false);

const createTask = () => {
    if (!title.value.trim() || isLoading.value) return;

    isLoading.value = true;

    router.post(
        route('tasks.store', [props.workspace, props.space, props.list]),
        { title: title.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => {
                title.value = '';
                emit('created');
            },
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
};
</script>

<template>
    <div
        class="bg-white dark:bg-gray-800 rounded-xl border transition-colors"
        :class="isFocused
            ? 'border-indigo-500 ring-2 ring-indigo-500/20'
            : 'border-gray-200 dark:border-gray-700'"
    >
        <div class="flex items-center px-4 py-3">
            <PlusIcon class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" />
            <input
                v-model="title"
                @focus="isFocused = true"
                @blur="isFocused = false"
                @keyup.enter="createTask"
                type="text"
                placeholder="Add a task..."
                class="flex-1 bg-transparent border-0 focus:ring-0 text-gray-900 dark:text-white placeholder-gray-400 text-sm p-0"
                :disabled="isLoading"
            />
            <button
                v-if="title.trim()"
                @click="createTask"
                :disabled="isLoading"
                class="ml-3 px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                {{ isLoading ? 'Adding...' : 'Add Task' }}
            </button>
        </div>
    </div>
</template>

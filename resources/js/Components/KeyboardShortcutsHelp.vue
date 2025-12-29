<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { KEYBOARD_SHORTCUTS } from '@/composables/useKeyboardShortcuts';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const shortcutGroups = [
    {
        title: 'Navigation',
        shortcuts: [
            { key: 'j / ↓', description: 'Move selection down' },
            { key: 'k / ↑', description: 'Move selection up' },
            { key: 'g g', description: 'Go to first task' },
            { key: 'Enter', description: 'Open selected task' },
            { key: 'Escape', description: 'Clear selection / Close' },
        ],
    },
    {
        title: 'Task Actions',
        shortcuts: [
            { key: 'x / c', description: 'Toggle complete' },
            { key: 's', description: 'Change status' },
            { key: 'p', description: 'Change priority' },
            { key: 'n', description: 'New task' },
            { key: 'Shift + Del', description: 'Delete task' },
        ],
    },
    {
        title: 'Global',
        shortcuts: [
            { key: '⌘ K', description: 'Command palette' },
            { key: '⌘ N', description: 'Quick add task' },
            { key: '?', description: 'Show this help' },
        ],
    },
];
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="emit('close')"
                @keyup.escape="emit('close')"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

                <!-- Modal -->
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Keyboard Shortcuts
                        </h2>
                        <button
                            @click="emit('close')"
                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6 overflow-y-auto max-h-[60vh]">
                        <div v-for="group in shortcutGroups" :key="group.title">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                                {{ group.title }}
                            </h3>
                            <div class="space-y-2">
                                <div
                                    v-for="shortcut in group.shortcuts"
                                    :key="shortcut.key"
                                    class="flex items-center justify-between"
                                >
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ shortcut.description }}
                                    </span>
                                    <div class="flex items-center space-x-1">
                                        <kbd
                                            v-for="(key, idx) in shortcut.key.split(' ')"
                                            :key="idx"
                                            class="px-2 py-1 text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded border border-gray-300 dark:border-gray-600"
                                        >
                                            {{ key }}
                                        </kbd>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Press <kbd class="px-1.5 py-0.5 text-xs bg-gray-200 dark:bg-gray-700 rounded">?</kbd> anytime to show this help
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    workspaces: Array,
});
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Workspaces</h1>
                <Link
                    :href="route('workspaces.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    New Workspace
                </Link>
            </div>

            <div v-if="workspaces.length === 0" class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-gray-200 dark:border-gray-700">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <PlusIcon class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No workspaces yet</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Create your first workspace to get started.</p>
                <Link
                    :href="route('workspaces.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Create Workspace
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="workspace in workspaces"
                    :key="workspace.id"
                    :href="route('workspaces.show', workspace)"
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors group"
                >
                    <div class="flex items-start justify-between">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl font-bold"
                            :style="{ backgroundColor: workspace.color || '#6366f1' }"
                        >
                            {{ workspace.name.charAt(0) }}
                        </div>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                        {{ workspace.name }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                        {{ workspace.description || 'No description' }}
                    </p>
                    <div class="mt-4 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ workspace.spaces_count || 0 }} spaces</span>
                        <span>{{ workspace.members_count || 0 }} members</span>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

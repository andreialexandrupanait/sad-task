<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    PlusIcon,
    FolderIcon,
    Cog6ToothIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    workspace: Object,
});

const showCreateSpaceModal = ref(false);

const spaceForm = useForm({
    name: '',
    description: '',
    color: '#8b5cf6',
    is_private: false,
});

const createSpace = () => {
    spaceForm.post(route('spaces.store', props.workspace), {
        onSuccess: () => {
            showCreateSpaceModal.value = false;
            spaceForm.reset();
        },
    });
};
</script>

<template>
    <AppLayout :workspace="workspace">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-start justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <div
                        class="w-14 h-14 rounded-xl flex items-center justify-center text-white text-2xl font-bold"
                        :style="{ backgroundColor: workspace.color || '#6366f1' }"
                    >
                        {{ workspace.name.charAt(0) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ workspace.name }}
                        </h1>
                        <p v-if="workspace.description" class="text-gray-600 dark:text-gray-400 mt-1">
                            {{ workspace.description }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('workspaces.members', workspace)"
                        class="inline-flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <UserGroupIcon class="w-4 h-4 mr-2" />
                        {{ workspace.members?.length || 0 }} Members
                    </Link>
                    <Link
                        :href="route('workspaces.edit', workspace)"
                        class="inline-flex items-center p-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <Cog6ToothIcon class="w-5 h-5" />
                    </Link>
                </div>
            </div>

            <!-- Spaces Section -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Spaces</h2>
                    <button
                        @click="showCreateSpaceModal = true"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        New Space
                    </button>
                </div>

                <div v-if="workspace.spaces?.length === 0" class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-gray-200 dark:border-gray-700">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <FolderIcon class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No spaces yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Create your first space to organize your projects.</p>
                    <button
                        @click="showCreateSpaceModal = true"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Create Space
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link
                        v-for="space in workspace.spaces"
                        :key="space.id"
                        :href="route('spaces.show', [workspace, space])"
                        class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors group"
                    >
                        <div class="flex items-center space-x-3 mb-4">
                            <div
                                class="w-10 h-10 rounded-lg flex items-center justify-center"
                                :style="{ backgroundColor: space.color + '20' }"
                            >
                                <span
                                    class="w-4 h-4 rounded-full"
                                    :style="{ backgroundColor: space.color }"
                                ></span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ space.name }}
                                </h3>
                                <p v-if="space.is_private" class="text-xs text-gray-500">Private</p>
                            </div>
                        </div>
                        <p v-if="space.description" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">
                            {{ space.description }}
                        </p>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ space.task_lists_count || 0 }} lists
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Create Space Modal -->
        <div
            v-if="showCreateSpaceModal"
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showCreateSpaceModal = false"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create Space</h3>

                    <form @submit.prevent="createSpace">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Space Name
                            </label>
                            <input
                                v-model="spaceForm.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="e.g., Product Development"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea
                                v-model="spaceForm.description"
                                rows="2"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            ></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    v-model="spaceForm.is_private"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    Make this space private
                                </span>
                            </label>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="showCreateSpaceModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="spaceForm.processing"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Create Space
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

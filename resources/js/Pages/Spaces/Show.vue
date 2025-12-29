<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    PlusIcon,
    FolderIcon,
    ListBulletIcon,
    EllipsisHorizontalIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    workspace: Object,
    space: Object,
});

const showCreateListModal = ref(false);
const showCreateFolderModal = ref(false);

const listForm = useForm({
    name: '',
    folder_id: null,
});

const folderForm = useForm({
    name: '',
});

const createList = () => {
    listForm.post(route('lists.store', [props.workspace, props.space]), {
        onSuccess: () => {
            showCreateListModal.value = false;
            listForm.reset();
        },
    });
};

const createFolder = () => {
    folderForm.post(route('folders.store', [props.workspace, props.space]), {
        onSuccess: () => {
            showCreateFolderModal.value = false;
            folderForm.reset();
        },
    });
};
</script>

<template>
    <AppLayout :workspace="workspace" :space="space">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex items-start justify-between mb-8">
                <div>
                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                        <Link :href="route('workspaces.show', workspace)" class="hover:text-gray-700 dark:hover:text-gray-200">
                            {{ workspace.name }}
                        </Link>
                        <span>/</span>
                        <span>{{ space.name }}</span>
                    </div>
                    <div class="flex items-center space-x-3">
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
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ space.name }}
                            </h1>
                            <p v-if="space.description" class="text-gray-600 dark:text-gray-400 mt-1">
                                {{ space.description }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <button
                        @click="showCreateFolderModal = true"
                        class="inline-flex items-center px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        <FolderIcon class="w-4 h-4 mr-2" />
                        New Folder
                    </button>
                    <button
                        @click="showCreateListModal = true"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        New List
                    </button>
                </div>
            </div>

            <!-- Folders -->
            <div v-if="space.folders?.length" class="space-y-6 mb-8">
                <div v-for="folder in space.folders" :key="folder.id" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-900">
                        <div class="flex items-center space-x-2">
                            <FolderIcon class="w-5 h-5 text-gray-400" />
                            <span class="font-medium text-gray-900 dark:text-white">{{ folder.name }}</span>
                            <span class="text-sm text-gray-500">{{ folder.task_lists?.length || 0 }} lists</span>
                        </div>
                        <button class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <EllipsisHorizontalIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <div v-if="folder.task_lists?.length" class="divide-y divide-gray-200 dark:divide-gray-700">
                        <Link
                            v-for="list in folder.task_lists"
                            :key="list.id"
                            :href="route('lists.show', [workspace, space, list])"
                            class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <ListBulletIcon class="w-5 h-5 text-gray-400 mr-3" />
                            <span class="text-gray-900 dark:text-white">{{ list.name }}</span>
                        </Link>
                    </div>
                    <div v-else class="px-4 py-6 text-center text-gray-500">
                        No lists in this folder
                    </div>
                </div>
            </div>

            <!-- Folderless Lists -->
            <div>
                <h2 v-if="space.folders?.length" class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Lists
                </h2>

                <div v-if="space.folderless_lists?.length === 0 && space.folders?.length === 0" class="bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-gray-200 dark:border-gray-700">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <ListBulletIcon class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No lists yet</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Create your first list to start adding tasks.</p>
                    <button
                        @click="showCreateListModal = true"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Create List
                    </button>
                </div>

                <div v-else-if="space.folderless_lists?.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
                    <Link
                        v-for="list in space.folderless_lists"
                        :key="list.id"
                        :href="route('lists.show', [workspace, space, list])"
                        class="flex items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <div
                            v-if="list.color"
                            class="w-3 h-3 rounded-full mr-3"
                            :style="{ backgroundColor: list.color }"
                        ></div>
                        <ListBulletIcon v-else class="w-5 h-5 text-gray-400 mr-3" />
                        <span class="text-gray-900 dark:text-white">{{ list.name }}</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Create List Modal -->
        <div v-if="showCreateListModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showCreateListModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create List</h3>
                    <form @submit.prevent="createList">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                List Name
                            </label>
                            <input
                                v-model="listForm.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                                placeholder="e.g., Sprint Backlog"
                            />
                        </div>
                        <div v-if="space.folders?.length" class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Folder (optional)
                            </label>
                            <select
                                v-model="listForm.folder_id"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2"
                            >
                                <option :value="null">No folder</option>
                                <option v-for="folder in space.folders" :key="folder.id" :value="folder.id">
                                    {{ folder.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="showCreateListModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Cancel
                            </button>
                            <button type="submit" :disabled="listForm.processing" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                                Create List
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Folder Modal -->
        <div v-if="showCreateFolderModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showCreateFolderModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl max-w-md w-full p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Create Folder</h3>
                    <form @submit.prevent="createFolder">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Folder Name
                            </label>
                            <input
                                v-model="folderForm.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                                placeholder="e.g., Q1 Projects"
                            />
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="showCreateFolderModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                Cancel
                            </button>
                            <button type="submit" :disabled="folderForm.processing" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                                Create Folder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

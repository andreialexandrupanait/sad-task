<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    name: '',
    description: '',
    color: '#6366f1',
});

const colors = [
    '#6366f1', // indigo
    '#8b5cf6', // violet
    '#ec4899', // pink
    '#ef4444', // red
    '#f97316', // orange
    '#eab308', // yellow
    '#22c55e', // green
    '#14b8a6', // teal
    '#0ea5e9', // sky
    '#6b7280', // gray
];

const submit = () => {
    form.post(route('workspaces.store'));
};
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <div class="mb-8">
                <Link
                    :href="route('workspaces.index')"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    &larr; Back to workspaces
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-4">Create Workspace</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    A workspace is where your team collaborates on projects and tasks.
                </p>
            </div>

            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Workspace Name
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="e.g., Marketing Team"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                        <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="What's this workspace for?"
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                </div>

                <!-- Color -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Color
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="color in colors"
                            :key="color"
                            type="button"
                            @click="form.color = color"
                            class="w-8 h-8 rounded-lg transition-transform hover:scale-110"
                            :class="{ 'ring-2 ring-offset-2 ring-gray-900 dark:ring-white': form.color === color }"
                            :style="{ backgroundColor: color }"
                        ></button>
                    </div>
                </div>

                <!-- Preview -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Preview</p>
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold"
                            :style="{ backgroundColor: form.color }"
                        >
                            {{ form.name ? form.name.charAt(0).toUpperCase() : 'W' }}
                        </div>
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ form.name || 'Workspace Name' }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <Link
                        :href="route('workspaces.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Workspace' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

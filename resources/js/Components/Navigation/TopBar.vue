<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    BellIcon,
    QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    user: Object,
});

const searchQuery = ref('');
const showUserMenu = ref(false);
const showNotifications = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const search = () => {
    if (searchQuery.value.trim()) {
        // Implement global search
        console.log('Searching:', searchQuery.value);
    }
};
</script>

<template>
    <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-6">
        <!-- Search -->
        <div class="flex-1 max-w-xl">
            <div class="relative">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                    v-model="searchQuery"
                    @keyup.enter="search"
                    type="text"
                    placeholder="Search tasks, projects..."
                    class="w-full pl-10 pr-4 py-2 bg-gray-100 dark:bg-gray-700 border-0 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 dark:text-gray-200 dark:placeholder-gray-400"
                />
            </div>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
            <!-- Help -->
            <button class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <QuestionMarkCircleIcon class="w-5 h-5" />
            </button>

            <!-- Notifications -->
            <div class="relative">
                <button
                    @click="showNotifications = !showNotifications"
                    class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 relative"
                >
                    <BellIcon class="w-5 h-5" />
                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- Notifications dropdown -->
                <div
                    v-if="showNotifications"
                    class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"
                >
                    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Notifications</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No new notifications
                        </div>
                    </div>
                </div>
            </div>

            <!-- User menu -->
            <div class="relative">
                <button
                    @click="showUserMenu = !showUserMenu"
                    class="flex items-center space-x-3 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <img
                        :src="user?.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name || 'U')}&color=7F9CF5&background=EBF4FF`"
                        :alt="user?.name"
                        class="w-8 h-8 rounded-full"
                    />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ user?.name }}
                    </span>
                </button>

                <!-- User dropdown -->
                <div
                    v-if="showUserMenu"
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
                >
                    <Link
                        :href="route('profile.edit')"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        Profile
                    </Link>
                    <Link
                        href="/settings"
                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        Settings
                    </Link>
                    <hr class="my-1 border-gray-200 dark:border-gray-700" />
                    <button
                        @click="logout"
                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        Sign out
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

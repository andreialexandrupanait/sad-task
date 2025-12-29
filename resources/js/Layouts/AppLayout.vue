<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Navigation/Sidebar.vue';
import CommandPalette from '@/Components/CommandPalette.vue';
import {
    Bars3Icon,
    MagnifyingGlassIcon,
    BellIcon,
    PlusIcon,
    ChevronDownIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const workspaces = computed(() => page.props.workspaces || []);

const sidebarCollapsed = ref(false);
const showUserMenu = ref(false);
const showCommandPalette = ref(false);

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

const logout = () => {
    router.post(route('logout'));
};

const userInitials = computed(() => {
    if (!user.value?.name) return '?';
    return user.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
});

// Global keyboard shortcuts
const handleKeyDown = (e) => {
    // Cmd+K or Ctrl+K for command palette
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        showCommandPalette.value = !showCommandPalette.value;
    }

    // Cmd+N or Ctrl+N for new task (opens command palette in create mode)
    if ((e.metaKey || e.ctrlKey) && e.key === 'n' && !e.shiftKey) {
        // Only intercept if not in an input field
        if (document.activeElement?.tagName !== 'INPUT' && document.activeElement?.tagName !== 'TEXTAREA') {
            e.preventDefault();
            showCommandPalette.value = true;
        }
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div class="h-screen flex bg-gray-50 dark:bg-gray-900 overflow-hidden">
        <!-- Sidebar -->
        <Sidebar
            :collapsed="sidebarCollapsed"
            :workspaces="workspaces"
            @toggle="toggleSidebar"
        />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header Bar -->
            <header class="h-12 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 flex-shrink-0">
                <div class="flex items-center space-x-4">
                    <!-- Sidebar Toggle -->
                    <button
                        @click="toggleSidebar"
                        class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400"
                    >
                        <Bars3Icon class="w-5 h-5" />
                    </button>

                    <!-- Breadcrumb / Page Title -->
                    <div class="flex items-center text-sm">
                        <slot name="header">
                            <span class="text-gray-900 dark:text-white font-medium">Dashboard</span>
                        </slot>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- Search / Command Palette Trigger -->
                    <button
                        @click="showCommandPalette = true"
                        class="flex items-center space-x-2 px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                    >
                        <MagnifyingGlassIcon class="w-4 h-4" />
                        <span class="hidden sm:inline">Search...</span>
                        <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-xs bg-gray-200 dark:bg-gray-600 rounded">⌘K</kbd>
                    </button>

                    <!-- Quick Add -->
                    <button
                        @click="showCommandPalette = true"
                        class="p-1.5 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white transition-colors"
                        title="Create new (⌘N)"
                    >
                        <PlusIcon class="w-5 h-5" />
                    </button>

                    <!-- Notifications -->
                    <button class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 relative">
                        <BellIcon class="w-5 h-5" />
                        <span class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- User Menu -->
                    <div class="relative">
                        <button
                            @click="showUserMenu = !showUserMenu"
                            class="flex items-center space-x-2 p-1 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-medium">
                                {{ userInitials }}
                            </div>
                            <ChevronDownIcon class="w-4 h-4 text-gray-500" />
                        </button>

                        <!-- Dropdown -->
                        <div
                            v-if="showUserMenu"
                            class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
                        >
                            <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user?.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
                            </div>
                            <button
                                @click="logout"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                            >
                                Sign out
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto">
                <slot />
            </main>
        </div>

        <!-- Click outside to close menus -->
        <div
            v-if="showUserMenu"
            class="fixed inset-0 z-40"
            @click="showUserMenu = false"
        ></div>

        <!-- Command Palette -->
        <CommandPalette
            :show="showCommandPalette"
            @close="showCommandPalette = false"
            @open="showCommandPalette = true"
        />
    </div>
</template>

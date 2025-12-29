<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    HomeIcon,
    InboxIcon,
    CalendarIcon,
    ClockIcon,
    DocumentTextIcon,
    PlusIcon,
    ChevronRightIcon,
    ChevronDownIcon,
    FolderIcon,
    ListBulletIcon,
    EllipsisHorizontalIcon,
    Cog6ToothIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    collapsed: Boolean,
    workspaces: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['toggle']);

const page = usePage();

const currentWorkspace = computed(() => props.workspaces[0]);
const spaces = computed(() => currentWorkspace.value?.spaces || []);

const expandedSpaces = ref({});
const expandedFolders = ref({});
const showFavorites = ref(true);

// Initialize expanded state
spaces.value.forEach(space => {
    expandedSpaces.value[space.id] = true;
});

const toggleSpace = (spaceId) => {
    expandedSpaces.value[spaceId] = !expandedSpaces.value[spaceId];
};

const toggleFolder = (folderId) => {
    expandedFolders.value[folderId] = !expandedFolders.value[folderId];
};

const getSpaceIcon = (space) => {
    return space.icon || '📁';
};
</script>

<template>
    <aside
        class="h-full bg-[#1a1d21] text-gray-300 transition-all duration-200 flex flex-col flex-shrink-0"
        :class="collapsed ? 'w-[52px]' : 'w-[240px]'"
    >
        <!-- Workspace Header -->
        <div class="h-12 flex items-center px-3 border-b border-gray-800/50">
            <template v-if="!collapsed">
                <div class="flex items-center flex-1 min-w-0">
                    <div
                        class="w-7 h-7 rounded-md flex items-center justify-center text-white text-sm font-semibold flex-shrink-0"
                        :style="{ backgroundColor: currentWorkspace?.color || '#7c3aed' }"
                    >
                        {{ currentWorkspace?.name?.charAt(0)?.toUpperCase() || 'S' }}
                    </div>
                    <span class="ml-2 font-semibold text-white text-sm truncate">
                        {{ currentWorkspace?.name || 'Workspace' }}
                    </span>
                    <ChevronDownIcon class="w-4 h-4 ml-1 text-gray-500 flex-shrink-0" />
                </div>
            </template>
            <template v-else>
                <div
                    class="w-7 h-7 rounded-md flex items-center justify-center text-white text-sm font-semibold mx-auto"
                    :style="{ backgroundColor: currentWorkspace?.color || '#7c3aed' }"
                >
                    {{ currentWorkspace?.name?.charAt(0)?.toUpperCase() || 'S' }}
                </div>
            </template>
        </div>

        <!-- Search (when expanded) -->
        <div v-if="!collapsed" class="px-2 py-2">
            <button class="w-full flex items-center px-2 py-1.5 text-sm text-gray-400 hover:text-gray-200 hover:bg-gray-800/50 rounded-md transition-colors">
                <MagnifyingGlassIcon class="w-4 h-4 mr-2" />
                <span>Search</span>
            </button>
        </div>

        <!-- Main Navigation -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden px-2 py-1" style="scrollbar-width: thin;">
            <!-- Quick Links -->
            <div class="space-y-0.5 mb-3">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center px-2 py-1.5 rounded-md hover:bg-gray-800/50 transition-colors group"
                    :class="{ 'justify-center': collapsed }"
                >
                    <HomeIcon class="w-4 h-4 text-gray-400 group-hover:text-gray-200 flex-shrink-0" />
                    <span v-if="!collapsed" class="ml-2 text-sm">Home</span>
                </Link>

                <button
                    class="w-full flex items-center px-2 py-1.5 rounded-md hover:bg-gray-800/50 transition-colors group"
                    :class="{ 'justify-center': collapsed }"
                >
                    <InboxIcon class="w-4 h-4 text-gray-400 group-hover:text-gray-200 flex-shrink-0" />
                    <span v-if="!collapsed" class="ml-2 text-sm">Inbox</span>
                    <span v-if="!collapsed" class="ml-auto text-xs bg-indigo-600 text-white px-1.5 py-0.5 rounded-full">3</span>
                </button>

                <button
                    class="w-full flex items-center px-2 py-1.5 rounded-md hover:bg-gray-800/50 transition-colors group"
                    :class="{ 'justify-center': collapsed }"
                >
                    <DocumentTextIcon class="w-4 h-4 text-gray-400 group-hover:text-gray-200 flex-shrink-0" />
                    <span v-if="!collapsed" class="ml-2 text-sm">Docs</span>
                </button>
            </div>

            <!-- Favorites Section -->
            <div v-if="!collapsed" class="mb-3">
                <button
                    @click="showFavorites = !showFavorites"
                    class="w-full flex items-center px-2 py-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-300"
                >
                    <ChevronRightIcon
                        class="w-3 h-3 mr-1 transition-transform"
                        :class="{ 'rotate-90': showFavorites }"
                    />
                    Favorites
                </button>
                <div v-if="showFavorites" class="mt-1 space-y-0.5">
                    <div class="px-2 py-1.5 text-xs text-gray-500 italic">
                        No favorites yet
                    </div>
                </div>
            </div>

            <!-- Spaces Section -->
            <div v-if="!collapsed" class="mb-3">
                <div class="flex items-center justify-between px-2 py-1 group">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Spaces</span>
                    <button class="opacity-0 group-hover:opacity-100 p-0.5 hover:bg-gray-700 rounded transition-opacity">
                        <PlusIcon class="w-3.5 h-3.5 text-gray-400" />
                    </button>
                </div>

                <div class="mt-1 space-y-0.5">
                    <div v-for="space in spaces" :key="space.id" class="space-y-0.5">
                        <!-- Space Header -->
                        <div class="group">
                            <button
                                @click="toggleSpace(space.id)"
                                class="w-full flex items-center px-2 py-1.5 rounded-md hover:bg-gray-800/50 transition-colors"
                            >
                                <ChevronRightIcon
                                    class="w-3 h-3 mr-1 text-gray-500 transition-transform flex-shrink-0"
                                    :class="{ 'rotate-90': expandedSpaces[space.id] }"
                                />
                                <div
                                    class="w-2 h-2 rounded-sm mr-2 flex-shrink-0"
                                    :style="{ backgroundColor: space.color || '#8b5cf6' }"
                                ></div>
                                <span class="text-sm truncate flex-1 text-left">{{ space.name }}</span>
                                <EllipsisHorizontalIcon class="w-4 h-4 text-gray-500 opacity-0 group-hover:opacity-100 flex-shrink-0" />
                            </button>
                        </div>

                        <!-- Space Content (Folders & Lists) -->
                        <div v-if="expandedSpaces[space.id]" class="ml-3 space-y-0.5">
                            <!-- Folders -->
                            <div v-for="folder in (space.folders || [])" :key="folder.id">
                                <button
                                    @click="toggleFolder(folder.id)"
                                    class="w-full flex items-center px-2 py-1 rounded-md hover:bg-gray-800/50 transition-colors group"
                                >
                                    <ChevronRightIcon
                                        class="w-3 h-3 mr-1 text-gray-500 transition-transform"
                                        :class="{ 'rotate-90': expandedFolders[folder.id] }"
                                    />
                                    <FolderIcon class="w-4 h-4 mr-2 text-gray-400" />
                                    <span class="text-sm truncate">{{ folder.name }}</span>
                                </button>

                                <!-- Lists in Folder -->
                                <div v-if="expandedFolders[folder.id]" class="ml-5 space-y-0.5">
                                    <Link
                                        v-for="list in (folder.task_lists || [])"
                                        :key="list.id"
                                        :href="route('lists.show', [currentWorkspace?.id, space.id, list.id])"
                                        class="flex items-center px-2 py-1 rounded-md hover:bg-gray-800/50 transition-colors group"
                                    >
                                        <ListBulletIcon class="w-4 h-4 mr-2 text-gray-400" />
                                        <span class="text-sm truncate">{{ list.name }}</span>
                                    </Link>
                                </div>
                            </div>

                            <!-- Lists without folders -->
                            <Link
                                v-for="list in (space.lists || [])"
                                :key="list.id"
                                :href="route('lists.show', [currentWorkspace?.id, space.id, list.id])"
                                class="flex items-center px-2 py-1 rounded-md hover:bg-gray-800/50 transition-colors group"
                            >
                                <span class="w-3 h-3 mr-1"></span>
                                <ListBulletIcon class="w-4 h-4 mr-2 text-gray-400" />
                                <span class="text-sm truncate">{{ list.name }}</span>
                            </Link>

                            <!-- Add List Button -->
                            <button class="w-full flex items-center px-2 py-1 rounded-md hover:bg-gray-800/50 transition-colors text-gray-500 hover:text-gray-300">
                                <span class="w-3 h-3 mr-1"></span>
                                <PlusIcon class="w-4 h-4 mr-2" />
                                <span class="text-sm">Add List</span>
                            </button>
                        </div>
                    </div>

                    <!-- Add Space Button -->
                    <button class="w-full flex items-center px-2 py-1.5 rounded-md hover:bg-gray-800/50 transition-colors text-gray-500 hover:text-gray-300">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        <span class="text-sm">Add Space</span>
                    </button>
                </div>
            </div>

            <!-- Collapsed Spaces Icons -->
            <div v-if="collapsed" class="space-y-1">
                <button
                    v-for="space in spaces"
                    :key="space.id"
                    class="w-full flex items-center justify-center py-2 rounded-md hover:bg-gray-800/50 transition-colors"
                    :title="space.name"
                >
                    <div
                        class="w-6 h-6 rounded-md flex items-center justify-center text-white text-xs font-medium"
                        :style="{ backgroundColor: space.color || '#8b5cf6' }"
                    >
                        {{ space.name?.charAt(0)?.toUpperCase() }}
                    </div>
                </button>
            </div>
        </nav>

        <!-- Bottom Section -->
        <div class="border-t border-gray-800/50 p-2">
            <button
                class="w-full flex items-center px-2 py-1.5 rounded-md hover:bg-gray-800/50 transition-colors group"
                :class="{ 'justify-center': collapsed }"
            >
                <Cog6ToothIcon class="w-4 h-4 text-gray-400 group-hover:text-gray-200 flex-shrink-0" />
                <span v-if="!collapsed" class="ml-2 text-sm">Settings</span>
            </button>
        </div>
    </aside>
</template>

<style scoped>
/* Custom scrollbar for the sidebar */
nav::-webkit-scrollbar {
    width: 6px;
}

nav::-webkit-scrollbar-track {
    background: transparent;
}

nav::-webkit-scrollbar-thumb {
    background: #374151;
    border-radius: 3px;
}

nav::-webkit-scrollbar-thumb:hover {
    background: #4b5563;
}
</style>

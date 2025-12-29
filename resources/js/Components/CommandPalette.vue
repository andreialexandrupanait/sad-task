<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    PlusIcon,
    HomeIcon,
    FolderIcon,
    ListBulletIcon,
    DocumentTextIcon,
    ClockIcon,
    ArrowRightIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();
const searchQuery = ref('');
const selectedIndex = ref(0);
const searchInput = ref(null);
const mode = ref('search'); // 'search' | 'create-task'

// New task form
const newTaskTitle = ref('');

const workspaces = computed(() => page.props.workspaces || []);

// Build searchable items
const allItems = computed(() => {
    const items = [];

    // Quick actions
    items.push({
        id: 'action-create-task',
        type: 'action',
        title: 'Create new task',
        subtitle: 'Start typing to create a task',
        icon: PlusIcon,
        action: () => {
            mode.value = 'create-task';
            newTaskTitle.value = searchQuery.value;
            searchQuery.value = '';
        },
    });

    items.push({
        id: 'action-home',
        type: 'action',
        title: 'Go to Home',
        subtitle: 'Dashboard',
        icon: HomeIcon,
        action: () => {
            router.visit(route('dashboard'));
            emit('close');
        },
    });

    // Workspaces, Spaces, and Lists
    workspaces.value.forEach(workspace => {
        items.push({
            id: `workspace-${workspace.id}`,
            type: 'workspace',
            title: workspace.name,
            subtitle: `${workspace.spaces?.length || 0} spaces`,
            icon: FolderIcon,
            color: workspace.color,
            action: () => {
                router.visit(route('workspaces.show', workspace.id));
                emit('close');
            },
        });

        workspace.spaces?.forEach(space => {
            items.push({
                id: `space-${space.id}`,
                type: 'space',
                title: space.name,
                subtitle: `${workspace.name}`,
                icon: FolderIcon,
                color: space.color,
                action: () => {
                    router.visit(route('spaces.show', [workspace.id, space.id]));
                    emit('close');
                },
            });

            // Folderless lists
            space.folderless_lists?.forEach(list => {
                items.push({
                    id: `list-${list.id}`,
                    type: 'list',
                    title: list.name,
                    subtitle: `${workspace.name} / ${space.name}`,
                    icon: ListBulletIcon,
                    action: () => {
                        router.visit(route('lists.show', [workspace.id, space.id, list.id]));
                        emit('close');
                    },
                });
            });

            // Folder lists
            space.folders?.forEach(folder => {
                folder.task_lists?.forEach(list => {
                    items.push({
                        id: `list-${list.id}`,
                        type: 'list',
                        title: list.name,
                        subtitle: `${workspace.name} / ${space.name} / ${folder.name}`,
                        icon: ListBulletIcon,
                        action: () => {
                            router.visit(route('lists.show', [workspace.id, space.id, list.id]));
                            emit('close');
                        },
                    });
                });
            });
        });
    });

    return items;
});

const filteredItems = computed(() => {
    if (!searchQuery.value.trim()) {
        return allItems.value.slice(0, 10);
    }

    const query = searchQuery.value.toLowerCase();
    return allItems.value
        .filter(item =>
            item.title.toLowerCase().includes(query) ||
            item.subtitle?.toLowerCase().includes(query)
        )
        .slice(0, 10);
});

const groupedItems = computed(() => {
    const groups = {};

    filteredItems.value.forEach(item => {
        const groupName = getGroupName(item.type);
        if (!groups[groupName]) {
            groups[groupName] = [];
        }
        groups[groupName].push(item);
    });

    return groups;
});

const flatItems = computed(() => {
    const items = [];
    Object.values(groupedItems.value).forEach(group => {
        items.push(...group);
    });
    return items;
});

function getGroupName(type) {
    const names = {
        action: 'Quick Actions',
        workspace: 'Workspaces',
        space: 'Spaces',
        list: 'Lists',
        task: 'Tasks',
    };
    return names[type] || 'Other';
}

function handleKeyDown(e) {
    if (mode.value === 'create-task') {
        if (e.key === 'Escape') {
            mode.value = 'search';
            newTaskTitle.value = '';
        }
        return;
    }

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, flatItems.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const item = flatItems.value[selectedIndex.value];
        if (item?.action) {
            item.action();
        }
    } else if (e.key === 'Escape') {
        emit('close');
    }
}

function selectItem(item) {
    if (item?.action) {
        item.action();
    }
}

watch(searchQuery, () => {
    selectedIndex.value = 0;
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        nextTick(() => {
            searchInput.value?.focus();
        });
        mode.value = 'search';
        searchQuery.value = '';
        newTaskTitle.value = '';
        selectedIndex.value = 0;
    }
});

// Global keyboard shortcut
function handleGlobalKeyDown(e) {
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        if (props.show) {
            emit('close');
        } else {
            emit('open');
        }
    }
}
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
                class="fixed inset-0 z-50 overflow-y-auto"
                @keydown="handleKeyDown"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                    @click="emit('close')"
                ></div>

                <!-- Modal -->
                <div class="flex min-h-full items-start justify-center p-4 pt-[15vh]">
                    <Transition
                        enter-active-class="duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="show"
                            class="w-full max-w-xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden"
                        >
                            <!-- Search Mode -->
                            <template v-if="mode === 'search'">
                                <!-- Search Input -->
                                <div class="flex items-center px-4 border-b border-gray-200 dark:border-gray-700">
                                    <MagnifyingGlassIcon class="w-5 h-5 text-gray-400" />
                                    <input
                                        ref="searchInput"
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search or type a command..."
                                        class="flex-1 px-3 py-4 bg-transparent border-0 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-0 text-sm"
                                    />
                                    <div class="flex items-center gap-1 text-xs text-gray-400">
                                        <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">esc</kbd>
                                        <span>to close</span>
                                    </div>
                                </div>

                                <!-- Results -->
                                <div class="max-h-96 overflow-y-auto py-2">
                                    <div
                                        v-for="(items, groupName) in groupedItems"
                                        :key="groupName"
                                        class="mb-2"
                                    >
                                        <div class="px-4 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            {{ groupName }}
                                        </div>
                                        <div
                                            v-for="(item, idx) in items"
                                            :key="item.id"
                                            @click="selectItem(item)"
                                            @mouseenter="selectedIndex = flatItems.indexOf(item)"
                                            class="flex items-center px-4 py-2 cursor-pointer transition-colors"
                                            :class="flatItems.indexOf(item) === selectedIndex
                                                ? 'bg-indigo-50 dark:bg-indigo-900/30'
                                                : 'hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                                        >
                                            <div
                                                v-if="item.color"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 text-white"
                                                :style="{ backgroundColor: item.color }"
                                            >
                                                <component :is="item.icon" class="w-4 h-4" />
                                            </div>
                                            <div
                                                v-else
                                                class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center mr-3 text-gray-500 dark:text-gray-400"
                                            >
                                                <component :is="item.icon" class="w-4 h-4" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ item.title }}
                                                </p>
                                                <p v-if="item.subtitle" class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    {{ item.subtitle }}
                                                </p>
                                            </div>
                                            <ArrowRightIcon
                                                v-if="flatItems.indexOf(item) === selectedIndex"
                                                class="w-4 h-4 text-indigo-500"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        v-if="Object.keys(groupedItems).length === 0"
                                        class="px-4 py-8 text-center text-gray-500 dark:text-gray-400"
                                    >
                                        <MagnifyingGlassIcon class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                        <p class="text-sm">No results found</p>
                                        <p class="text-xs mt-1">Try a different search term</p>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="flex items-center justify-between px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-xs text-gray-500">
                                    <div class="flex items-center gap-4">
                                        <span class="flex items-center gap-1">
                                            <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded shadow-sm">↑</kbd>
                                            <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded shadow-sm">↓</kbd>
                                            to navigate
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded shadow-sm">↵</kbd>
                                            to select
                                        </span>
                                    </div>
                                    <span class="flex items-center gap-1">
                                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded shadow-sm">⌘</kbd>
                                        <kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-700 rounded shadow-sm">K</kbd>
                                        to toggle
                                    </span>
                                </div>
                            </template>

                            <!-- Create Task Mode -->
                            <template v-else-if="mode === 'create-task'">
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            Create Task
                                        </h3>
                                        <button
                                            @click="mode = 'search'"
                                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded"
                                        >
                                            <XMarkIcon class="w-5 h-5" />
                                        </button>
                                    </div>

                                    <input
                                        v-model="newTaskTitle"
                                        type="text"
                                        placeholder="Task title..."
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        autofocus
                                    />

                                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                        Select a list to add this task to:
                                    </p>

                                    <div class="mt-2 max-h-48 overflow-y-auto space-y-1">
                                        <template v-for="workspace in workspaces" :key="workspace.id">
                                            <template v-for="space in workspace.spaces" :key="space.id">
                                                <template v-for="list in (space.folderless_lists || [])" :key="list.id">
                                                    <button
                                                        class="w-full flex items-center px-3 py-2 text-left rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                                    >
                                                        <ListBulletIcon class="w-4 h-4 text-gray-400 mr-2" />
                                                        <span class="text-sm text-gray-900 dark:text-white">{{ list.name }}</span>
                                                        <span class="ml-auto text-xs text-gray-500">{{ workspace.name }}</span>
                                                    </button>
                                                </template>
                                            </template>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

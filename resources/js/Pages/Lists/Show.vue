<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ListView from '@/Components/Views/ListView.vue';
import BoardView from '@/Components/Views/BoardView.vue';
import CalendarView from '@/Components/Views/CalendarView.vue';
import TaskModal from '@/Components/Tasks/TaskModal.vue';
import CreateTaskInput from '@/Components/Tasks/CreateTaskInput.vue';
import KeyboardShortcutsHelp from '@/Components/KeyboardShortcutsHelp.vue';
import {
    Bars3Icon,
    ViewColumnsIcon,
    CalendarIcon,
    ChartBarIcon,
    FunnelIcon,
    PlusIcon,
    QuestionMarkCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    workspace: Object,
    space: Object,
    list: Object,
    tasks: Array,
    view: String,
    filters: Object,
});

const currentView = ref(props.view || 'list');
const showFilters = ref(false);
const selectedTask = ref(null);
const showTaskModal = ref(false);
const showShortcutsHelp = ref(false);
const selectedTaskIndex = ref(-1);
const showAddTaskInput = ref(false);

const views = [
    { id: 'list', name: 'List', icon: Bars3Icon },
    { id: 'board', name: 'Board', icon: ViewColumnsIcon },
    { id: 'calendar', name: 'Calendar', icon: CalendarIcon },
    { id: 'gantt', name: 'Gantt', icon: ChartBarIcon },
];

const tasksByStatus = computed(() => {
    const grouped = {};
    props.list.statuses.forEach(status => {
        grouped[status.id] = {
            status,
            tasks: props.tasks.filter(task => task.status_id === status.id),
        };
    });
    return grouped;
});

const changeView = (viewId) => {
    currentView.value = viewId;
    selectedTaskIndex.value = -1;
    router.get(route('lists.show', [props.workspace, props.space, props.list]), {
        view: viewId,
        ...props.filters,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const openTask = (task) => {
    selectedTask.value = task;
    showTaskModal.value = true;
};

const closeTaskModal = () => {
    showTaskModal.value = false;
    selectedTask.value = null;
};

const handleTaskCreated = (task) => {
    router.reload({ only: ['tasks'] });
    showAddTaskInput.value = false;
};

const handleTaskUpdated = () => {
    router.reload({ only: ['tasks'] });
    closeTaskModal();
};

const handleTasksReordered = (reorderedTasks) => {
    router.post(route('tasks.reorder', [props.workspace, props.space, props.list]), {
        tasks: reorderedTasks,
    }, {
        preserveScroll: true,
    });
};

// Keyboard shortcuts
const isInputFocused = () => {
    const activeElement = document.activeElement;
    const tagName = activeElement?.tagName?.toLowerCase();
    return tagName === 'input' || tagName === 'textarea' || activeElement?.isContentEditable;
};

const toggleTaskComplete = (task) => {
    if (!task) return;

    const closedStatus = props.list.statuses.find(s => s.is_closed);
    const openStatus = props.list.statuses.find(s => s.type === 'open' || s.is_default);

    let updates = {};
    if (task.completed_at) {
        if (openStatus) {
            updates = { status_id: openStatus.id, completed_at: null };
        }
    } else {
        if (closedStatus) {
            updates = { status_id: closedStatus.id, completed_at: new Date().toISOString() };
        }
    }

    if (Object.keys(updates).length) {
        router.put(
            route('tasks.update', [props.workspace.id, props.space.id, props.list.id, task.id]),
            updates,
            { preserveScroll: true }
        );
    }
};

const handleKeyDown = (e) => {
    // Don't handle if in input or modal is open (except for escape)
    if (isInputFocused() && e.key !== 'Escape') return;

    // If modal is open, only handle escape
    if (showTaskModal.value) {
        if (e.key === 'Escape') {
            e.preventDefault();
            closeTaskModal();
        }
        return;
    }

    // If shortcuts help is open
    if (showShortcutsHelp.value) {
        if (e.key === 'Escape' || e.key === '?') {
            e.preventDefault();
            showShortcutsHelp.value = false;
        }
        return;
    }

    const taskList = props.tasks;
    const currentIndex = selectedTaskIndex.value;

    switch (e.key.toLowerCase()) {
        case 'j':
        case 'arrowdown':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                if (currentIndex < taskList.length - 1) {
                    selectedTaskIndex.value = currentIndex + 1;
                } else if (currentIndex === -1 && taskList.length > 0) {
                    selectedTaskIndex.value = 0;
                }
                scrollToSelected();
            }
            break;

        case 'k':
        case 'arrowup':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                if (currentIndex > 0) {
                    selectedTaskIndex.value = currentIndex - 1;
                } else if (currentIndex === -1 && taskList.length > 0) {
                    selectedTaskIndex.value = taskList.length - 1;
                }
                scrollToSelected();
            }
            break;

        case 'enter':
            e.preventDefault();
            if (currentIndex >= 0 && currentIndex < taskList.length) {
                openTask(taskList[currentIndex]);
            }
            break;

        case 'escape':
            e.preventDefault();
            if (selectedTaskIndex.value >= 0) {
                selectedTaskIndex.value = -1;
            }
            break;

        case 'x':
        case 'c':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                if (currentIndex >= 0 && currentIndex < taskList.length) {
                    toggleTaskComplete(taskList[currentIndex]);
                }
            }
            break;

        case 'n':
            if (!e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                showAddTaskInput.value = true;
            }
            break;

        case '?':
            e.preventDefault();
            showShortcutsHelp.value = true;
            break;

        case 'g':
            // Go to first task
            e.preventDefault();
            if (taskList.length > 0) {
                selectedTaskIndex.value = 0;
                scrollToSelected();
            }
            break;

        case '1':
            e.preventDefault();
            changeView('list');
            break;

        case '2':
            e.preventDefault();
            changeView('board');
            break;

        case '3':
            e.preventDefault();
            changeView('calendar');
            break;
    }
};

const scrollToSelected = () => {
    setTimeout(() => {
        const selected = document.querySelector('[data-task-selected="true"]');
        if (selected) {
            selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }, 10);
};

onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});

// Watch for task changes to reset selection if needed
watch(() => props.tasks, () => {
    if (selectedTaskIndex.value >= props.tasks.length) {
        selectedTaskIndex.value = props.tasks.length - 1;
    }
});
</script>

<template>
    <AppLayout :workspace="workspace" :space="space">
        <div class="h-full flex flex-col p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                        <span>{{ workspace.name }}</span>
                        <span>/</span>
                        <span>{{ space.name }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <span
                            v-if="list.color"
                            class="w-3 h-3 rounded-full mr-3"
                            :style="{ backgroundColor: list.color }"
                        ></span>
                        {{ list.name }}
                    </h1>
                </div>

                <div class="flex items-center space-x-2">
                    <!-- View switcher -->
                    <div class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                        <button
                            v-for="(view, index) in views"
                            :key="view.id"
                            @click="changeView(view.id)"
                            class="flex items-center px-3 py-1.5 text-sm rounded-md transition-colors"
                            :class="currentView === view.id
                                ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm'
                                : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                            :title="`${view.name} (${index + 1})`"
                        >
                            <component :is="view.icon" class="w-4 h-4 mr-1.5" />
                            {{ view.name }}
                        </button>
                    </div>

                    <!-- Filters -->
                    <button
                        @click="showFilters = !showFilters"
                        class="flex items-center px-3 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white bg-gray-100 dark:bg-gray-800 rounded-lg"
                    >
                        <FunnelIcon class="w-4 h-4 mr-1.5" />
                        Filters
                    </button>

                    <!-- Keyboard shortcuts help -->
                    <button
                        @click="showShortcutsHelp = true"
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        title="Keyboard shortcuts (?)"
                    >
                        <QuestionMarkCircleIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Create task input -->
            <CreateTaskInput
                v-if="showAddTaskInput || !tasks.length"
                :workspace="workspace"
                :space="space"
                :list="list"
                @created="handleTaskCreated"
                @cancel="showAddTaskInput = false"
                class="mb-4"
            />

            <!-- Views -->
            <div class="flex-1 overflow-hidden">
                <ListView
                    v-if="currentView === 'list'"
                    :tasks="tasks"
                    :statuses="list.statuses"
                    :workspace="workspace"
                    :space="space"
                    :list="list"
                    :selected-index="selectedTaskIndex"
                    group-by="status"
                    @open-task="openTask"
                    @reorder="handleTasksReordered"
                    @select-task="(idx) => selectedTaskIndex = idx"
                />

                <BoardView
                    v-else-if="currentView === 'board'"
                    :tasks-by-status="tasksByStatus"
                    :workspace="workspace"
                    :space="space"
                    :list="list"
                    @open-task="openTask"
                    @reorder="handleTasksReordered"
                    @task-created="handleTaskCreated"
                />

                <CalendarView
                    v-else-if="currentView === 'calendar'"
                    :tasks="tasks"
                    :statuses="list.statuses"
                    :workspace="workspace"
                    :space="space"
                    :list="list"
                    @open-task="openTask"
                />

                <div
                    v-else-if="currentView === 'gantt'"
                    class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400"
                >
                    Gantt view coming soon
                </div>
            </div>

            <!-- Task modal -->
            <TaskModal
                v-if="showTaskModal && selectedTask"
                :task="selectedTask"
                :workspace="workspace"
                :space="space"
                :list="list"
                @close="closeTaskModal"
                @updated="handleTaskUpdated"
            />

            <!-- Keyboard shortcuts help modal -->
            <KeyboardShortcutsHelp
                :show="showShortcutsHelp"
                @close="showShortcutsHelp = false"
            />
        </div>
    </AppLayout>
</template>

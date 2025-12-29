<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import TaskRow from '@/Components/Tasks/TaskRow.vue';
import {
    PlusIcon,
    ChevronDownIcon,
    ChevronRightIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    tasks: Array,
    statuses: Array,
    workspace: Object,
    space: Object,
    list: Object,
    groupBy: {
        type: String,
        default: 'status',
    },
    sortBy: {
        type: String,
        default: 'position',
    },
    selectedIndex: {
        type: Number,
        default: -1,
    },
});

const emit = defineEmits(['open-task', 'reorder', 'update-grouping', 'update-sorting', 'select-task']);

const localTasks = ref([...props.tasks]);
const drag = ref(false);
const collapsedGroups = ref({});
const addTaskInGroup = ref(null);
const newTaskTitle = ref('');

// Watch for external task updates
watch(() => props.tasks, (newTasks) => {
    localTasks.value = [...newTasks];
}, { deep: true });

// Group tasks by status (ClickUp default)
const groupedTasks = computed(() => {
    if (props.groupBy === 'none') {
        return [{
            id: 'all',
            name: 'All Tasks',
            color: '#6b7280',
            tasks: localTasks.value,
            count: localTasks.value.length,
        }];
    }

    if (props.groupBy === 'status') {
        return props.statuses.map(status => ({
            id: status.id,
            name: status.name,
            color: status.color || '#6b7280',
            isClosed: status.is_closed,
            tasks: localTasks.value.filter(t => t.status_id === status.id),
            count: localTasks.value.filter(t => t.status_id === status.id).length,
        }));
    }

    if (props.groupBy === 'priority') {
        const priorityConfig = [
            { id: 1, name: 'Urgent', color: '#ef4444' },
            { id: 2, name: 'High', color: '#f97316' },
            { id: 3, name: 'Normal', color: '#3b82f6' },
            { id: 4, name: 'Low', color: '#6b7280' },
        ];
        return priorityConfig.map(p => ({
            ...p,
            tasks: localTasks.value.filter(t => t.priority === p.id),
            count: localTasks.value.filter(t => t.priority === p.id).length,
        }));
    }

    return [];
});

// Get the global index for a task
const getTaskIndex = (task) => {
    return localTasks.value.findIndex(t => t.id === task.id);
};

const isTaskSelected = (task) => {
    return getTaskIndex(task) === props.selectedIndex;
};

const toggleGroup = (groupId) => {
    collapsedGroups.value[groupId] = !collapsedGroups.value[groupId];
};

const isGroupCollapsed = (groupId) => {
    return collapsedGroups.value[groupId] || false;
};

const onDragEnd = (groupId) => {
    drag.value = false;
    // Collect all tasks in order
    const allTasks = [];
    groupedTasks.value.forEach(group => {
        group.tasks.forEach((task, index) => {
            allTasks.push({
                id: task.id,
                position: allTasks.length,
                status_id: props.groupBy === 'status' ? group.id : task.status_id,
            });
        });
    });
    emit('reorder', allTasks);
};

const showAddTaskInput = (groupId) => {
    addTaskInGroup.value = groupId;
    newTaskTitle.value = '';
};

const createTask = (group) => {
    if (!newTaskTitle.value.trim()) return;

    const taskData = {
        title: newTaskTitle.value.trim(),
    };

    // Set status if grouping by status
    if (props.groupBy === 'status' && group.id !== 'all') {
        taskData.status_id = group.id;
    }

    // Set priority if grouping by priority
    if (props.groupBy === 'priority') {
        taskData.priority = group.id;
    }

    router.post(
        route('tasks.store', [props.workspace.id, props.space.id, props.list.id]),
        taskData,
        {
            preserveScroll: true,
            onSuccess: () => {
                newTaskTitle.value = '';
                addTaskInGroup.value = null;
            },
        }
    );
};

const cancelAddTask = () => {
    addTaskInGroup.value = null;
    newTaskTitle.value = '';
};

const handleTaskClick = (task) => {
    const idx = getTaskIndex(task);
    emit('select-task', idx);
    emit('open-task', task);
};

const getStatusBadgeClass = (group) => {
    if (group.isClosed) {
        return 'bg-green-500';
    }
    return '';
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header row -->
        <div class="grid grid-cols-12 gap-2 px-4 py-2.5 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            <div class="col-span-5 pl-8">Name</div>
            <div class="col-span-1">Lists</div>
            <div class="col-span-1">Assignee</div>
            <div class="col-span-1">Due date</div>
            <div class="col-span-1">Priority</div>
            <div class="col-span-1">Status</div>
            <div class="col-span-2">Date created</div>
        </div>

        <!-- Status groups -->
        <div v-for="group in groupedTasks" :key="group.id" class="border-b border-gray-200 dark:border-gray-700 last:border-b-0">
            <!-- Group header -->
            <div
                @click="toggleGroup(group.id)"
                class="flex items-center px-4 py-2 bg-gray-50 dark:bg-gray-900/50 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                <button class="mr-2 text-gray-400">
                    <ChevronDownIcon
                        v-if="!isGroupCollapsed(group.id)"
                        class="w-4 h-4 transition-transform"
                    />
                    <ChevronRightIcon
                        v-else
                        class="w-4 h-4"
                    />
                </button>

                <!-- Status badge -->
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold text-white mr-3"
                    :style="{ backgroundColor: group.color }"
                >
                    {{ group.name.toUpperCase() }}
                </span>

                <!-- Task count -->
                <span class="text-xs text-gray-500 dark:text-gray-400 mr-4">
                    {{ group.count }}
                </span>

                <!-- Add task button in header -->
                <button
                    @click.stop="showAddTaskInput(group.id)"
                    class="flex items-center text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <PlusIcon class="w-4 h-4 mr-1" />
                    Add Task
                </button>
            </div>

            <!-- Tasks in group -->
            <div v-show="!isGroupCollapsed(group.id)">
                <draggable
                    v-model="group.tasks"
                    item-key="id"
                    handle=".drag-handle"
                    ghost-class="opacity-50"
                    @start="drag = true"
                    @end="onDragEnd(group.id)"
                    class="divide-y divide-gray-100 dark:divide-gray-700/50"
                >
                    <template #item="{ element: task }">
                        <div
                            :data-task-selected="isTaskSelected(task)"
                            class="transition-colors"
                            :class="{
                                'bg-indigo-50 dark:bg-indigo-900/20 ring-1 ring-indigo-500 ring-inset': isTaskSelected(task),
                                'hover:bg-gray-50 dark:hover:bg-gray-700/30': !isTaskSelected(task),
                            }"
                        >
                            <TaskRow
                                :task="task"
                                :statuses="statuses"
                                :workspace="workspace"
                                :space="space"
                                :list="list"
                                :show-list-name="true"
                                @click="handleTaskClick(task)"
                                @update="$emit('reorder', [])"
                            />
                        </div>
                    </template>
                </draggable>

                <!-- Add task input row -->
                <div
                    v-if="addTaskInGroup === group.id"
                    class="flex items-center px-4 py-2 bg-gray-50 dark:bg-gray-800"
                >
                    <div class="pl-8 flex-1 flex items-center gap-2">
                        <input
                            v-model="newTaskTitle"
                            type="text"
                            placeholder="Task name"
                            class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            @keyup.enter="createTask(group)"
                            @keyup.escape="cancelAddTask"
                            autofocus
                        />
                        <button
                            @click="createTask(group)"
                            class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700"
                        >
                            Save
                        </button>
                        <button
                            @click="cancelAddTask"
                            class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Add task row (always visible) -->
                <div
                    v-else
                    @click="showAddTaskInput(group.id)"
                    class="flex items-center px-4 py-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer transition-colors"
                >
                    <div class="pl-8 flex items-center text-sm">
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Add Task
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div
            v-if="localTasks.length === 0"
            class="px-4 py-12 text-center text-gray-500 dark:text-gray-400"
        >
            <p class="mb-2">No tasks yet</p>
            <button
                @click="showAddTaskInput(groupedTasks[0]?.id)"
                class="inline-flex items-center px-3 py-1.5 text-sm text-indigo-600 hover:text-indigo-700 font-medium"
            >
                <PlusIcon class="w-4 h-4 mr-1" />
                Create your first task
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import TaskCard from '@/Components/Tasks/TaskCard.vue';
import { PlusIcon, EllipsisHorizontalIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    tasksByStatus: Object,
    workspace: Object,
    space: Object,
    list: Object,
});

const emit = defineEmits(['open-task', 'reorder', 'task-created']);

const columns = computed(() => Object.values(props.tasksByStatus));

// Quick add state for each column
const quickAddStates = ref({});
const quickAddTitles = ref({});

const startQuickAdd = (statusId) => {
    quickAddStates.value[statusId] = true;
    quickAddTitles.value[statusId] = '';
};

const cancelQuickAdd = (statusId) => {
    quickAddStates.value[statusId] = false;
    quickAddTitles.value[statusId] = '';
};

const createTask = (statusId) => {
    const title = quickAddTitles.value[statusId]?.trim();
    if (!title || !props.workspace || !props.space || !props.list) return;

    router.post(
        route('tasks.store', [props.workspace.id, props.space.id, props.list.id]),
        {
            title,
            status_id: statusId,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                cancelQuickAdd(statusId);
                emit('task-created');
            },
        }
    );
};

const onTaskMoved = (statusId, event) => {
    if (event.added || event.moved) {
        const reordered = [];

        Object.entries(props.tasksByStatus).forEach(([sId, column]) => {
            column.tasks.forEach((task, index) => {
                reordered.push({
                    id: task.id,
                    position: index,
                    status_id: parseInt(sId),
                });
            });
        });

        emit('reorder', reordered);
    }
};

const getColumnWIPIndicator = (column) => {
    // Could be configured per status
    const wipLimit = column.status.wip_limit || null;
    if (!wipLimit) return null;

    const count = column.tasks.length;
    const isOverLimit = count > wipLimit;

    return {
        count,
        limit: wipLimit,
        isOverLimit,
    };
};
</script>

<template>
    <div class="flex space-x-4 overflow-x-auto pb-4 h-full">
        <div
            v-for="column in columns"
            :key="column.status.id"
            class="flex-shrink-0 w-80 bg-gray-100 dark:bg-gray-800 rounded-xl flex flex-col max-h-full"
            :class="{
                'ring-2 ring-red-500': getColumnWIPIndicator(column)?.isOverLimit,
            }"
        >
            <!-- Column header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-2">
                    <span
                        class="w-3 h-3 rounded-full flex-shrink-0"
                        :style="{ backgroundColor: column.status.color }"
                    ></span>
                    <span class="font-medium text-gray-900 dark:text-white truncate">
                        {{ column.status.name }}
                    </span>
                    <span
                        class="px-1.5 py-0.5 text-xs font-medium rounded"
                        :class="getColumnWIPIndicator(column)?.isOverLimit
                            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                    >
                        {{ column.tasks.length }}
                        <span v-if="getColumnWIPIndicator(column)?.limit">
                            / {{ getColumnWIPIndicator(column).limit }}
                        </span>
                    </span>
                </div>
                <div class="flex items-center space-x-1">
                    <button
                        @click="startQuickAdd(column.status.id)"
                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                        title="Add task"
                    >
                        <PlusIcon class="w-5 h-5" />
                    </button>
                    <button class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                        <EllipsisHorizontalIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Tasks -->
            <div class="flex-1 overflow-y-auto px-2 py-2">
                <draggable
                    :list="column.tasks"
                    group="tasks"
                    item-key="id"
                    ghost-class="opacity-50"
                    drag-class="shadow-lg"
                    class="space-y-2 min-h-[60px]"
                    @change="onTaskMoved(column.status.id, $event)"
                >
                    <template #item="{ element: task }">
                        <TaskCard
                            :task="task"
                            @click="emit('open-task', task)"
                        />
                    </template>
                </draggable>

                <!-- Empty state -->
                <div
                    v-if="column.tasks.length === 0 && !quickAddStates[column.status.id]"
                    class="text-center py-4 text-gray-400 text-sm"
                >
                    No tasks
                </div>
            </div>

            <!-- Quick Add Form -->
            <div class="px-2 pb-2">
                <div
                    v-if="quickAddStates[column.status.id]"
                    class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-2"
                >
                    <textarea
                        v-model="quickAddTitles[column.status.id]"
                        placeholder="Enter task title..."
                        rows="2"
                        class="w-full px-2 py-1.5 text-sm border-0 bg-transparent text-gray-900 dark:text-white placeholder-gray-400 focus:ring-0 resize-none"
                        @keyup.enter.exact="createTask(column.status.id)"
                        @keyup.escape="cancelQuickAdd(column.status.id)"
                        autofocus
                    ></textarea>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-gray-400">Enter to save, Esc to cancel</span>
                        <div class="flex items-center space-x-2">
                            <button
                                @click="cancelQuickAdd(column.status.id)"
                                class="px-2 py-1 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                            >
                                Cancel
                            </button>
                            <button
                                @click="createTask(column.status.id)"
                                class="px-3 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors"
                            >
                                Add
                            </button>
                        </div>
                    </div>
                </div>
                <button
                    v-else
                    @click="startQuickAdd(column.status.id)"
                    class="w-full flex items-center justify-center py-2 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                    <PlusIcon class="w-4 h-4 mr-1" />
                    Add task
                </button>
            </div>
        </div>

        <!-- Add Column Button (for adding new statuses) -->
        <div class="flex-shrink-0 w-80">
            <button class="w-full h-12 flex items-center justify-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 transition-colors">
                <PlusIcon class="w-5 h-5 mr-2" />
                Add status
            </button>
        </div>
    </div>
</template>

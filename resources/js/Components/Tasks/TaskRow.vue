<script setup>
import { ref, computed, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Bars2Icon,
    CheckCircleIcon,
    CalendarIcon,
    UserPlusIcon,
    FlagIcon,
    EllipsisHorizontalIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    task: Object,
    statuses: Array,
    workspace: Object,
    space: Object,
    list: Object,
    showListName: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click', 'update']);

// Inline editing states
const isEditingTitle = ref(false);
const editedTitle = ref('');
const titleInput = ref(null);

const showStatusDropdown = ref(false);
const showPriorityDropdown = ref(false);
const showDatePicker = ref(false);

const priorityOptions = [
    { value: 1, label: 'Urgent', color: '#ef4444', bgClass: 'bg-red-500' },
    { value: 2, label: 'High', color: '#f97316', bgClass: 'bg-orange-500' },
    { value: 3, label: 'Normal', color: '#3b82f6', bgClass: 'bg-blue-500' },
    { value: 4, label: 'Low', color: '#6b7280', bgClass: 'bg-gray-400' },
];

const status = computed(() =>
    props.statuses.find(s => s.id === props.task.status_id)
);

const currentPriority = computed(() =>
    priorityOptions.find(p => p.value === props.task.priority) || priorityOptions[2]
);

const formattedDueDate = computed(() => {
    if (!props.task.due_date) return null;

    const date = new Date(props.task.due_date);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    if (date.toDateString() === today.toDateString()) {
        return 'Today';
    } else if (date.toDateString() === tomorrow.toDateString()) {
        return 'Tomorrow';
    }

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
});

const formattedCreatedDate = computed(() => {
    if (!props.task.created_at) return '-';
    const date = new Date(props.task.created_at);
    return date.toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'short',
    });
});

const isOverdue = computed(() => {
    if (!props.task.due_date || props.task.completed_at) return false;
    return new Date(props.task.due_date) < new Date();
});

// Inline editing methods
const startEditingTitle = () => {
    isEditingTitle.value = true;
    editedTitle.value = props.task.title;
    nextTick(() => {
        titleInput.value?.focus();
        titleInput.value?.select();
    });
};

const saveTitle = () => {
    if (editedTitle.value.trim() && editedTitle.value !== props.task.title) {
        updateTask({ title: editedTitle.value.trim() });
    }
    isEditingTitle.value = false;
};

const cancelEditTitle = () => {
    isEditingTitle.value = false;
    editedTitle.value = '';
};

const toggleComplete = () => {
    const closedStatus = props.statuses.find(s => s.is_closed);
    const openStatus = props.statuses.find(s => s.type === 'open' || s.is_default);

    if (props.task.completed_at) {
        // Reopen task
        if (openStatus) {
            updateTask({ status_id: openStatus.id, completed_at: null });
        }
    } else {
        // Complete task
        if (closedStatus) {
            updateTask({ status_id: closedStatus.id, completed_at: new Date().toISOString() });
        }
    }
};

const changeStatus = (newStatusId) => {
    const newStatus = props.statuses.find(s => s.id === newStatusId);
    const updates = { status_id: newStatusId };

    if (newStatus?.is_closed) {
        updates.completed_at = new Date().toISOString();
    } else if (props.task.completed_at) {
        updates.completed_at = null;
    }

    updateTask(updates);
    showStatusDropdown.value = false;
};

const changePriority = (newPriority) => {
    updateTask({ priority: newPriority });
    showPriorityDropdown.value = false;
};

const changeDueDate = (e) => {
    updateTask({ due_date: e.target.value || null });
    showDatePicker.value = false;
};

const updateTask = (updates) => {
    if (!props.workspace || !props.space || !props.list) {
        emit('update', updates);
        return;
    }

    router.put(
        route('tasks.update', [props.workspace.id, props.space.id, props.list.id, props.task.id]),
        updates,
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('update', updates);
            },
        }
    );
};

// Close dropdowns when clicking outside
const closeDropdowns = () => {
    showStatusDropdown.value = false;
    showPriorityDropdown.value = false;
    showDatePicker.value = false;
};
</script>

<template>
    <div
        class="grid grid-cols-12 gap-2 px-4 py-2.5 cursor-pointer group"
        @click="!isEditingTitle && emit('click')"
    >
        <!-- Task name column -->
        <div class="col-span-5 flex items-center min-w-0">
            <!-- Drag handle -->
            <button class="drag-handle opacity-0 group-hover:opacity-100 cursor-grab text-gray-400 mr-2 flex-shrink-0">
                <Bars2Icon class="w-4 h-4" />
            </button>

            <!-- Complete checkbox -->
            <button
                class="flex-shrink-0 mr-2"
                @click.stop="toggleComplete"
            >
                <CheckCircleSolidIcon
                    v-if="task.completed_at"
                    class="w-5 h-5 text-green-500 hover:text-green-600 transition-colors"
                />
                <CheckCircleIcon
                    v-else
                    class="w-5 h-5 text-gray-300 hover:text-green-500 transition-colors"
                />
            </button>

            <!-- Task title -->
            <div class="min-w-0 flex-1">
                <input
                    v-if="isEditingTitle"
                    ref="titleInput"
                    v-model="editedTitle"
                    type="text"
                    class="w-full px-1 py-0.5 text-sm border border-indigo-500 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                    @click.stop
                    @blur="saveTitle"
                    @keyup.enter="saveTitle"
                    @keyup.escape="cancelEditTitle"
                />
                <p
                    v-else
                    @dblclick.stop="startEditingTitle"
                    class="text-sm truncate"
                    :class="task.completed_at
                        ? 'text-gray-400 line-through'
                        : 'text-gray-900 dark:text-white'"
                    title="Double-click to edit"
                >
                    {{ task.title }}
                </p>
            </div>

            <!-- Subtask indicator -->
            <span
                v-if="task.subtasks?.length"
                class="ml-2 text-xs text-gray-400 flex-shrink-0"
            >
                {{ task.subtasks.filter(s => s.completed_at).length }}/{{ task.subtasks.length }}
            </span>
        </div>

        <!-- Lists column -->
        <div class="col-span-1 flex items-center">
            <span
                v-if="showListName"
                class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 truncate"
            >
                {{ list?.name }}
            </span>
            <span v-else class="text-gray-300">-</span>
        </div>

        <!-- Assignee column -->
        <div class="col-span-1 flex items-center">
            <div
                v-if="task.assignees?.length"
                class="flex -space-x-1"
            >
                <img
                    v-for="assignee in task.assignees.slice(0, 2)"
                    :key="assignee.id"
                    :src="assignee.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(assignee.name)}&size=24&color=7F9CF5&background=EBF4FF`"
                    :alt="assignee.name"
                    :title="assignee.name"
                    class="w-6 h-6 rounded-full border border-white dark:border-gray-800"
                />
                <span
                    v-if="task.assignees.length > 2"
                    class="w-6 h-6 rounded-full border border-white dark:border-gray-800 bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-[10px] text-gray-600 dark:text-gray-300"
                >
                    +{{ task.assignees.length - 2 }}
                </span>
            </div>
            <button
                v-else
                @click.stop
                class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
                <UserPlusIcon class="w-5 h-5" />
            </button>
        </div>

        <!-- Due date column -->
        <div class="col-span-1 flex items-center relative">
            <button
                @click.stop="showDatePicker = !showDatePicker"
                class="text-xs transition-colors"
                :class="formattedDueDate
                    ? (isOverdue ? 'text-red-600 font-medium' : 'text-gray-600 dark:text-gray-400')
                    : 'text-gray-300 opacity-0 group-hover:opacity-100'"
            >
                {{ formattedDueDate || '-' }}
            </button>

            <!-- Date picker dropdown -->
            <div
                v-if="showDatePicker"
                class="absolute top-full left-0 mt-1 z-20 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-2"
                @click.stop
            >
                <input
                    type="date"
                    :value="task.due_date?.split('T')[0] || ''"
                    @change="changeDueDate"
                    class="px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                />
                <button
                    v-if="task.due_date"
                    @click="updateTask({ due_date: null }); showDatePicker = false"
                    class="mt-1 w-full text-xs text-red-600 hover:text-red-700 py-1"
                >
                    Clear
                </button>
            </div>
        </div>

        <!-- Priority column -->
        <div class="col-span-1 flex items-center relative">
            <button
                @click.stop="showPriorityDropdown = !showPriorityDropdown"
                class="flex items-center"
            >
                <FlagIcon
                    class="w-4 h-4"
                    :style="{ color: currentPriority.color }"
                />
            </button>

            <!-- Priority dropdown -->
            <div
                v-if="showPriorityDropdown"
                class="absolute top-full left-0 mt-1 z-20 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-[100px]"
                @click.stop
            >
                <button
                    v-for="option in priorityOptions"
                    :key="option.value"
                    @click="changePriority(option.value)"
                    class="w-full flex items-center px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                    :class="task.priority === option.value ? 'bg-gray-50 dark:bg-gray-700/50' : ''"
                >
                    <FlagIcon class="w-4 h-4 mr-2" :style="{ color: option.color }" />
                    {{ option.label }}
                </button>
            </div>
        </div>

        <!-- Status column -->
        <div class="col-span-1 flex items-center relative">
            <button
                @click.stop="showStatusDropdown = !showStatusDropdown"
                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium text-white truncate max-w-full"
                :style="{ backgroundColor: status?.color || '#6b7280' }"
            >
                {{ status?.name || 'No status' }}
            </button>

            <!-- Status dropdown -->
            <div
                v-if="showStatusDropdown"
                class="absolute top-full left-0 mt-1 z-20 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-[120px]"
                @click.stop
            >
                <button
                    v-for="s in statuses"
                    :key="s.id"
                    @click="changeStatus(s.id)"
                    class="w-full flex items-center px-3 py-1.5 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                    :class="task.status_id === s.id ? 'bg-gray-50 dark:bg-gray-700/50' : ''"
                >
                    <span
                        class="w-2 h-2 rounded-full mr-2"
                        :style="{ backgroundColor: s.color }"
                    ></span>
                    {{ s.name }}
                </button>
            </div>
        </div>

        <!-- Date created column -->
        <div class="col-span-2 flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ formattedCreatedDate }}
            </span>

            <!-- Actions menu -->
            <button
                @click.stop
                class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded"
            >
                <EllipsisHorizontalIcon class="w-5 h-5" />
            </button>
        </div>
    </div>

    <!-- Click outside handler -->
    <Teleport to="body">
        <div
            v-if="showStatusDropdown || showPriorityDropdown || showDatePicker"
            class="fixed inset-0 z-10"
            @click="closeDropdowns"
        ></div>
    </Teleport>
</template>

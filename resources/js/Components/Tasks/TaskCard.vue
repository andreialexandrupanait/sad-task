<script setup>
import { computed } from 'vue';
import {
    ChatBubbleLeftIcon,
    PaperClipIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    task: Object,
});

const emit = defineEmits(['click']);

const priorityColors = {
    1: 'border-l-red-500',
    2: 'border-l-orange-500',
    3: 'border-l-blue-500',
    4: 'border-l-gray-300',
};

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

const isOverdue = computed(() => {
    if (!props.task.due_date || props.task.completed_at) return false;
    return new Date(props.task.due_date) < new Date();
});

const subtaskProgress = computed(() => {
    if (!props.task.subtasks?.length) return null;
    const completed = props.task.subtasks.filter(s => s.completed_at).length;
    return {
        completed,
        total: props.task.subtasks.length,
        percentage: Math.round((completed / props.task.subtasks.length) * 100),
    };
});
</script>

<template>
    <div
        @click="emit('click')"
        class="bg-white dark:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 p-3 cursor-pointer hover:shadow-md transition-shadow border-l-4"
        :class="priorityColors[task.priority]"
    >
        <!-- Title -->
        <p
            class="font-medium text-sm mb-2"
            :class="task.completed_at
                ? 'text-gray-400 line-through'
                : 'text-gray-900 dark:text-white'"
        >
            {{ task.title }}
        </p>

        <!-- Subtask progress -->
        <div v-if="subtaskProgress" class="mb-2">
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                <span>Subtasks</span>
                <span>{{ subtaskProgress.completed }}/{{ subtaskProgress.total }}</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                <div
                    class="bg-green-500 h-1.5 rounded-full transition-all"
                    :style="{ width: `${subtaskProgress.percentage}%` }"
                ></div>
            </div>
        </div>

        <!-- Tags -->
        <div v-if="task.tags?.length" class="flex flex-wrap gap-1 mb-2">
            <span
                v-for="tagRelation in task.tags.slice(0, 3)"
                :key="tagRelation.tag.id"
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                :style="{
                    backgroundColor: tagRelation.tag.color + '20',
                    color: tagRelation.tag.color,
                }"
            >
                {{ tagRelation.tag.name }}
            </span>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 text-gray-400">
                <!-- Due date -->
                <span
                    v-if="formattedDueDate"
                    class="text-xs"
                    :class="isOverdue ? 'text-red-500' : ''"
                >
                    {{ formattedDueDate }}
                </span>

                <!-- Comment count -->
                <span
                    v-if="task.comments_count"
                    class="flex items-center text-xs"
                >
                    <ChatBubbleLeftIcon class="w-3.5 h-3.5 mr-0.5" />
                    {{ task.comments_count }}
                </span>

                <!-- Attachment count -->
                <span
                    v-if="task.attachments_count"
                    class="flex items-center text-xs"
                >
                    <PaperClipIcon class="w-3.5 h-3.5 mr-0.5" />
                    {{ task.attachments_count }}
                </span>

                <!-- Checklist progress -->
                <span
                    v-if="task.checklists?.length"
                    class="flex items-center text-xs"
                >
                    <CheckCircleIcon class="w-3.5 h-3.5 mr-0.5" />
                </span>
            </div>

            <!-- Assignees -->
            <div
                v-if="task.assignees?.length"
                class="flex -space-x-1.5"
            >
                <img
                    v-for="assignee in task.assignees.slice(0, 2)"
                    :key="assignee.id"
                    :src="assignee.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(assignee.name)}&color=7F9CF5&background=EBF4FF&size=24`"
                    :alt="assignee.name"
                    :title="assignee.name"
                    class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-700"
                />
                <span
                    v-if="task.assignees.length > 2"
                    class="w-6 h-6 rounded-full border-2 border-white dark:border-gray-700 bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs text-gray-600 dark:text-gray-300"
                >
                    +{{ task.assignees.length - 2 }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    tasks: Array,
    statuses: Array,
    workspace: Object,
    space: Object,
    list: Object,
});

const emit = defineEmits(['open-task']);

const currentDate = ref(new Date());
const viewMode = ref('month'); // 'month', 'week'

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const currentMonth = computed(() => currentDate.value.getMonth());
const currentYear = computed(() => currentDate.value.getFullYear());

const calendarDays = computed(() => {
    const year = currentYear.value;
    const month = currentMonth.value;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDay = firstDay.getDay();

    const days = [];

    // Previous month days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    for (let i = startingDay - 1; i >= 0; i--) {
        days.push({
            date: new Date(year, month - 1, prevMonthLastDay - i),
            isCurrentMonth: false,
        });
    }

    // Current month days
    for (let i = 1; i <= daysInMonth; i++) {
        days.push({
            date: new Date(year, month, i),
            isCurrentMonth: true,
        });
    }

    // Next month days
    const remainingDays = 42 - days.length;
    for (let i = 1; i <= remainingDays; i++) {
        days.push({
            date: new Date(year, month + 1, i),
            isCurrentMonth: false,
        });
    }

    return days;
});

const tasksByDate = computed(() => {
    const map = {};

    props.tasks.forEach(task => {
        if (!task.due_date) return;

        const date = new Date(task.due_date);
        const key = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;

        if (!map[key]) {
            map[key] = [];
        }
        map[key].push(task);
    });

    return map;
});

const getTasksForDate = (date) => {
    const key = `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`;
    return tasksByDate.value[key] || [];
};

const isToday = (date) => {
    const today = new Date();
    return date.getDate() === today.getDate() &&
           date.getMonth() === today.getMonth() &&
           date.getFullYear() === today.getFullYear();
};

const previousMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

const getStatusColor = (task) => {
    const status = props.statuses.find(s => s.id === task.status_id);
    return status?.color || '#6b7280';
};

const getPriorityClass = (task) => {
    const classes = {
        1: 'border-l-red-500',
        2: 'border-l-orange-500',
        3: 'border-l-blue-500',
        4: 'border-l-gray-300',
    };
    return classes[task.priority] || 'border-l-gray-300';
};

const handleDragStart = (event, task) => {
    event.dataTransfer.setData('taskId', task.id);
    event.dataTransfer.effectAllowed = 'move';
};

const handleDrop = (event, date) => {
    event.preventDefault();
    const taskId = event.dataTransfer.getData('taskId');

    if (!taskId || !props.workspace || !props.space || !props.list) return;

    const task = props.tasks.find(t => t.id === parseInt(taskId));
    if (!task) return;

    const newDueDate = date.toISOString().split('T')[0];

    router.put(
        route('tasks.update', [props.workspace.id, props.space.id, props.list.id, task.id]),
        { due_date: newDueDate },
        { preserveScroll: true }
    );
};

const handleDragOver = (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden h-full flex flex-col">
        <!-- Calendar Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ monthNames[currentMonth] }} {{ currentYear }}
                </h2>
                <div class="flex items-center space-x-1">
                    <button
                        @click="previousMonth"
                        class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400"
                    >
                        <ChevronLeftIcon class="w-5 h-5" />
                    </button>
                    <button
                        @click="nextMonth"
                        class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400"
                    >
                        <ChevronRightIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <button
                    @click="goToToday"
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                    Today
                </button>
                <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                    <button
                        @click="viewMode = 'month'"
                        class="px-3 py-1 text-sm rounded-md transition-colors"
                        :class="viewMode === 'month'
                            ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-400'"
                    >
                        Month
                    </button>
                    <button
                        @click="viewMode = 'week'"
                        class="px-3 py-1 text-sm rounded-md transition-colors"
                        :class="viewMode === 'week'
                            ? 'bg-white dark:bg-gray-600 text-gray-900 dark:text-white shadow-sm'
                            : 'text-gray-600 dark:text-gray-400'"
                    >
                        Week
                    </button>
                </div>
            </div>
        </div>

        <!-- Day Headers -->
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
            <div
                v-for="day in dayNames"
                :key="day"
                class="py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
                {{ day }}
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="flex-1 grid grid-cols-7 auto-rows-fr overflow-hidden">
            <div
                v-for="(day, index) in calendarDays"
                :key="index"
                @drop="handleDrop($event, day.date)"
                @dragover="handleDragOver"
                class="border-b border-r border-gray-200 dark:border-gray-700 p-1 overflow-hidden"
                :class="{
                    'bg-gray-50 dark:bg-gray-900/30': !day.isCurrentMonth,
                    'bg-indigo-50 dark:bg-indigo-900/20': isToday(day.date),
                }"
            >
                <!-- Date number -->
                <div class="flex items-center justify-between mb-1">
                    <span
                        class="text-sm font-medium"
                        :class="{
                            'text-gray-400 dark:text-gray-600': !day.isCurrentMonth,
                            'text-indigo-600 dark:text-indigo-400': isToday(day.date),
                            'text-gray-900 dark:text-white': day.isCurrentMonth && !isToday(day.date),
                        }"
                    >
                        {{ day.date.getDate() }}
                    </span>
                    <span
                        v-if="isToday(day.date)"
                        class="text-xs text-indigo-600 dark:text-indigo-400 font-medium"
                    >
                        Today
                    </span>
                </div>

                <!-- Tasks -->
                <div class="space-y-0.5 overflow-y-auto max-h-[calc(100%-24px)]">
                    <div
                        v-for="task in getTasksForDate(day.date).slice(0, 3)"
                        :key="task.id"
                        draggable="true"
                        @dragstart="handleDragStart($event, task)"
                        @click="emit('open-task', task)"
                        class="px-1.5 py-0.5 text-xs rounded cursor-pointer border-l-2 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 truncate transition-colors"
                        :class="getPriorityClass(task)"
                        :title="task.title"
                    >
                        <span
                            class="w-1.5 h-1.5 rounded-full inline-block mr-1"
                            :style="{ backgroundColor: getStatusColor(task) }"
                        ></span>
                        {{ task.title }}
                    </div>

                    <!-- More indicator -->
                    <div
                        v-if="getTasksForDate(day.date).length > 3"
                        class="text-xs text-gray-500 dark:text-gray-400 px-1.5"
                    >
                        +{{ getTasksForDate(day.date).length - 3 }} more
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center justify-between px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center">
                    <span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span>
                    Urgent
                </span>
                <span class="flex items-center">
                    <span class="w-2 h-2 rounded-full bg-orange-500 mr-1"></span>
                    High
                </span>
                <span class="flex items-center">
                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span>
                    Normal
                </span>
                <span class="flex items-center">
                    <span class="w-2 h-2 rounded-full bg-gray-400 mr-1"></span>
                    Low
                </span>
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Drag tasks to reschedule
            </span>
        </div>
    </div>
</template>

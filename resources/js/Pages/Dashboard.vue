<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    PlusIcon,
    CalendarDaysIcon,
    ClockIcon,
    CheckCircleIcon,
    FlagIcon,
    ArrowRightIcon,
    ExclamationTriangleIcon,
    ChatBubbleLeftIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    workspaces: {
        type: Array,
        default: () => [],
    },
    todayTasks: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({ completed: 0, inProgress: 0, overdue: 0 }),
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const getPriorityColor = (priority) => {
    const colors = {
        urgent: 'text-red-500',
        high: 'text-orange-500',
        medium: 'text-yellow-500',
        low: 'text-blue-500',
    };
    return colors[priority] || 'text-gray-400';
};

const getActivityIcon = (type) => {
    const icons = {
        created: PlusIcon,
        completed: CheckCircleIcon,
        updated: ClockIcon,
        status_changed: ArrowRightIcon,
        comment_added: ChatBubbleLeftIcon,
    };
    return icons[type] || ClockIcon;
};

const getActivityColor = (type) => {
    const colors = {
        created: 'bg-green-100 dark:bg-green-900/30 text-green-600',
        completed: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600',
        updated: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600',
        status_changed: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600',
        comment_added: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600',
    };
    return colors[type] || 'bg-gray-100 dark:bg-gray-700 text-gray-600';
};

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
});

const pendingTasksCount = computed(() => {
    return props.todayTasks.filter(t => t.status !== 'completed').length;
});
</script>

<template>
    <AppLayout>
        <template #header>
            <span class="text-gray-900 dark:text-white font-medium">Home</span>
        </template>

        <div class="p-6 max-w-6xl mx-auto">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ greeting }}, {{ user?.name?.split(' ')[0] || 'there' }}!
                </h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">
                    Here's what's on your plate today
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Today's Tasks -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <div class="flex items-center">
                                <CalendarDaysIcon class="w-5 h-5 text-gray-400 mr-2" />
                                <h2 class="font-semibold text-gray-900 dark:text-white">Today</h2>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ pendingTasksCount }} tasks
                                </span>
                            </div>
                            <button class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                                View all
                            </button>
                        </div>

                        <div v-if="todayTasks.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-for="task in todayTasks"
                                :key="task.id"
                                class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                            >
                                <!-- Checkbox -->
                                <button class="flex-shrink-0">
                                    <CheckCircleSolidIcon
                                        v-if="task.status === 'completed'"
                                        class="w-5 h-5 text-green-500"
                                    />
                                    <div
                                        v-else
                                        class="w-5 h-5 rounded-full border-2 border-gray-300 dark:border-gray-600 hover:border-green-500 transition-colors"
                                    />
                                </button>

                                <!-- Task Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p
                                            class="text-sm truncate"
                                            :class="task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-900 dark:text-white'"
                                        >
                                            {{ task.name }}
                                        </p>
                                        <ExclamationTriangleIcon
                                            v-if="task.is_overdue"
                                            class="w-4 h-4 text-red-500 flex-shrink-0"
                                            title="Overdue"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ task.list }}
                                    </p>
                                </div>

                                <!-- Priority Flag -->
                                <FlagIcon
                                    class="w-4 h-4 flex-shrink-0"
                                    :class="getPriorityColor(task.priority)"
                                />
                            </div>
                        </div>

                        <div v-else class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <CheckCircleIcon class="w-8 h-8 mx-auto mb-2 text-green-500" />
                            <p class="text-sm">You're all caught up!</p>
                            <p class="text-xs mt-1">No tasks due today</p>
                        </div>

                        <!-- Add Task -->
                        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                            <button class="flex items-center text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                <PlusIcon class="w-4 h-4 mr-2" />
                                Add task
                            </button>
                        </div>
                    </div>

                    <!-- Workspaces -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h2 class="font-semibold text-gray-900 dark:text-white">Workspaces</h2>
                            <Link
                                :href="route('workspaces.create')"
                                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center"
                            >
                                <PlusIcon class="w-4 h-4 mr-1" />
                                New
                            </Link>
                        </div>

                        <div v-if="workspaces.length === 0" class="p-8 text-center">
                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <PlusIcon class="w-6 h-6 text-gray-400" />
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                No workspaces yet
                            </p>
                            <Link
                                :href="route('workspaces.create')"
                                class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700"
                            >
                                Create workspace
                            </Link>
                        </div>

                        <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                            <Link
                                v-for="workspace in workspaces"
                                :key="workspace.id"
                                :href="route('workspaces.show', workspace.id)"
                                class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group"
                            >
                                <div
                                    class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-semibold flex-shrink-0"
                                    :style="{ backgroundColor: workspace.color || '#7c3aed' }"
                                >
                                    {{ workspace.name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ workspace.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ workspace.spaces?.length || 0 }} spaces
                                    </p>
                                </div>
                                <ArrowRightIcon class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Stats -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">This Week</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mr-3">
                                        <CheckCircleIcon class="w-4 h-4 text-green-600 dark:text-green-400" />
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Completed</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.completed }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mr-3">
                                        <ClockIcon class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">In Progress</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.inProgress }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mr-3">
                                        <FlagIcon class="w-4 h-4 text-red-600 dark:text-red-400" />
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Overdue</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ stats.overdue }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Recent Activity</h3>
                        <div v-if="recentActivity.length > 0" class="space-y-3">
                            <div
                                v-for="activity in recentActivity"
                                :key="activity.id"
                                class="flex items-start gap-3"
                            >
                                <div
                                    class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                                    :class="getActivityColor(activity.type)"
                                >
                                    <component :is="getActivityIcon(activity.type)" class="w-3 h-3" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 dark:text-white truncate">
                                        {{ activity.description }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ activity.user }} &middot; {{ activity.created_at }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center text-gray-500 dark:text-gray-400 py-4">
                            <ClockIcon class="w-6 h-6 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No recent activity</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

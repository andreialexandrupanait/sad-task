<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import {
    XMarkIcon,
    CheckCircleIcon,
    UserPlusIcon,
    CalendarIcon,
    TagIcon,
    PaperClipIcon,
    ClockIcon,
    ChatBubbleLeftIcon,
    ListBulletIcon,
    ChevronDownIcon,
    TrashIcon,
    PlusIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleSolidIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    task: Object,
    workspace: Object,
    space: Object,
    list: Object,
});

const emit = defineEmits(['close', 'updated']);

const form = useForm({
    title: props.task.title,
    description: props.task.description || '',
    status_id: props.task.status_id,
    priority: props.task.priority,
    due_date: props.task.due_date ? props.task.due_date.split('T')[0] : '',
    time_estimate: props.task.time_estimate || '',
});

const isEditing = ref(false);
const activeTab = ref('details');

// Subtasks
const newSubtaskTitle = ref('');
const showAddSubtask = ref(false);
const subtaskInput = ref(null);

// Checklists
const newChecklistName = ref('');
const showAddChecklist = ref(false);
const checklistInput = ref(null);
const newChecklistItems = ref({});
const showAddItemInput = ref({});

// Comments
const newComment = ref('');
const isSubmittingComment = ref(false);

// Attachments
const fileInput = ref(null);
const isUploading = ref(false);

const priorityOptions = [
    { value: 1, label: 'Urgent', color: 'text-red-600' },
    { value: 2, label: 'High', color: 'text-orange-600' },
    { value: 3, label: 'Normal', color: 'text-blue-600' },
    { value: 4, label: 'Low', color: 'text-gray-600' },
];

const tabs = [
    { id: 'details', label: 'Details', icon: ListBulletIcon },
    { id: 'comments', label: 'Comments', icon: ChatBubbleLeftIcon },
    { id: 'activity', label: 'Activity', icon: ClockIcon },
];

const saveChanges = () => {
    form.put(
        route('tasks.update', [props.workspace.id, props.space.id, props.list.id, props.task.id]),
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditing.value = false;
                emit('updated');
            },
        }
    );
};

const toggleComplete = () => {
    const newStatus = props.task.completed_at
        ? props.list.statuses.find(s => s.type === 'open' || s.is_default)
        : props.list.statuses.find(s => s.is_closed);

    if (newStatus) {
        router.put(
            route('tasks.update', [props.workspace.id, props.space.id, props.list.id, props.task.id]),
            {
                status_id: newStatus.id,
                completed_at: props.task.completed_at ? null : new Date().toISOString(),
            },
            { preserveScroll: true, onSuccess: () => emit('updated') }
        );
    }
};

// ===== SUBTASKS =====
const addSubtask = () => {
    if (!newSubtaskTitle.value.trim()) return;

    router.post(
        route('tasks.store', [props.workspace.id, props.space.id, props.list.id]),
        {
            title: newSubtaskTitle.value.trim(),
            parent_id: props.task.id,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                newSubtaskTitle.value = '';
                showAddSubtask.value = false;
                emit('updated');
            },
        }
    );
};

const toggleSubtask = (subtask) => {
    const closedStatus = props.list.statuses.find(s => s.is_closed);
    const openStatus = props.list.statuses.find(s => s.type === 'open' || s.is_default);

    const updates = subtask.completed_at
        ? { status_id: openStatus?.id, completed_at: null }
        : { status_id: closedStatus?.id, completed_at: new Date().toISOString() };

    router.put(
        route('tasks.update', [props.workspace.id, props.space.id, props.list.id, subtask.id]),
        updates,
        { preserveScroll: true, onSuccess: () => emit('updated') }
    );
};

const deleteSubtask = (subtask) => {
    if (!confirm('Delete this subtask?')) return;

    router.delete(
        route('tasks.destroy', [props.workspace.id, props.space.id, props.list.id, subtask.id]),
        { preserveScroll: true, onSuccess: () => emit('updated') }
    );
};

const focusSubtaskInput = () => {
    showAddSubtask.value = true;
    nextTick(() => subtaskInput.value?.focus());
};

// ===== CHECKLISTS =====
const createChecklist = () => {
    if (!newChecklistName.value.trim()) return;

    router.post(
        route('checklists.store', [props.workspace.id, props.space.id, props.list.id, props.task.id]),
        { name: newChecklistName.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => {
                newChecklistName.value = '';
                showAddChecklist.value = false;
                emit('updated');
            },
        }
    );
};

const deleteChecklist = (checklist) => {
    if (!confirm('Delete this checklist and all its items?')) return;

    router.delete(
        route('checklists.destroy', [props.workspace.id, props.space.id, props.list.id, props.task.id, checklist.id]),
        { preserveScroll: true, onSuccess: () => emit('updated') }
    );
};

const addChecklistItem = (checklist) => {
    const content = newChecklistItems.value[checklist.id]?.trim();
    if (!content) return;

    router.post(
        route('checklists.items.store', [props.workspace.id, props.space.id, props.list.id, props.task.id, checklist.id]),
        { content },
        {
            preserveScroll: true,
            onSuccess: () => {
                newChecklistItems.value[checklist.id] = '';
                showAddItemInput.value[checklist.id] = false;
                emit('updated');
            },
        }
    );
};

const toggleChecklistItem = (checklist, item) => {
    router.put(
        route('checklists.items.update', [props.workspace.id, props.space.id, props.list.id, props.task.id, checklist.id, item.id]),
        { is_completed: !item.is_completed },
        { preserveScroll: true, onSuccess: () => emit('updated') }
    );
};

const deleteChecklistItem = (checklist, item) => {
    router.delete(
        route('checklists.items.destroy', [props.workspace.id, props.space.id, props.list.id, props.task.id, checklist.id, item.id]),
        { preserveScroll: true, onSuccess: () => emit('updated') }
    );
};

const focusChecklistInput = () => {
    showAddChecklist.value = true;
    nextTick(() => checklistInput.value?.focus());
};

const getChecklistProgress = (checklist) => {
    if (!checklist.items?.length) return 0;
    const completed = checklist.items.filter(i => i.is_completed).length;
    return Math.round((completed / checklist.items.length) * 100);
};

// ===== COMMENTS =====
const submitComment = () => {
    if (!newComment.value.trim() || isSubmittingComment.value) return;

    isSubmittingComment.value = true;

    router.post(
        route('comments.store', [props.workspace.id, props.space.id, props.list.id, props.task.id]),
        { content: newComment.value.trim() },
        {
            preserveScroll: true,
            onSuccess: () => {
                newComment.value = '';
                emit('updated');
            },
            onFinish: () => {
                isSubmittingComment.value = false;
            },
        }
    );
};

const deleteComment = (comment) => {
    if (!confirm('Delete this comment?')) return;

    router.delete(
        route('comments.destroy', [props.workspace.id, props.space.id, props.list.id, props.task.id, comment.id]),
        { preserveScroll: true, onSuccess: () => emit('updated') }
    );
};

// ===== ATTACHMENTS =====
const triggerFileUpload = () => {
    fileInput.value?.click();
};

const handleFileUpload = (event) => {
    const files = event.target.files;
    if (!files.length) return;

    isUploading.value = true;

    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }

    router.post(
        route('attachments.store', [props.workspace.id, props.space.id, props.list.id, props.task.id]),
        formData,
        {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                emit('updated');
            },
            onFinish: () => {
                isUploading.value = false;
                if (fileInput.value) {
                    fileInput.value.value = '';
                }
            },
        }
    );
};

const deleteAttachment = (attachment) => {
    if (!confirm('Delete this attachment?')) return;

    router.delete(
        route('attachments.destroy', [props.workspace.id, props.space.id, props.list.id, props.task.id, attachment.id]),
        { preserveScroll: true, onSuccess: () => emit('updated') }
    );
};

const downloadAttachment = (attachment) => {
    window.open(
        route('attachments.download', [props.workspace.id, props.space.id, props.list.id, props.task.id, attachment.id]),
        '_blank'
    );
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const handleEscape = (e) => {
    if (e.key === 'Escape') {
        emit('close');
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleEscape);
    document.body.style.overflow = 'hidden';
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop -->
        <div
            class="absolute inset-0 bg-black/50 transition-opacity"
            @click="emit('close')"
        ></div>

        <!-- Modal -->
        <div class="absolute inset-y-0 right-0 flex max-w-full pl-10">
            <div class="w-screen max-w-2xl">
                <div class="flex h-full flex-col bg-white dark:bg-gray-800 shadow-xl">
                    <!-- Header -->
                    <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-start space-x-4">
                            <button
                                @click="toggleComplete"
                                class="mt-1 flex-shrink-0"
                            >
                                <CheckCircleSolidIcon
                                    v-if="task.completed_at"
                                    class="w-6 h-6 text-green-500"
                                />
                                <CheckCircleIcon
                                    v-else
                                    class="w-6 h-6 text-gray-300 hover:text-green-500 transition-colors"
                                />
                            </button>

                            <div>
                                <input
                                    v-model="form.title"
                                    @blur="saveChanges"
                                    @keyup.enter="$event.target.blur()"
                                    class="text-xl font-semibold text-gray-900 dark:text-white bg-transparent border-0 p-0 focus:ring-0 w-full"
                                    :class="{ 'line-through text-gray-400': task.completed_at }"
                                />
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ task.identifier }} in {{ list.name }}
                                </p>
                            </div>
                        </div>

                        <button
                            @click="emit('close')"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-y-auto">
                        <div class="px-6 py-4">
                            <!-- Quick actions -->
                            <div class="flex flex-wrap gap-2 mb-6">
                                <!-- Status -->
                                <div class="relative">
                                    <select
                                        v-model="form.status_id"
                                        @change="saveChanges"
                                        class="appearance-none pl-3 pr-8 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    >
                                        <option
                                            v-for="status in list.statuses"
                                            :key="status.id"
                                            :value="status.id"
                                        >
                                            {{ status.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Priority -->
                                <select
                                    v-model="form.priority"
                                    @change="saveChanges"
                                    class="appearance-none pl-3 pr-8 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                >
                                    <option
                                        v-for="option in priorityOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>

                                <!-- Due date -->
                                <div class="flex items-center">
                                    <CalendarIcon class="w-4 h-4 text-gray-400 mr-2" />
                                    <input
                                        v-model="form.due_date"
                                        @change="saveChanges"
                                        type="date"
                                        class="text-sm border-0 p-0 bg-transparent text-gray-900 dark:text-white focus:ring-0"
                                    />
                                </div>

                                <!-- Assignees -->
                                <button class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <UserPlusIcon class="w-4 h-4 mr-1.5" />
                                    Assign
                                </button>

                                <!-- Tags -->
                                <button class="flex items-center px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <TagIcon class="w-4 h-4 mr-1.5" />
                                    Tags
                                </button>
                            </div>

                            <!-- Tabs -->
                            <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                                <nav class="flex space-x-8">
                                    <button
                                        v-for="tab in tabs"
                                        :key="tab.id"
                                        @click="activeTab = tab.id"
                                        class="flex items-center py-3 px-1 border-b-2 text-sm font-medium transition-colors"
                                        :class="activeTab === tab.id
                                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                    >
                                        <component :is="tab.icon" class="w-4 h-4 mr-2" />
                                        {{ tab.label }}
                                    </button>
                                </nav>
                            </div>

                            <!-- Tab content -->
                            <div v-if="activeTab === 'details'">
                                <!-- Description -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Description
                                    </label>
                                    <textarea
                                        v-model="form.description"
                                        @blur="saveChanges"
                                        rows="4"
                                        placeholder="Add a description..."
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    ></textarea>
                                </div>

                                <!-- Subtasks -->
                                <div class="mb-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Subtasks
                                            <span v-if="task.subtasks?.length" class="text-gray-400 ml-1">
                                                ({{ task.subtasks.filter(s => s.completed_at).length }}/{{ task.subtasks.length }})
                                            </span>
                                        </h3>
                                    </div>

                                    <div v-if="task.subtasks?.length" class="space-y-1 mb-3">
                                        <div
                                            v-for="subtask in task.subtasks"
                                            :key="subtask.id"
                                            class="flex items-center group p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        >
                                            <button
                                                @click="toggleSubtask(subtask)"
                                                class="flex-shrink-0 mr-2"
                                            >
                                                <CheckCircleSolidIcon
                                                    v-if="subtask.completed_at"
                                                    class="w-5 h-5 text-green-500 hover:text-green-600"
                                                />
                                                <CheckCircleIcon
                                                    v-else
                                                    class="w-5 h-5 text-gray-300 hover:text-green-500 transition-colors"
                                                />
                                            </button>
                                            <span
                                                class="flex-1 text-sm"
                                                :class="subtask.completed_at
                                                    ? 'text-gray-400 line-through'
                                                    : 'text-gray-900 dark:text-white'"
                                            >
                                                {{ subtask.title }}
                                            </span>
                                            <button
                                                @click="deleteSubtask(subtask)"
                                                class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-500 transition-all"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Add subtask form -->
                                    <div v-if="showAddSubtask" class="flex items-center gap-2 mb-2">
                                        <input
                                            ref="subtaskInput"
                                            v-model="newSubtaskTitle"
                                            type="text"
                                            placeholder="Subtask title..."
                                            class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                            @keyup.enter="addSubtask"
                                            @keyup.escape="showAddSubtask = false; newSubtaskTitle = ''"
                                        />
                                        <button
                                            @click="addSubtask"
                                            class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                        >
                                            Add
                                        </button>
                                        <button
                                            @click="showAddSubtask = false; newSubtaskTitle = ''"
                                            class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900"
                                        >
                                            Cancel
                                        </button>
                                    </div>

                                    <button
                                        v-if="!showAddSubtask"
                                        @click="focusSubtaskInput"
                                        class="flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                    >
                                        <PlusIcon class="w-4 h-4 mr-1" />
                                        Add subtask
                                    </button>
                                </div>

                                <!-- Checklists -->
                                <div class="mb-6">
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Checklists
                                    </h3>

                                    <div v-if="task.checklists?.length" class="space-y-4 mb-3">
                                        <div
                                            v-for="checklist in task.checklists"
                                            :key="checklist.id"
                                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-3"
                                        >
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="font-medium text-gray-900 dark:text-white">
                                                    {{ checklist.name }}
                                                </h4>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-gray-500">
                                                        {{ getChecklistProgress(checklist) }}%
                                                    </span>
                                                    <button
                                                        @click="deleteChecklist(checklist)"
                                                        class="p-1 text-gray-400 hover:text-red-500"
                                                    >
                                                        <TrashIcon class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Progress bar -->
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mb-3">
                                                <div
                                                    class="bg-green-500 h-1.5 rounded-full transition-all"
                                                    :style="{ width: getChecklistProgress(checklist) + '%' }"
                                                ></div>
                                            </div>

                                            <div class="space-y-1">
                                                <div
                                                    v-for="item in checklist.items"
                                                    :key="item.id"
                                                    class="flex items-center py-1 group"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        :checked="item.is_completed"
                                                        @change="toggleChecklistItem(checklist, item)"
                                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mr-2"
                                                    />
                                                    <span
                                                        class="flex-1 text-sm"
                                                        :class="item.is_completed
                                                            ? 'text-gray-400 line-through'
                                                            : 'text-gray-700 dark:text-gray-300'"
                                                    >
                                                        {{ item.content }}
                                                    </span>
                                                    <button
                                                        @click="deleteChecklistItem(checklist, item)"
                                                        class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-500"
                                                    >
                                                        <TrashIcon class="w-3 h-3" />
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Add item form -->
                                            <div v-if="showAddItemInput[checklist.id]" class="flex items-center gap-2 mt-2">
                                                <input
                                                    v-model="newChecklistItems[checklist.id]"
                                                    type="text"
                                                    placeholder="Add an item..."
                                                    class="flex-1 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-indigo-500"
                                                    @keyup.enter="addChecklistItem(checklist)"
                                                    @keyup.escape="showAddItemInput[checklist.id] = false"
                                                />
                                                <button
                                                    @click="addChecklistItem(checklist)"
                                                    class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700"
                                                >
                                                    Add
                                                </button>
                                            </div>

                                            <button
                                                v-else
                                                @click="showAddItemInput[checklist.id] = true"
                                                class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800"
                                            >
                                                + Add item
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Add checklist form -->
                                    <div v-if="showAddChecklist" class="flex items-center gap-2 mb-2">
                                        <input
                                            ref="checklistInput"
                                            v-model="newChecklistName"
                                            type="text"
                                            placeholder="Checklist name..."
                                            class="flex-1 px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                            @keyup.enter="createChecklist"
                                            @keyup.escape="showAddChecklist = false; newChecklistName = ''"
                                        />
                                        <button
                                            @click="createChecklist"
                                            class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                        >
                                            Create
                                        </button>
                                        <button
                                            @click="showAddChecklist = false; newChecklistName = ''"
                                            class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            Cancel
                                        </button>
                                    </div>

                                    <button
                                        v-if="!showAddChecklist"
                                        @click="focusChecklistInput"
                                        class="flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                    >
                                        <PlusIcon class="w-4 h-4 mr-1" />
                                        Add checklist
                                    </button>
                                </div>

                                <!-- Attachments -->
                                <div class="mb-6">
                                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Attachments
                                    </h3>

                                    <div v-if="task.attachments?.length" class="space-y-2 mb-3">
                                        <div
                                            v-for="attachment in task.attachments"
                                            :key="attachment.id"
                                            class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 group"
                                        >
                                            <button
                                                @click="downloadAttachment(attachment)"
                                                class="flex items-center flex-1 min-w-0"
                                            >
                                                <PaperClipIcon class="w-5 h-5 text-gray-400 mr-2 flex-shrink-0" />
                                                <span class="text-sm text-gray-900 dark:text-white truncate">
                                                    {{ attachment.original_name }}
                                                </span>
                                                <span class="ml-2 text-xs text-gray-500 flex-shrink-0">
                                                    {{ formatFileSize(attachment.size) }}
                                                </span>
                                            </button>
                                            <button
                                                @click="deleteAttachment(attachment)"
                                                class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-500 ml-2"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <input
                                        ref="fileInput"
                                        type="file"
                                        multiple
                                        class="hidden"
                                        @change="handleFileUpload"
                                    />

                                    <button
                                        @click="triggerFileUpload"
                                        :disabled="isUploading"
                                        class="flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 disabled:opacity-50"
                                    >
                                        <PlusIcon class="w-4 h-4 mr-1" />
                                        {{ isUploading ? 'Uploading...' : 'Add attachment' }}
                                    </button>
                                </div>
                            </div>

                            <div v-else-if="activeTab === 'comments'" class="py-4">
                                <div v-if="task.comments?.length" class="space-y-4">
                                    <div
                                        v-for="comment in task.comments"
                                        :key="comment.id"
                                        class="flex space-x-3 group"
                                    >
                                        <img
                                            :src="comment.user?.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(comment.user?.name || 'U')}&color=7F9CF5&background=EBF4FF`"
                                            class="w-8 h-8 rounded-full flex-shrink-0"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-2">
                                                    <span class="font-medium text-gray-900 dark:text-white text-sm">
                                                        {{ comment.user?.name }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ new Date(comment.created_at).toLocaleDateString() }}
                                                    </span>
                                                </div>
                                                <button
                                                    @click="deleteComment(comment)"
                                                    class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-500"
                                                >
                                                    <TrashIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">
                                                {{ comment.content }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-500 py-8">
                                    No comments yet. Be the first to comment!
                                </div>

                                <!-- Add comment -->
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <textarea
                                        v-model="newComment"
                                        rows="3"
                                        placeholder="Write a comment..."
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        @keyup.ctrl.enter="submitComment"
                                        @keyup.meta.enter="submitComment"
                                    ></textarea>
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-xs text-gray-400">
                                            Press Ctrl+Enter to send
                                        </span>
                                        <button
                                            @click="submitComment"
                                            :disabled="!newComment.trim() || isSubmittingComment"
                                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {{ isSubmittingComment ? 'Sending...' : 'Comment' }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="activeTab === 'activity'" class="py-4">
                                <div v-if="task.activities?.length" class="space-y-4">
                                    <div
                                        v-for="activity in task.activities"
                                        :key="activity.id"
                                        class="flex items-start space-x-3 text-sm"
                                    >
                                        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                            <ClockIcon class="w-4 h-4 text-gray-400" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-gray-900 dark:text-white">
                                                <span class="font-medium">{{ activity.user?.name }}</span>
                                                {{ activity.description || activity.type }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ new Date(activity.created_at).toLocaleString() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center text-gray-500 py-8">
                                    No activity recorded yet
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

import { ref, onMounted, onUnmounted } from 'vue';

/**
 * Composable for handling keyboard shortcuts in task views
 */
export function useKeyboardShortcuts(options = {}) {
    const {
        tasks = ref([]),
        selectedIndex = ref(-1),
        onOpenTask = () => {},
        onToggleComplete = () => {},
        onChangeStatus = () => {},
        onChangePriority = () => {},
        onDelete = () => {},
        onNewTask = () => {},
        enabled = ref(true),
    } = options;

    const isInputFocused = () => {
        const activeElement = document.activeElement;
        const tagName = activeElement?.tagName?.toLowerCase();
        return tagName === 'input' || tagName === 'textarea' || activeElement?.isContentEditable;
    };

    const handleKeyDown = (e) => {
        // Don't handle shortcuts when typing in inputs
        if (isInputFocused() || !enabled.value) return;

        const taskList = tasks.value;
        const currentIndex = selectedIndex.value;

        switch (e.key.toLowerCase()) {
            // Navigation
            case 'j':
            case 'arrowdown':
                e.preventDefault();
                if (currentIndex < taskList.length - 1) {
                    selectedIndex.value = currentIndex + 1;
                } else if (currentIndex === -1 && taskList.length > 0) {
                    selectedIndex.value = 0;
                }
                scrollToSelected();
                break;

            case 'k':
            case 'arrowup':
                e.preventDefault();
                if (currentIndex > 0) {
                    selectedIndex.value = currentIndex - 1;
                } else if (currentIndex === -1 && taskList.length > 0) {
                    selectedIndex.value = taskList.length - 1;
                }
                scrollToSelected();
                break;

            case 'g':
                // gg - go to first task
                if (e.repeat) return;
                waitForSecondKey('g', () => {
                    selectedIndex.value = 0;
                    scrollToSelected();
                });
                break;

            case 'enter':
                e.preventDefault();
                if (currentIndex >= 0 && currentIndex < taskList.length) {
                    onOpenTask(taskList[currentIndex]);
                }
                break;

            case 'escape':
                e.preventDefault();
                selectedIndex.value = -1;
                break;

            // Actions on selected task
            case 'x':
            case 'c':
                // Toggle complete
                e.preventDefault();
                if (currentIndex >= 0 && currentIndex < taskList.length) {
                    onToggleComplete(taskList[currentIndex]);
                }
                break;

            case 's':
                // Change status
                if (!e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    if (currentIndex >= 0 && currentIndex < taskList.length) {
                        onChangeStatus(taskList[currentIndex]);
                    }
                }
                break;

            case 'p':
                // Change priority
                if (!e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    if (currentIndex >= 0 && currentIndex < taskList.length) {
                        onChangePriority(taskList[currentIndex]);
                    }
                }
                break;

            case 'delete':
            case 'backspace':
                // Delete task (with confirmation)
                if (e.shiftKey) {
                    e.preventDefault();
                    if (currentIndex >= 0 && currentIndex < taskList.length) {
                        onDelete(taskList[currentIndex]);
                    }
                }
                break;

            case 'n':
                // New task
                if (!e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    onNewTask();
                }
                break;

            case '?':
                // Show help
                e.preventDefault();
                showShortcutsHelp.value = true;
                break;
        }
    };

    // Helper for two-key sequences (like gg)
    let pendingKey = null;
    let pendingTimeout = null;

    const waitForSecondKey = (key, callback) => {
        if (pendingKey === key) {
            clearTimeout(pendingTimeout);
            pendingKey = null;
            callback();
        } else {
            pendingKey = key;
            pendingTimeout = setTimeout(() => {
                pendingKey = null;
            }, 500);
        }
    };

    const scrollToSelected = () => {
        setTimeout(() => {
            const selected = document.querySelector('[data-selected="true"]');
            if (selected) {
                selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        }, 10);
    };

    const showShortcutsHelp = ref(false);

    onMounted(() => {
        document.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
        document.removeEventListener('keydown', handleKeyDown);
    });

    return {
        selectedIndex,
        showShortcutsHelp,
        selectTask: (index) => {
            selectedIndex.value = index;
        },
        clearSelection: () => {
            selectedIndex.value = -1;
        },
    };
}

/**
 * Keyboard shortcuts reference
 */
export const KEYBOARD_SHORTCUTS = [
    { key: 'j / ↓', description: 'Move selection down' },
    { key: 'k / ↑', description: 'Move selection up' },
    { key: 'Enter', description: 'Open selected task' },
    { key: 'Escape', description: 'Clear selection / Close modal' },
    { key: 'x / c', description: 'Toggle task complete' },
    { key: 's', description: 'Change status' },
    { key: 'p', description: 'Change priority' },
    { key: 'n', description: 'New task' },
    { key: 'Shift+Delete', description: 'Delete task' },
    { key: '⌘+K', description: 'Open command palette' },
    { key: '?', description: 'Show keyboard shortcuts' },
];

# SAD-Tasks: Complete Project Blueprint

## Executive Summary

**Project Name:** SAD-Tasks  
**Type:** Task Management Application (ClickUp-inspired)  
**Relationship:** Companion app to SAD-ERP  
**Target Launch:** MVP in 8-10 weeks  

---

## 1. Vision & Goals

### Primary Goal
Build a task management system with ClickUp-level UX but focused on essential features (20% of features covering 80% of use cases).

### Success Metrics
- Task creation to completion in < 3 clicks
- Page load time < 1 second
- Real-time sync latency < 500ms
- User adoption from existing ERP users > 80%

---

## 2. Feature Roadmap

### Phase 1: MVP (Weeks 1-8)

| Feature | Description | Priority |
|---------|-------------|----------|
| **Workspaces** | Top-level containers, linked to ERP clients | P0 |
| **Projects** | Containers for task organization | P0 |
| **Task Lists** | Groupings within projects | P0 |
| **Tasks** | Core entity with title, description, status, priority, assignee, due date | P0 |
| **List View** | Table-style task view with sorting/grouping | P0 |
| **Board View** | Kanban-style drag-and-drop | P0 |
| **Calendar View** | Tasks on calendar by due date | P0 |
| **Task Detail** | Slide-over panel with full task info | P0 |
| **Quick Add** | Cmd+K or inline task creation | P0 |
| **Statuses** | Customizable per project | P0 |
| **Tags** | Color-coded labels | P1 |
| **Comments** | Discussion on tasks | P1 |
| **Attachments** | File uploads | P1 |
| **Search** | Full-text search across tasks | P1 |
| **SSO with ERP** | Shared authentication | P0 |

### Phase 2: Power Features (Weeks 9-14)

| Feature | Description | Priority |
|---------|-------------|----------|
| **Subtasks** | One level of task nesting | P1 |
| **Checklists** | Todo items within tasks | P1 |
| **Time Tracking** | Log time against tasks | P1 |
| **Filters** | Advanced filtering with save option | P1 |
| **Bulk Actions** | Multi-select and batch operations | P1 |
| **Activity Log** | Full audit trail | P1 |
| **Notifications** | Email + in-app + Slack | P1 |
| **Keyboard Shortcuts** | Full keyboard navigation | P1 |
| **Dark Mode** | Theme support | P2 |

### Phase 3: Advanced (Weeks 15-24)

| Feature | Description | Priority |
|---------|-------------|----------|
| **Custom Fields** | User-defined task properties | P2 |
| **Dependencies** | Task relationships (blocks/blocked by) | P2 |
| **Recurring Tasks** | Scheduled repetition | P2 |
| **Gantt View** | Timeline visualization | P2 |
| **Automations** | Rule-based actions | P2 |
| **AI Agents** | Smart task creation, decomposition, summaries | P2 |
| **API** | External integrations | P2 |
| **Mobile App** | React Native or PWA | P3 |

---

## 3. User Experience Specifications

### 3.1 Navigation Structure

```
┌─────────────────────────────────────────────────────────────────┐
│  ┌──────────┐                                                   │
│  │  LOGO    │  Search (Cmd+K)                    User Avatar    │
│  └──────────┘                                                   │
├─────────────────┬───────────────────────────────────────────────┤
│                 │                                               │
│  SIDEBAR        │              MAIN CONTENT                     │
│                 │                                               │
│  ○ Home         │  ┌─────────────────────────────────────────┐ │
│                 │  │  Project Header                         │ │
│  WORKSPACES     │  │  [List] [Board] [Calendar] [+ View]     │ │
│  ▼ Workspace 1  │  ├─────────────────────────────────────────┤ │
│    ▶ Project A  │  │                                         │ │
│    ▶ Project B  │  │  View Content                           │ │
│  ▶ Workspace 2  │  │  (List / Board / Calendar)              │ │
│                 │  │                                         │ │
│  FAVORITES      │  │                                         │ │
│  ★ Project X    │  │                                         │ │
│                 │  │                                         │ │
│  FILTERS        │  └─────────────────────────────────────────┘ │
│  ◇ My Tasks     │                                               │
│  ◇ Due Today    │                                               │
│                 │                                               │
└─────────────────┴───────────────────────────────────────────────┘
```

### 3.2 Core UI Components

| Component | ClickUp Reference | Key Features |
|-----------|-------------------|--------------|
| **Sidebar** | Collapsible, tree navigation | Workspaces → Projects hierarchy, favorites, saved filters |
| **Command Palette** | Cmd+K everywhere | Quick actions, search, navigation |
| **Task Card** | Board view card | Priority indicator, assignee, due date, tags, progress |
| **Task Row** | List view row | Inline editing, expandable, drag handle |
| **Task Detail** | Right slide-over | Full task editing without leaving context |
| **Status Dropdown** | Colored pill selector | Custom statuses per project |
| **Priority Selector** | Flag icons with colors | Urgent/High/Normal/Low |
| **Date Picker** | Smart date input | Natural language ("tomorrow", "next week") |
| **User Picker** | Avatar dropdown | Team member selection |
| **Tag Picker** | Multi-select with colors | Create new inline |

### 3.3 Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Cmd/Ctrl + K` | Open command palette |
| `Cmd/Ctrl + N` | New task |
| `Cmd/Ctrl + Enter` | Save and close |
| `Escape` | Close modal/cancel |
| `J / K` | Navigate up/down in list |
| `Enter` | Open selected task |
| `E` | Edit task |
| `Space` | Toggle task selection |
| `S` | Change status |
| `P` | Change priority |
| `A` | Assign |
| `D` | Set due date |
| `T` | Add tag |
| `C` | Add comment |
| `Delete` | Delete task (with confirmation) |

### 3.4 Views Specification

#### List View
- Grouped by: Status, Priority, Assignee, Due Date, or None
- Sortable columns: Title, Status, Priority, Assignee, Due Date, Created
- Inline editing for quick updates
- Expandable rows for subtasks
- Drag-and-drop reordering

#### Board View (Kanban)
- Columns = Statuses
- Drag cards between columns
- WIP limits (optional)
- Swimlanes by Assignee/Priority (optional)
- Quick add at bottom of each column

#### Calendar View
- Month/Week/Day views
- Tasks positioned by due date
- Drag to reschedule
- Color-coded by status or priority

---

## 4. Technical Architecture

### 4.1 Technology Stack

| Layer | Technology | Justification |
|-------|------------|---------------|
| **Backend Framework** | Laravel 12 | Same as ERP, team expertise |
| **Frontend Framework** | Vue 3 + Inertia.js | Rich UI without API complexity |
| **CSS Framework** | Tailwind CSS 3.4 | Consistent with ERP |
| **Real-time** | Laravel Reverb | WebSocket for live updates |
| **Database** | MySQL 8.0 | Shared infrastructure |
| **Cache** | Redis | Sessions, cache, queues |
| **Search** | Meilisearch | Fast full-text search |
| **File Storage** | S3-compatible | Attachments |
| **AI Provider** | Anthropic Claude API | Agent features |

### 4.2 System Architecture

```
                                    ┌─────────────────┐
                                    │   CDN (Assets)  │
                                    └────────┬────────┘
                                             │
┌─────────────────────────────────────────────────────────────────┐
│                         LOAD BALANCER                           │
└───────────────────────────────┬─────────────────────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        │                       │                       │
        ▼                       ▼                       ▼
┌───────────────┐       ┌───────────────┐       ┌───────────────┐
│   App Server  │       │   App Server  │       │  Reverb WS    │
│   (Laravel)   │       │   (Laravel)   │       │   Server      │
└───────┬───────┘       └───────┬───────┘       └───────┬───────┘
        │                       │                       │
        └───────────────────────┼───────────────────────┘
                                │
        ┌───────────────────────┼───────────────────────┐
        │                       │                       │
        ▼                       ▼                       ▼
┌───────────────┐       ┌───────────────┐       ┌───────────────┐
│     MySQL     │       │     Redis     │       │  Meilisearch  │
│   (Primary)   │       │ Cache/Queue   │       │   (Search)    │
└───────────────┘       └───────────────┘       └───────────────┘
                                │
                                ▼
                        ┌───────────────┐
                        │    Horizon    │
                        │ (Queue Worker)│
                        └───────────────┘
```

### 4.3 Integration with SAD-ERP

```
┌─────────────────────────────────────────────────────────────────┐
│                        SHARED LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│  • Authentication (SSO via Sanctum tokens)                      │
│  • User database (synced or shared)                             │
│  • Organization/tenant context                                  │
│  • Redis instance                                               │
└─────────────────────────────────────────────────────────────────┘
                    │                           │
                    ▼                           ▼
        ┌───────────────────┐       ┌───────────────────┐
        │     SAD-ERP       │       │    SAD-Tasks      │
        │                   │       │                   │
        │  • Clients ───────────────▶ Workspaces       │
        │  • Users ─────────────────▶ Users            │
        │  • Subscriptions  │       │  • Projects       │
        │  • Invoices       │       │  • Tasks          │
        │  • Domains        │       │  • Comments       │
        │                   │       │                   │
        │  intern.simplead  │       │  tasks.simplead   │
        └───────────────────┘       └───────────────────┘
```

**Data Sync Strategy:**
1. **Users**: Shared database table or webhook sync
2. **Clients → Workspaces**: Automatic workspace creation when ERP client is created
3. **Cross-linking**: Tasks can reference ERP entities (subscriptions, invoices)

---

## 5. Database Design

### 5.1 Core Entities

```
WORKSPACES
├── id, name, slug, description
├── erp_client_id (FK to ERP)
├── organization_id
├── owner_id
├── settings (JSON)
└── archived_at

PROJECTS
├── id, workspace_id
├── name, slug, description
├── color, icon, cover_image
├── default_view (list|board|calendar)
├── owner_id, position
└── status (active|on_hold|completed|archived)

STATUSES (per project)
├── id, project_id
├── name, slug, color
├── type (todo|in_progress|done|closed)
├── is_default, is_done, is_closed
└── position

TASK_LISTS
├── id, project_id
├── name, description
├── position, collapsed_by_default
└── archived_at

TASKS
├── id, task_list_id, parent_id
├── title, description (rich text)
├── status_id, priority (urgent|high|normal|low)
├── assignee_id, reporter_id
├── due_date, due_time, start_date
├── time_estimate, time_spent
├── position (float for gap-based ordering)
├── task_number (auto per project)
└── completed_at

TAGS
├── id, workspace_id
├── name, slug, color
└── (many-to-many with tasks)

COMMENTS
├── id, task_id, user_id
├── content (rich text)
├── mentions (JSON array)
├── reactions (JSON)
└── edited_at

ATTACHMENTS
├── id, task_id, user_id
├── filename, path, mime_type, size
└── thumbnail_path

CHECKLISTS
├── id, task_id, name, position
└── items (separate table with content, is_completed, assignee_id)

TIME_ENTRIES
├── id, task_id, user_id
├── started_at, ended_at, duration
├── description, is_billable
└── is_running

ACTIVITIES (audit log)
├── id, subject_type, subject_id
├── user_id, action
├── changes (JSON), metadata (JSON)
└── created_at

SAVED_FILTERS
├── id, user_id, workspace_id, project_id
├── name, filters (JSON)
├── view_type, sort_by, group_by
└── is_shared, position
```

### 5.2 Key Relationships

```
Workspace 1:N Projects
Project 1:N Statuses
Project 1:N TaskLists
TaskList 1:N Tasks
Task 1:N Tasks (subtasks, 1 level)
Task 1:N Comments
Task 1:N Attachments
Task 1:N Checklists
Task N:M Tags
Task 1:N TimeEntries
Task 1:N Activities
```

---

## 6. AI Agents Specification

### 6.1 Agent Overview

| Agent | Purpose | Trigger | Output |
|-------|---------|---------|--------|
| **Task Creator** | Parse natural language into task | User types in AI input | Structured task data |
| **Task Decomposer** | Break large task into subtasks | User clicks "Decompose" | List of subtasks |
| **Priority Advisor** | Suggest priority based on context | On task creation | Priority recommendation |
| **Standup Generator** | Summarize work for daily standup | Scheduled or on-demand | Markdown report |
| **Blocker Detector** | Find stuck/blocked tasks | Scheduled nightly | Alert notifications |
| **Smart Scheduler** | Suggest due dates based on workload | On task creation | Date recommendation |

### 6.2 Agent Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      User Interface                             │
│                                                                 │
│   ┌─────────────────────────────────────────────────────────┐  │
│   │  "Create 5 tasks for the website redesign project,      │  │
│   │   including homepage, about page, contact form..."      │  │
│   └─────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Agent Orchestrator                           │
│                                                                 │
│   • Determine which agent(s) to invoke                         │
│   • Build context from workspace/project data                  │
│   • Rate limiting and token tracking                           │
│   • Response parsing and validation                            │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Claude API                                 │
│                                                                 │
│   Model: claude-sonnet-4-20250514 (for speed)                          │
│   System prompt: Agent-specific instructions                   │
│   Context: Project details, existing tasks, team members       │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Response Handler                             │
│                                                                 │
│   • Parse structured output (JSON)                             │
│   • Validate against schema                                    │
│   • Create tasks/subtasks in database                          │
│   • Return confirmation to UI                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 6.3 Example Agent Prompts

**Task Creator Agent:**
```
You are a task creation assistant. Given a natural language description,
extract structured task data.

Context:
- Project: {{ project.name }}
- Existing statuses: {{ statuses }}
- Team members: {{ members }}
- Existing tags: {{ tags }}

User input: "{{ user_input }}"

Return JSON:
{
  "tasks": [
    {
      "title": "string",
      "description": "string (optional)",
      "priority": "urgent|high|normal|low",
      "suggested_assignee": "user_id or null",
      "suggested_due_date": "YYYY-MM-DD or null",
      "suggested_tags": ["tag_name"],
      "subtasks": ["subtask title"]
    }
  ]
}
```

**Standup Generator Agent:**
```
You are a standup report generator. Analyze the task activity and
create a concise daily standup report.

Context:
- User: {{ user.name }}
- Date range: {{ yesterday }} to {{ today }}
- Completed tasks: {{ completed_tasks }}
- Updated tasks: {{ updated_tasks }}
- New tasks: {{ new_tasks }}
- Blocked tasks: {{ blocked_tasks }}

Generate a report with:
1. What was accomplished yesterday
2. What's planned for today
3. Any blockers or concerns
```

---

## 7. UI/UX Design Guidelines

### 7.1 Design Principles

1. **Speed First**: Every action should feel instant
2. **Keyboard Friendly**: Power users should never need a mouse
3. **Context Preservation**: Never lose user's place (slide-overs, not page navigations)
4. **Progressive Disclosure**: Show essentials first, details on demand
5. **Familiar Patterns**: Match ClickUp conventions users expect

### 7.2 Color System

```css
/* Primary Brand */
--primary-50: #eef2ff;
--primary-500: #6366f1;  /* Indigo - main accent */
--primary-600: #4f46e5;
--primary-700: #4338ca;

/* Status Colors */
--status-todo: #6b7280;       /* Gray */
--status-in-progress: #3b82f6; /* Blue */
--status-review: #f59e0b;      /* Amber */
--status-done: #10b981;        /* Green */
--status-closed: #9ca3af;      /* Gray */

/* Priority Colors */
--priority-urgent: #ef4444;    /* Red */
--priority-high: #f97316;      /* Orange */
--priority-normal: #3b82f6;    /* Blue */
--priority-low: #9ca3af;       /* Gray */

/* Semantic */
--success: #10b981;
--warning: #f59e0b;
--error: #ef4444;
--info: #3b82f6;
```

### 7.3 Typography

```css
/* Font Family */
font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

/* Scale */
--text-xs: 0.75rem;    /* 12px - metadata */
--text-sm: 0.875rem;   /* 14px - body small */
--text-base: 1rem;     /* 16px - body */
--text-lg: 1.125rem;   /* 18px - titles */
--text-xl: 1.25rem;    /* 20px - headings */
--text-2xl: 1.5rem;    /* 24px - page titles */
```

### 7.4 Component Specifications

#### Task Card (Board View)
```
┌─────────────────────────────────────┐
│ ● #123                    ⋮        │  <- Priority dot, task number, menu
│                                     │
│ Design homepage hero section        │  <- Title (2 lines max)
│                                     │
│ [frontend] [urgent]                 │  <- Tags (3 max + overflow)
│                                     │
│ ━━━━━━━━━━━━━━━━━━ 2/5             │  <- Subtask progress
│                                     │
│ 📅 Tomorrow   💬 3  📎 2    (👤)   │  <- Due, comments, attachments, assignee
└─────────────────────────────────────┘

Width: 272px (fixed)
Padding: 12px
Border radius: 8px
Shadow: subtle on hover
```

#### Task Row (List View)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│ ☐  ●  #123  Design homepage hero section    [frontend]   👤 John   📅 Dec 15│
│    ↑                                                                        │
│ Checkbox  Priority                          Tags        Assignee   Due date │
└─────────────────────────────────────────────────────────────────────────────┘

Height: 40px
Hover: Light background highlight
Click: Opens task detail slide-over
```

---

## 8. Development Phases

### Phase 1: Foundation (Weeks 1-2)

- [ ] Project setup (Laravel + Inertia + Vue 3)
- [ ] Database migrations (core tables)
- [ ] Authentication (SSO with ERP)
- [ ] Basic layouts (sidebar, header)
- [ ] Workspace CRUD
- [ ] Project CRUD

### Phase 2: Core Task Features (Weeks 3-5)

- [ ] Task CRUD operations
- [ ] List View with sorting/grouping
- [ ] Board View with drag-and-drop
- [ ] Task Detail slide-over
- [ ] Status management
- [ ] Priority system
- [ ] Assignee selection
- [ ] Due dates with date picker

### Phase 3: Enhanced Features (Weeks 6-7)

- [ ] Tags system
- [ ] Comments
- [ ] Attachments
- [ ] Search (Meilisearch integration)
- [ ] Real-time updates (Reverb)
- [ ] Activity logging

### Phase 4: Polish & Launch (Week 8)

- [ ] Calendar View
- [ ] Keyboard shortcuts
- [ ] Command palette
- [ ] Performance optimization
- [ ] Bug fixes
- [ ] Documentation
- [ ] Deployment

---

## 9. Infrastructure & Deployment

### 9.1 Docker Services

```yaml
services:
  app:           # Laravel application
  nginx:         # Web server
  mysql:         # Database (separate from ERP)
  redis:         # Cache + Queue + Sessions
  reverb:        # WebSocket server
  meilisearch:   # Search engine
  horizon:       # Queue dashboard
```

### 9.2 Environment Configuration

```env
# Application
APP_NAME="SAD-Tasks"
APP_URL=https://tasks.simplead.ro
APP_ENV=production

# Database (separate from ERP)
DB_DATABASE=sad_tasks
DB_USERNAME=sad_tasks_user
DB_PASSWORD=secure_password

# ERP Integration
ERP_BASE_URL=https://intern.simplead.ro
ERP_API_TOKEN=shared_secret_token

# AI Agents
ANTHROPIC_API_KEY=sk-ant-...
AGENT_ENABLED=true
AGENT_RATE_LIMIT_PER_MINUTE=20

# Real-time
BROADCAST_DRIVER=reverb
REVERB_APP_ID=sad-tasks
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret

# Search
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://meilisearch:7700
MEILISEARCH_KEY=your-master-key
```

### 9.3 Domain Setup

| Service | Domain | Port |
|---------|--------|------|
| SAD-ERP | intern.simplead.ro | 443 |
| SAD-Tasks | tasks.simplead.ro | 443 |
| WebSocket | ws.tasks.simplead.ro | 443 |

---

## 10. Success Criteria

### MVP Launch Criteria

- [ ] Users can create workspaces and projects
- [ ] Users can create, edit, delete tasks
- [ ] List View functional with sorting
- [ ] Board View functional with drag-and-drop
- [ ] Task detail opens in slide-over
- [ ] Real-time updates work between users
- [ ] SSO with ERP works seamlessly
- [ ] Mobile responsive (basic)
- [ ] < 2 seconds initial page load
- [ ] Zero critical bugs

### Post-MVP Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Daily Active Users | 80% of ERP users | Analytics |
| Tasks Created/Day | 50+ | Database |
| Avg. Session Duration | 15+ minutes | Analytics |
| Error Rate | < 0.1% | Logging |
| User Satisfaction | 8+/10 | Survey |

---

## 11. Risks & Mitigations

| Risk | Impact | Likelihood | Mitigation |
|------|--------|------------|------------|
| Scope creep | High | High | Strict phase gates, MVP focus |
| Real-time complexity | Medium | Medium | Use proven Laravel Reverb |
| AI agent costs | Medium | Low | Rate limiting, caching |
| ERP integration issues | High | Low | Early integration testing |
| Performance at scale | Medium | Medium | Load testing before launch |

---

## 12. Next Steps

1. **Review this document** with stakeholders
2. **Finalize tech stack** decisions
3. **Set up development environment** (Docker, repos)
4. **Create detailed sprint plan** for Phase 1
5. **Begin development** with authentication and layouts

---

*Document Version: 1.0*  
*Last Updated: December 2024*  
*Author: Claude (AI Assistant)*

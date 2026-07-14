<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Analytics & Intelligence Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Host App Vite Assets & Livewire Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <!-- Apache ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

    <!-- Marked for AI Chat Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Premium Glassmorphic Stylesheet -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 50% 50%, #111827 0%, #030712 100%);
            margin: 0;
            overflow-x: hidden;
        }

        /* Scrollbar styles */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.3);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Glassmorphism */
        .glass-panel {
            background: rgba(17, 24, 39, 0.5);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }

        .glass-panel-hover:hover {
            border-color: rgba(99, 102, 241, 0.3);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.08);
            transform: translateY(-2px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glow-indigo {
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.15);
        }

        .glow-emerald {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
        }

        /* Input styling */
        .custom-input {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
            border-radius: 8px;
            padding: 8px 12px;
            outline: none;
            transition: all 0.2s;
        }

        .custom-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        .custom-select {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
            border-radius: 8px;
            padding: 8px 12px;
            outline: none;
            cursor: pointer;
        }

        .custom-select option {
            background: #0f172a;
            color: #f1f5f9;
        }

        /* Grid layouts */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 1.5rem;
        }

        /* Modals animations */
        .modal-overlay {
            backdrop-filter: blur(8px);
            background: rgba(3, 7, 18, 0.6);
        }

        .tab-btn {
            position: relative;
            transition: all 0.3s;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #6366f1;
            box-shadow: 0 0 10px #6366f1;
        }

        /* Utility helper classes (inline flex/grid/spans for layout compliance) */
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 0.5rem; }
        .gap-4 { gap: 1rem; }
        .w-full { width: 100%; }
        .h-full { height: 100%; }
        .p-4 { padding: 1rem; }
        .p-6 { padding: 1.5rem; }
        .rounded-xl { border-radius: 0.75rem; }
        .font-semibold { font-weight: 600; }
        .text-sm { font-size: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .text-lg { font-size: 1.125rem; }
        .text-xl { font-size: 1.25rem; }
        .text-2xl { font-size: 1.5rem; }
        .text-3xl { font-size: 1.875rem; }
        .text-slate-400 { color: #94a3b8; }
        .text-indigo-400 { color: #818cf8; }
        .text-indigo-500 { color: #6366f1; }
        .bg-indigo-600 { background-color: #4f46e5; }
        .bg-indigo-600:hover { background-color: #4338ca; }
        .bg-slate-900 { background-color: #0f172a; }
        .border-t { border-top: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="h-full flex flex-col overflow-hidden bg-slate-950 font-sans text-slate-200 antialiased" x-data="dashboardApp()">

    <livewire:layout.navigation />

    <div class="flex-1 flex overflow-hidden">
        <!-- Sidebar -->
    <aside class="w-64 glass-panel border-r border-slate-900 flex flex-col justify-between shrink-0">
        <div>
            <!-- Sidebar Header -->
            <div class="p-6 flex items-center gap-3 border-b border-slate-900">
                <div class="h-10 w-10 bg-indigo-600 rounded-xl flex items-center justify-center glow-indigo">
                    <i class="fa-solid fa-chart-line text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="font-bold text-lg leading-tight tracking-wide">Scorpi</h2>
                    <span class="text-xs text-indigo-400 font-semibold tracking-wider uppercase">Analytics Engine</span>
                </div>
            </div>

            <!-- Dashboard Navigation -->
            <div class="p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between px-2 mb-2">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Dashboards</span>
                    <button @click="openCreateDashboardModal()" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                        <i class="fa-solid fa-plus"></i> New
                    </button>
                </div>

                <!-- Dashboard List -->
                <nav class="flex flex-col gap-1 overflow-y-auto max-h-[350px]">
                    <template x-for="db in dashboards" :key="db.id">
                        <button 
                            @click="selectDashboard(db)" 
                            class="flex items-center justify-between p-3 rounded-lg text-sm text-left transition-all"
                            :class="activeDashboard && activeDashboard.id === db.id ? 'bg-indigo-600/20 text-white font-medium border border-indigo-500/20' : 'text-slate-400 hover:bg-slate-900 hover:text-slate-200'"
                        >
                            <div class="flex items-center gap-2 truncate">
                                <i class="fa-solid fa-gauge-high" :class="activeDashboard && activeDashboard.id === db.id ? 'text-indigo-400' : 'text-slate-500'"></i>
                                <span class="truncate" x-text="db.title"></span>
                            </div>
                            <span class="text-xs bg-slate-900 text-slate-400 px-2 py-0.5 rounded-full" x-text="db.tabs_count || 0"></span>
                        </button>
                    </template>
                </nav>
            </div>
        </div>

        <!-- Footer profile / quick settings -->
        <div class="p-4 border-t border-slate-900 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-full bg-slate-800 flex items-center justify-center font-bold text-xs text-slate-300 border border-slate-700">
                    JD
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-semibold">Developer Session</span>
                    <span class="text-[10px] text-slate-500">Antigravity Mode</span>
                </div>
            </div>
            <button @click="alert('Settings configured via config/dashboard.php')" class="text-slate-500 hover:text-slate-300">
                <i class="fa-solid fa-sliders"></i>
            </button>
        </div>
    </aside>

    <!-- Main Workspace -->
    <main class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Header Nav -->
        <header class="h-16 border-b border-slate-900 glass-panel flex items-center justify-between px-8 z-10">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold text-slate-100" x-text="activeDashboard ? activeDashboard.title : 'Loading...' "></h1>
                <span class="text-xs bg-indigo-900/30 text-indigo-400 px-2.5 py-1 rounded-full border border-indigo-500/10" x-text="activeDashboard ? activeDashboard.description : '' "></span>
            </div>

            <!-- Actions Bar -->
            <div class="flex items-center gap-3" x-show="activeDashboard">
                <!-- Date Filter Preset -->
                <select x-model="dateFilter.range" @change="reloadAllWidgets()" class="custom-select text-xs">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="this_week">This Week</option>
                    <option value="last_7_days">Last 7 Days</option>
                    <option value="last_30_days" selected>Last 30 Days</option>
                    <option value="this_month">This Month</option>
                    <option value="last_month">Last Month</option>
                    <option value="this_year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>

                <!-- Custom Range inputs if chosen -->
                <div class="flex items-center gap-1 text-xs" x-show="dateFilter.range === 'custom'">
                    <input type="date" x-model="dateFilter.from" @change="reloadAllWidgets()" class="custom-input py-1 text-xs">
                    <span class="text-slate-500">to</span>
                    <input type="date" x-model="dateFilter.to" @change="reloadAllWidgets()" class="custom-input py-1 text-xs">
                </div>

                <!-- Auto-Refresh Selector -->
                <div class="flex items-center gap-1.5">
                    <select x-model="refreshInterval" @change="onRefreshIntervalChange()" class="custom-select text-xs font-semibold">
                        <option value="0">Manual Refresh</option>
                        <option value="60">1 Min Refresh</option>
                        <option value="120">2 Min Refresh</option>
                        <option value="custom">Custom...</option>
                    </select>
                    
                    <!-- Custom Refresh Input -->
                    <div x-show="refreshInterval === 'custom'" class="flex items-center gap-1 text-xs" x-cloak>
                        <input 
                            type="number" 
                            x-model.number="customRefreshMinutes" 
                            @input="setupCustomRefreshTimer()" 
                            min="1" 
                            class="custom-input py-1 px-1.5 w-14 text-xs text-center border border-slate-800 rounded-md bg-slate-950 text-slate-200" 
                            placeholder="Min"
                        >
                        <span class="text-slate-400 font-medium">min</span>
                    </div>
                </div>

                <!-- AI Chat Toggle Button -->
                <button 
                    @click="isChatOpen = !isChatOpen" 
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all border"
                    :class="isChatOpen ? 'bg-indigo-600 border-indigo-500 text-white shadow-lg glow-indigo' : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white'"
                >
                    <i class="fa-solid fa-robot"></i>
                    <span>AI Chat</span>
                </button>

                <!-- Edit Switch -->
                <button 
                    @click="isEditMode = !isEditMode" 
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all"
                    :class="isEditMode ? 'bg-indigo-600 text-white shadow-lg glow-indigo' : 'bg-slate-900 text-slate-400 border border-slate-800 hover:text-white'"
                >
                    <i class="fa-solid" :class="isEditMode ? 'fa-check' : 'fa-pen-to-square'"></i>
                    <span x-text="isEditMode ? 'Save Layout' : 'Edit Dashboard'"></span>
                </button>

                <!-- Flowchart view Toggle -->
                <button 
                    @click="isFlowchartView = !isFlowchartView" 
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all border"
                    :class="isFlowchartView ? 'bg-emerald-600/20 text-emerald-400 border-emerald-500/20 glow-emerald' : 'bg-slate-900 text-slate-400 border-slate-800 hover:text-white'"
                >
                    <i class="fa-solid fa-diagram-project"></i>
                    <span x-text="isFlowchartView ? 'Grid View' : 'Flowchart'"></span>
                </button>

                <!-- Add widget button -->
                <button @click="openCreateWidgetModal()" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 glow-indigo" x-show="isEditMode">
                    <i class="fa-solid fa-plus"></i> Add Widget
                </button>
            </div>
        </header>

        <!-- Dynamic Content Body Wrapper -->
        <div class="flex-1 flex overflow-hidden relative">
            <!-- Side-panel Left Chat -->
            <div x-show="isChatOpen && chatPlacement === 'side-panel-left'" x-cloak class="w-96 border-r border-slate-900 bg-slate-950 flex flex-col h-full z-20 shrink-0">
                @include('dashboard::partials.chat-content')
            </div>

            <!-- Dynamic Content Body -->
            <div class="flex-1 overflow-y-auto p-8 relative">
            <template x-if="!activeDashboard">
                <div class="h-full flex flex-col items-center justify-center text-slate-500">
                    <i class="fa-solid fa-chart-line text-5xl mb-4 animate-pulse text-indigo-500/50"></i>
                    <p class="text-sm">Select or create a dashboard from the sidebar to begin.</p>
                </div>
            </template>

            <!-- Dashboard Tabs Bar -->
            <div class="flex items-center justify-between border-b border-slate-900 mb-6" x-show="activeDashboard && !isFlowchartView">
                <div class="flex items-center gap-6">
                    <template x-for="tab in (activeDashboard ? activeDashboard.tabs : [])" :key="tab.id">
                        <button 
                            @click="selectTab(tab)" 
                            class="tab-btn pb-3 text-sm font-semibold transition-all relative"
                            :class="activeTab && activeTab.id === tab.id ? 'text-white active' : 'text-slate-400 hover:text-slate-200'"
                        >
                            <span x-text="tab.title"></span>
                            <!-- Delete Tab option in edit mode -->
                            <i class="fa-solid fa-xmark ml-1.5 text-xs text-red-500 hover:text-red-400 cursor-pointer" x-show="isEditMode && activeDashboard && activeDashboard.tabs.length > 1" @click.stop="deleteTab(tab)"></i>
                        </button>
                    </template>

                    <!-- Add Tab button in edit mode -->
                    <button @click="openAddTabModal()" class="pb-3 text-sm text-indigo-400 hover:text-indigo-300 font-semibold" x-show="isEditMode">
                        <i class="fa-solid fa-plus"></i> Add Tab
                    </button>
                </div>
            </div>

            <!-- GRID WIDGETS LAYOUT -->
            <div class="dashboard-grid" x-show="activeDashboard && !isFlowchartView">
                <template x-for="widget in currentWidgets" :key="widget.id">
                    <div 
                        class="glass-panel rounded-xl overflow-hidden flex flex-col relative"
                        :class="getGridSpanClass(widget)"
                    >
                        <!-- Widget Header -->
                        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-900/50">
                            <div>
                                <h3 class="font-bold text-sm text-slate-200" x-text="widget.title"></h3>
                            </div>
                            
                            <!-- Widget Actions -->
                            <div class="flex items-center gap-2">
                                <button @click="reloadWidget(widget)" class="text-slate-500 hover:text-slate-300 text-xs">
                                    <i class="fa-solid fa-rotate-right"></i>
                                </button>
                                <button @click="openEditWidgetModal(widget)" class="text-slate-500 hover:text-indigo-400 text-xs" x-show="isEditMode">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button @click="deleteWidget(widget)" class="text-red-500 hover:text-red-400 text-xs" x-show="isEditMode">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Widget Body -->
                        <div class="p-6 flex-1 flex flex-col justify-center min-h-[260px]">
                            <!-- Chart widgets -->
                            <div 
                                x-init="$nextTick(() => renderEChart($el, widget))" 
                                class="w-full h-[240px]" 
                                x-show="isChartWidget(widget.widget_type)"
                            ></div>

                            <!-- Value/KPI widgets -->
                            <div class="text-center" x-show="widget.widget_type === 'number'">
                                <div class="text-4xl font-extrabold text-white tracking-tight" x-text="getWidgetPrimaryValue(widget)"></div>
                                <div class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-semibold">Total Aggregated Value</div>
                            </div>

                            <!-- List/Table widgets -->
                            <div class="overflow-y-auto max-h-[220px] w-full" x-show="widget.widget_type === 'list'">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-900 text-slate-400 font-semibold">
                                            <th class="pb-2">Label</th>
                                            <th class="pb-2 text-right">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="row in getWidgetRawRows(widget)">
                                            <tr class="border-b border-slate-900/30 last:border-0 hover:bg-slate-900/20">
                                                <td class="py-2.5 text-slate-300" x-text="row.x_axis || 'Unknown'"></td>
                                                <td class="py-2.5 text-right font-medium text-slate-100" x-text="row.aggregate_value"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="currentWidgets.length === 0">
                    <div class="col-span-12 py-16 text-center text-slate-500 border border-dashed border-slate-800 rounded-xl">
                        <i class="fa-solid fa-cube text-3xl mb-2 text-indigo-500/30"></i>
                        <p class="text-sm">No widgets added to this tab yet.</p>
                        <button @click="openCreateWidgetModal()" class="mt-4 px-4 py-2 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 border border-indigo-500/20 rounded-lg text-xs font-semibold" x-show="isEditMode">
                            Add First Widget
                        </button>
                    </div>
                </template>
            </div>

            <!-- FLOWCHART CANVAS VIEW -->
            <div class="w-full h-[600px] glass-panel rounded-xl relative overflow-hidden flex flex-col" x-show="activeDashboard && isFlowchartView" x-init="initFlowchartCanvas()">
                <div class="p-4 border-b border-slate-900/50 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="font-bold text-sm">Flowchart Pipeline & Transition Analytics</h3>
                        <span class="text-[10px] text-slate-400">Map state progressions or custom operational workflows</span>
                    </div>
                    <div class="flex gap-2">
                        <button @click="addFlowchartNode()" class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs" x-show="isEditMode">
                            + Add State Node
                        </button>
                        <button @click="addFlowchartEdge()" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs" x-show="isEditMode">
                            + Add Link Line
                        </button>
                    </div>
                </div>

                <!-- Custom SVG Graph Board -->
                <div class="flex-1 relative bg-slate-950/40 select-none overflow-auto" id="flowchart-board">
                    <!-- SVG lines rendering -->
                    <svg class="absolute inset-0 w-full h-full pointer-events-none" id="flowchart-svg">
                        <defs>
                            <marker id="arrow" viewBox="0 0 10 10" refX="25" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                                <path d="M 0 0 L 10 5 L 0 10 z" fill="#6366f1" />
                            </marker>
                        </defs>
                        <g x-html="getSvgLines()"></g>
                    </svg>

                    <!-- Interactive nodes drag and configure -->
                    <template x-for="node in flowchart.nodes" :key="node.id">
                        <div 
                            class="absolute glass-panel p-4 rounded-lg cursor-pointer border border-indigo-500/20 min-w-[150px]"
                            :style="`left: ${node.position_x}px; top: ${node.position_y}px; z-index: 10;`"
                            @mousedown="startDragNode($event, node)"
                        >
                            <div class="flex items-center justify-between border-b border-slate-900 pb-1.5 mb-1.5">
                                <span class="font-bold text-xs" x-text="node.label"></span>
                                <i class="fa-solid fa-xmark text-[10px] text-red-500 hover:text-red-400 cursor-pointer" x-show="isEditMode" @click.stop="deleteNode(node)"></i>
                            </div>
                            <div class="text-[10px] text-slate-400">
                                Model: <span class="text-indigo-400 font-semibold" x-text="node.module"></span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                Condition: <span class="text-emerald-400" x-text="node.conditions ? JSON.stringify(node.conditions) : 'None'"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- Side-panel Right Chat -->
        <div x-show="isChatOpen && chatPlacement === 'side-panel-right'" x-cloak class="w-96 border-l border-slate-900 bg-slate-950 flex flex-col h-full z-20 shrink-0">
            @include('dashboard::partials.chat-content')
        </div>

        <!-- Overlay Chat -->
        <div 
            x-show="isChatOpen && chatPlacement.startsWith('overlay')" 
            x-cloak 
            class="fixed w-96 h-[550px] border border-slate-800 rounded-xl bg-slate-950/90 backdrop-blur-md shadow-2xl flex flex-col z-50 overflow-hidden transition-all duration-300"
            :class="{
                'bottom-6 right-6': chatPlacement === 'overlay-bottom-right',
                'bottom-6 left-[17rem]': chatPlacement === 'overlay-bottom-left',
                'top-20 right-6': chatPlacement === 'overlay-top-right',
                'top-20 left-[17rem]': chatPlacement === 'overlay-top-left'
            }"
        >
            @include('dashboard::partials.chat-content')
        </div>
    </div>
</main>
    </div>

    <!-- MODAL: CREATE DASHBOARD -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay" x-show="modals.createDashboard" x-cloak>
        <div class="glass-panel w-full max-w-md rounded-2xl p-6 glow-indigo" @click.away="closeModals()">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-900">
                <h3 class="font-bold text-lg">Create New Dashboard</h3>
                <button @click="closeModals()" class="text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form @submit.prevent="submitCreateDashboard()" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-400 uppercase">Title</label>
                    <input type="text" x-model="forms.dashboard.title" required placeholder="e.g. Sales Performance" class="custom-input">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-400 uppercase">Slug Identifier</label>
                    <input type="text" x-model="forms.dashboard.slug" required placeholder="e.g. sales-perf" class="custom-input">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-400 uppercase">Description</label>
                    <textarea x-model="forms.dashboard.description" rows="2" placeholder="Summary of the KPI scope..." class="custom-input"></textarea>
                </div>
                
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" @click="closeModals()" class="px-4 py-2 bg-slate-900 text-slate-400 rounded-lg text-xs font-semibold hover:text-white border border-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold glow-indigo">Create Dashboard</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ADD TAB -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay" x-show="modals.addTab" x-cloak>
        <div class="glass-panel w-full max-w-sm rounded-2xl p-6 glow-indigo" @click.away="closeModals()">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-900">
                <h3 class="font-bold text-base">Add New Tab</h3>
                <button @click="closeModals()" class="text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <form @submit.prevent="submitAddTab()" class="flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-400 uppercase">Tab Name</label>
                    <input type="text" x-model="forms.tab.title" required placeholder="e.g. Customer Metrics" class="custom-input">
                </div>
                
                <div class="flex justify-end gap-3 mt-2">
                    <button type="button" @click="closeModals()" class="px-4 py-2 bg-slate-900 text-slate-400 rounded-lg text-xs font-semibold hover:text-white border border-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold glow-indigo">Add Tab</button>
                </div>
            </form>
        </div>
    </div>

    <!-- BACKDROP for widget side-panel -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] transition-opacity"
         x-show="modals.widget" x-cloak
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModals()"></div>

    <!-- SIDE PANEL: CREATE/EDIT WIDGET -->
    <div class="fixed inset-y-0 right-0 z-[70] w-full max-w-2xl bg-slate-950/95 backdrop-blur-md border-l border-slate-800 shadow-2xl flex flex-col h-full overflow-hidden"
         x-show="modals.widget" x-cloak
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">
         
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-slate-900 bg-slate-950/50">
            <div>
                <h3 class="font-bold text-lg text-white" x-text="forms.widget.id ? 'Edit Widget Schema' : 'Add Widget'"></h3>
                <p class="text-xs text-slate-400 mt-1">Configure layout, data target mapping, and filters for real-time visualization.</p>
            </div>
            <button @click="closeModals()" class="text-slate-400 hover:text-slate-200 p-2 rounded-lg hover:bg-slate-900 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <!-- Scrollable Form Content -->
        <form @submit.prevent="submitWidget()" class="flex-1 overflow-y-auto p-6 flex flex-col gap-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-400 uppercase">Widget Name</label>
                    <input type="text" x-model="forms.widget.title" required placeholder="e.g. Lead Status Summary" class="custom-input">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-400 uppercase">Widget Type</label>
                    <select x-model="forms.widget.widget_type" @change="fetchWidgetPreview()" required class="custom-select">
                        <option value="bar">Bar Chart</option>
                        <option value="line">Line Chart</option>
                        <option value="area">Area Chart</option>
                        <option value="pie">Pie Chart</option>
                        <option value="donut">Donut Chart</option>
                        <option value="gauge">Gauge Indicator</option>
                        <option value="funnel">Funnel Stage Chart</option>
                        <option value="number">KPI / Number Card</option>
                        <option value="list">Summary List / Table</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-400 uppercase">Grid Columns Span</label>
                    <select x-model="forms.widget.grid_position.w" class="custom-select">
                        <option :value="4">Small Card (4 cols)</option>
                        <option :value="6">Medium Card (6 cols)</option>
                        <option :value="8">Large Card (8 cols)</option>
                        <option :value="12">Full Width (12 cols)</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold text-slate-400 uppercase">Height / Order</label>
                    <input type="number" x-model="forms.widget.order" placeholder="0" class="custom-input">
                </div>
            </div>

            <!-- Database Target Mapping -->
            <div class="border-t border-slate-900 pt-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-400 mb-3">Database Target Mapping</h4>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-slate-400 uppercase">E-Commerce/CRM Module</label>
                        <select x-model="forms.widget.source.module" @change="onModuleChange($el.value)" required class="custom-select">
                            <option value="">Select registered module...</option>
                            <template x-for="mod in registeredModules" :key="mod.slug">
                                <option :value="mod.slug" x-text="mod.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-slate-400 uppercase">Y-Axis / Metric Value (e.g. amount, id)</label>
                        <select x-model="forms.widget.source.y_axis_field" @change="fetchWidgetPreview()" required class="custom-select">
                            <option value="">Select column...</option>
                            <template x-for="f in currentModuleFields" :key="f.name">
                                <option :value="f.name" x-text="`${f.name} (${f.type})`"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="flex flex-col gap-1.5 col-span-1">
                        <label class="text-xs font-semibold text-slate-400 uppercase">Y-Axis Aggregator</label>
                        <select x-model="forms.widget.source.y_axis_aggregate" @change="fetchWidgetPreview()" required class="custom-select">
                            <option value="count">COUNT</option>
                            <option value="sum">SUM</option>
                            <option value="avg">AVERAGE</option>
                            <option value="min">MINIMUM</option>
                            <option value="max">MAXIMUM</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5 col-span-2">
                        <label class="text-xs font-semibold text-slate-400 uppercase">X-Axis / Category (e.g. status, created_at)</label>
                        <select x-model="forms.widget.source.x_axis_field" @change="fetchWidgetPreview()" required class="custom-select">
                            <option value="">Select column...</option>
                            <template x-for="f in currentModuleFields" :key="f.name">
                                <option :value="f.name" x-text="`${f.name} (${f.type})`"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-slate-400 uppercase">X-Axis Mapping Type</label>
                        <select x-model="forms.widget.source.x_axis_type" @change="fetchWidgetPreview()" class="custom-select">
                            <option value="field">Direct field value</option>
                            <option value="date_group">Group chronologically (YYYY-MM)</option>
                            <option value="relation">Map relation ID to Name</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-slate-400 uppercase">Group Subseries by Field</label>
                        <select x-model="forms.widget.source.y_axis_group_by" @change="fetchWidgetPreview()" class="custom-select">
                            <option value="">None (Single Series)</option>
                            <template x-for="f in currentModuleFields" :key="f.name">
                                <option :value="f.name" x-text="f.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Custom Condition Filters for widget -->
            <div class="border-t border-slate-900 pt-4">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-400">Custom Condition Filters</h4>
                    <button type="button" @click="addWidgetConditionRow()" class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold">
                        + Add Condition
                    </button>
                </div>

                <div class="flex flex-col gap-2">
                    <template x-for="(cond, idx) in forms.widget.source.conditions" :key="idx">
                        <div class="flex items-center gap-2">
                            <select x-model="cond.field" @change="fetchWidgetPreview()" class="custom-select text-xs flex-1">
                                <option value="">Select Field...</option>
                                <template x-for="f in currentModuleFields" :key="f.name">
                                    <option :value="f.name" x-text="f.name"></option>
                                </template>
                            </select>
                            <select x-model="cond.operator" @change="fetchWidgetPreview()" class="custom-select text-xs w-24">
                                <option value="=">=</option>
                                <option value="!=">!=</option>
                                <option value=">">&gt;</option>
                                <option value="<">&lt;</option>
                                <option value="like">LIKE</option>
                                <option value="in">IN</option>
                            </select>
                            <input type="text" x-model="cond.value" @input.debounce.500ms="fetchWidgetPreview()" placeholder="Value" class="custom-input text-xs flex-1">
                            <button type="button" @click="removeWidgetConditionRow(idx); fetchWidgetPreview()" class="text-red-500 hover:text-red-400 p-1 text-xs">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Live Preview Section -->
            <div class="border-t border-slate-900 pt-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-400 mb-3 flex items-center gap-2">
                    <span>Live Preview</span>
                    <template x-if="isPreviewLoading">
                        <i class="fa-solid fa-circle-notch animate-spin text-indigo-400"></i>
                    </template>
                </h4>
                
                <div class="bg-slate-950/50 border border-slate-800 rounded-xl p-4 min-h-[220px] flex items-center justify-center relative overflow-hidden">
                    <!-- Loading Overlay -->
                    <div x-show="isPreviewLoading" class="absolute inset-0 bg-slate-950/70 z-10 flex items-center justify-center">
                        <div class="flex items-center gap-2 text-indigo-400 text-xs">
                            <i class="fa-solid fa-circle-notch animate-spin"></i>
                            <span>Fetching live preview...</span>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div x-show="previewError" class="text-red-400 text-xs text-center p-4">
                        <i class="fa-solid fa-triangle-exclamation mb-1 text-lg"></i>
                        <p x-text="previewError"></p>
                    </div>

                    <!-- No Data / Configure First -->
                    <div x-show="!forms.widget.source.module" class="text-slate-500 text-xs text-center">
                        <i class="fa-solid fa-chart-line mb-1 text-lg"></i>
                        <p>Select a module to view live preview</p>
                    </div>

                    <!-- Chart Preview -->
                    <div id="widget-preview-chart" class="w-full h-[200px]" x-show="forms.widget.source.module && isChartWidget(forms.widget.widget_type) && !previewError"></div>

                    <!-- Number Card Preview -->
                    <div x-show="forms.widget.source.module && forms.widget.widget_type === 'number' && !previewError" class="text-center">
                        <div class="text-xs text-slate-400 uppercase tracking-wider mb-1" x-text="forms.widget.title || 'KPI Value'"></div>
                        <div class="text-3xl font-extrabold text-white" x-text="previewRaw.length > 0 ? (previewRaw[0].aggregate_value || 0) : 0"></div>
                    </div>

                    <!-- List Preview -->
                    <div x-show="forms.widget.source.module && forms.widget.widget_type === 'list' && !previewError" class="w-full overflow-x-auto max-h-[180px]">
                        <table class="w-full text-left border-collapse text-[10px]">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase">
                                    <th class="py-1 px-2" x-text="forms.widget.source.x_axis_field || 'Category'"></th>
                                    <th class="py-1 px-2 text-right" x-text="`${forms.widget.source.y_axis_aggregate || 'Value'}(${forms.widget.source.y_axis_field || ''})`"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, idx) in previewRaw" :key="idx">
                                    <tr class="border-b border-slate-900/50 hover:bg-slate-900/20">
                                        <td class="py-1 px-2 text-slate-300" x-text="row.x_value"></td>
                                        <td class="py-1 px-2 text-right text-indigo-400 font-semibold" x-text="row.aggregate_value"></td>
                                    </tr>
                                </template>
                                <template x-if="previewRaw.length === 0">
                                    <tr>
                                        <td colspan="2" class="py-4 text-center text-slate-500">No records found</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Form Actions Footer -->
            <div class="flex justify-end gap-3 border-t border-slate-900 pt-4 mt-auto">
                <button type="button" @click="closeModals()" class="px-4 py-2 bg-slate-900 text-slate-400 rounded-lg text-xs font-semibold hover:text-white border border-slate-800 transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold glow-indigo transition-colors" x-text="forms.widget.id ? 'Save Changes' : 'Add Widget'"></button>
            </div>
        </form>
    </div>

    <!-- Page loading indicator -->
    <div class="fixed bottom-4 right-4 bg-indigo-950/80 border border-indigo-500/20 text-indigo-400 px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-2 glow-indigo z-50" x-show="isLoading">
        <i class="fa-solid fa-circle-notch animate-spin"></i>
        <span>Synchronizing data pipelines...</span>
    </div>

    <!-- Alpine.js Main JS Controller App -->
    <script>
        function dashboardApp() {
            return {
                dashboards: [],
                activeDashboard: null,
                activeTab: null,
                currentWidgets: [],
                registeredModules: [],
                currentModuleFields: [],
                isEditMode: false,
                isFlowchartView: false,
                isLoading: false,
                
                previewError: null,
                isPreviewLoading: false,
                previewRaw: [],
                previewChartInstance: null,
                
                // Active chart instances cache
                chartInstances: {},

                // AI Chat State
                chatPlacement: 'overlay-bottom-right',
                refreshInterval: 0,
                customRefreshMinutes: 5,
                isChatOpen: false,
                chatMessages: [],
                chatInput: '',
                isChatLoading: false,
                refreshTimer: null,

                dateFilter: {
                    range: 'last_30_days',
                    from: '',
                    to: ''
                },

                modals: {
                    createDashboard: false,
                    addTab: false,
                    widget: false
                },

                forms: {
                    dashboard: {
                        title: '',
                        slug: '',
                        description: ''
                    },
                    tab: {
                        title: ''
                    },
                    widget: {
                        id: null,
                        title: '',
                        widget_type: 'bar',
                        grid_position: { w: 6, h: 4 },
                        order: 0,
                        source: {
                            module: '',
                            x_axis_field: '',
                            x_axis_type: 'field',
                            y_axis_field: '',
                            y_axis_aggregate: 'count',
                            y_axis_group_by: '',
                            conditions: []
                        }
                    }
                },

                flowchart: {
                    nodes: [
                        { id: 1, label: 'Leads Received', module: 'leads', conditions: { status: 'new' }, position_x: 50, position_y: 100 },
                        { id: 2, label: 'Contacted', module: 'leads', conditions: { status: 'contacted' }, position_x: 250, position_y: 100 },
                        { id: 3, label: 'Qualified', module: 'leads', conditions: { status: 'qualified' }, position_x: 450, position_y: 100 },
                        { id: 4, label: 'Proposal Sent', module: 'deals', conditions: { stage: 'proposal' }, position_x: 250, position_y: 250 },
                        { id: 5, label: 'Won / Conversions', module: 'deals', conditions: { stage: 'won' }, position_x: 450, position_y: 250 }
                    ],
                    edges: [
                        { source_node_id: 1, target_node_id: 2 },
                        { source_node_id: 2, target_node_id: 3 },
                        { source_node_id: 3, target_node_id: 4 },
                        { source_node_id: 4, target_node_id: 5 }
                    ],
                    activeDragNode: null,
                    dragOffset: { x: 0, y: 0 }
                },

                async init() {
                    this.isLoading = true;
                    try {
                        await this.loadDashboards();
                        await this.loadRegisteredModules();
                        if (this.dashboards.length > 0) {
                            await this.selectDashboard(this.dashboards[0]);
                        }
                        this.setupRefreshTimer(this.refreshInterval);
                    } catch (e) {
                        console.error("Initialization failed: ", e);
                    } finally {
                        this.isLoading = false;
                    }
                },

                async loadDashboards() {
                    let res = await fetch('/api/dashboard/dashboards');
                    let json = await res.json();
                    if (json.success) {
                        this.dashboards = json.data;
                    }
                },

                async loadRegisteredModules() {
                    let res = await fetch('/api/dashboard/modules');
                    let json = await res.json();
                    if (json.success) {
                        this.registeredModules = json.data;
                    }
                },

                async onModuleChange(moduleSlug) {
                    if (!moduleSlug) {
                        this.currentModuleFields = [];
                        this.fetchWidgetPreview();
                        return;
                    }
                    let res = await fetch(`/api/dashboard/modules/${moduleSlug}/fields`);
                    let json = await res.json();
                    if (json.success) {
                        this.currentModuleFields = Object.entries(json.data.fields).map(([name, info]) => ({
                            name: name,
                            ...info
                        }));
                    }
                    this.fetchWidgetPreview();
                },

                async selectDashboard(db) {
                    this.isLoading = true;
                    try {
                        let res = await fetch(`/api/dashboard/dashboards/${db.id}`);
                        let json = await res.json();
                        if (json.success) {
                            this.activeDashboard = json.data;
                            if (this.activeDashboard.tabs && this.activeDashboard.tabs.length > 0) {
                                this.selectTab(this.activeDashboard.tabs[0]);
                            } else {
                                this.activeTab = null;
                                this.currentWidgets = [];
                            }
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                selectTab(tab) {
                    this.activeTab = tab;
                    this.currentWidgets = tab.widgets || [];
                    this.$nextTick(() => {
                        this.reloadAllWidgets();
                    });
                },

                reloadAllWidgets() {
                    this.currentWidgets.forEach(w => {
                        this.reloadWidget(w);
                    });
                },

                async reloadWidget(widget) {
                    // Build query string with date filter
                    let url = `/api/dashboard/widgets/${widget.id}/data?date_range=${this.dateFilter.range}`;
                    if (this.dateFilter.range === 'custom') {
                        url += `&date_from=${this.dateFilter.from}&date_to=${this.dateFilter.to}`;
                    }

                    try {
                        let res = await fetch(url);
                        let json = await res.json();
                        if (json.success && json.data.length > 0) {
                            let widgetData = json.data[0];
                            widget._results = widgetData.raw || [];
                            widget._echartOption = widgetData.formatted || null;

                            // Update ECharts instance if existing
                            if (this.isChartWidget(widget.widget_type) && this.chartInstances[widget.id]) {
                                if (widget._echartOption) {
                                    this.chartInstances[widget.id].setOption(widget._echartOption, true);
                                }
                            }
                        } else {
                            widget._results = [];
                        }
                    } catch (e) {
                        console.error("Widget load failed: ", e);
                    }
                },

                setupRefreshTimer(seconds) {
                    if (this.refreshTimer) {
                        clearInterval(this.refreshTimer);
                        this.refreshTimer = null;
                    }
                    if (seconds > 0) {
                        this.refreshTimer = setInterval(() => {
                            this.reloadAllWidgets();
                        }, seconds * 1000);
                    }
                },

                onRefreshIntervalChange() {
                    if (this.refreshInterval === 'custom') {
                        this.setupCustomRefreshTimer();
                    } else {
                        this.setupRefreshTimer(parseInt(this.refreshInterval));
                    }
                },

                setupCustomRefreshTimer() {
                    const mins = parseInt(this.customRefreshMinutes);
                    if (!isNaN(mins) && mins > 0) {
                        this.setupRefreshTimer(mins * 60);
                    } else {
                        this.setupRefreshTimer(0);
                    }
                },

                async sendChat() {
                    const message = this.chatInput.trim();
                    if (!message || this.isChatLoading) return;

                    this.chatMessages.push({
                        role: 'user',
                        content: message
                    });
                    this.chatInput = '';
                    this.isChatLoading = true;

                    this.scrollChatToBottom();

                    try {
                        let res = await fetch('/api/dashboard/ai/chat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ message: message })
                        });
                        let json = await res.json();
                        if (json.success) {
                            this.chatMessages.push({
                                role: 'ai',
                                content: json.response
                            });
                        } else {
                            this.chatMessages.push({
                                role: 'system',
                                content: json.message || 'An error occurred.'
                            });
                        }
                    } catch (e) {
                        this.chatMessages.push({
                            role: 'system',
                            content: 'Failed to communicate with AI agent.'
                        });
                    } finally {
                        this.isChatLoading = false;
                        this.scrollChatToBottom();
                    }
                },

                clearChat() {
                    this.chatMessages = [];
                },

                scrollChatToBottom() {
                    this.$nextTick(() => {
                        const el = this.$refs.chatMessages;
                        if (el) {
                            el.scrollTop = el.scrollHeight;
                        }
                    });
                },

                renderMarkdown(content) {
                    if (window.marked && window.marked.parse) {
                        return marked.parse(content);
                    }
                    return content
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/\n/g, '<br>');
                },

                renderEChart(el, widget) {
                    // Destroy existing if present
                    if (this.chartInstances[widget.id]) {
                        this.chartInstances[widget.id].dispose();
                    }

                    const myChart = echarts.init(el, 'dark', { backgroundColor: 'transparent' });
                    this.chartInstances[widget.id] = myChart;

                    // Initial state or dynamic set
                    if (widget._echartOption) {
                        myChart.setOption(widget._echartOption);
                    } else {
                        // fallback empty placeholder option
                        myChart.setOption({
                            title: { text: 'Loading data...', textStyle: { fontSize: 12, color: '#64748b' }, left: 'center', top: 'center' }
                        });
                    }

                    window.addEventListener('resize', () => {
                        myChart.resize();
                    });
                },

                isChartWidget(type) {
                    return ['bar', 'line', 'pie', 'donut', 'area', 'gauge', 'funnel'].includes(type.toLowerCase());
                },

                getWidgetPrimaryValue(widget) {
                    if (widget._results && widget._results.length > 0) {
                        let first = widget._results[0];
                        return first.aggregate_value || 0;
                    }
                    return 0;
                },

                getWidgetRawRows(widget) {
                    return widget._results || [];
                },

                getGridSpanClass(widget) {
                    let w = widget.grid_position?.w || 6;
                    if (w === 4) return 'col-span-12 md:col-span-4';
                    if (w === 8) return 'col-span-12 md:col-span-8';
                    if (w === 12) return 'col-span-12';
                    return 'col-span-12 md:col-span-6'; // default 6
                },

                // CRUD Modals and Actions
                openCreateDashboardModal() {
                    this.forms.dashboard = { title: '', slug: '', description: '' };
                    this.modals.createDashboard = true;
                },

                async submitCreateDashboard() {
                    this.isLoading = true;
                    try {
                        let res = await fetch('/api/dashboard/dashboards', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(this.forms.dashboard)
                        });
                        let json = await res.json();
                        if (json.success) {
                            await this.loadDashboards();
                            this.modals.createDashboard = false;
                            await this.selectDashboard(json.data);
                        } else {
                            alert(json.message);
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                openAddTabModal() {
                    this.forms.tab = { title: '' };
                    this.modals.addTab = true;
                },

                async submitAddTab() {
                    this.isLoading = true;
                    try {
                        let res = await fetch(`/api/dashboard/dashboards/${this.activeDashboard.id}/tabs`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(this.forms.tab)
                        });
                        let json = await res.json();
                        if (json.success) {
                            this.modals.addTab = false;
                            await this.selectDashboard(this.activeDashboard);
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                async deleteTab(tab) {
                    if (!confirm(`Are you sure you want to delete tab "${tab.title}"?`)) return;
                    this.isLoading = true;
                    try {
                        let res = await fetch(`/api/dashboard/tabs/${tab.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });
                        if (res.ok) {
                            await this.selectDashboard(this.activeDashboard);
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                openCreateWidgetModal() {
                    this.forms.widget = {
                        id: null,
                        title: '',
                        widget_type: 'bar',
                        grid_position: { w: 6, h: 4 },
                        order: 0,
                        source: {
                            module: '',
                            x_axis_field: '',
                            x_axis_type: 'field',
                            y_axis_field: '',
                            y_axis_aggregate: 'count',
                            y_axis_group_by: '',
                            conditions: []
                        }
                    };
                    this.currentModuleFields = [];
                    this.previewError = null;
                    this.previewRaw = [];
                    this.isPreviewLoading = false;
                    if (this.previewChartInstance) {
                        this.previewChartInstance.dispose();
                        this.previewChartInstance = null;
                    }
                    this.modals.widget = true;
                },

                async openEditWidgetModal(widget) {
                    let source = widget.data_sources && widget.data_sources.length > 0 ? widget.data_sources[0] : null;
                    
                    this.forms.widget = {
                        id: widget.id,
                        title: widget.title,
                        widget_type: widget.widget_type,
                        grid_position: widget.grid_position || { w: 6, h: 4 },
                        order: widget.order || 0,
                        source: {
                            module: source ? source.module : '',
                            x_axis_field: source ? source.x_axis_field : '',
                            x_axis_type: source ? (source.x_axis_type || 'field') : 'field',
                            y_axis_field: source ? source.y_axis_field : '',
                            y_axis_aggregate: source ? (source.y_axis_aggregate || 'count') : 'count',
                            y_axis_group_by: source ? (source.y_axis_group_by || '') : '',
                            conditions: source && source.conditions ? source.conditions.map(c => ({
                                field: c.field,
                                operator: c.operator,
                                value: c.value
                            })) : []
                        }
                    };

                    this.previewError = null;
                    this.previewRaw = [];
                    this.isPreviewLoading = false;
                    if (this.previewChartInstance) {
                        this.previewChartInstance.dispose();
                        this.previewChartInstance = null;
                    }

                    await this.onModuleChange(this.forms.widget.source.module);
                    this.modals.widget = true;
                    this.fetchWidgetPreview();
                },

                addWidgetConditionRow() {
                    this.forms.widget.source.conditions.push({
                        field: '',
                        operator: '=',
                        value: ''
                    });
                },

                removeWidgetConditionRow(idx) {
                    this.forms.widget.source.conditions.splice(idx, 1);
                },

                async submitWidget() {
                    this.isLoading = true;
                    try {
                        let method = this.forms.widget.id ? 'PUT' : 'POST';
                        let url = this.forms.widget.id 
                            ? `/api/dashboard/widgets/${this.forms.widget.id}`
                            : '/api/dashboard/widgets';

                        let payload = {
                            dashboard_tab_id: this.activeTab.id,
                            title: this.forms.widget.title,
                            widget_type: this.forms.widget.widget_type,
                            grid_position: this.forms.widget.grid_position,
                            order: this.forms.widget.order,
                            data_sources: [{
                                module: this.forms.widget.source.module,
                                x_axis_field: this.forms.widget.source.x_axis_field,
                                x_axis_type: this.forms.widget.source.x_axis_type,
                                y_axis_field: this.forms.widget.source.y_axis_field,
                                y_axis_aggregate: this.forms.widget.source.y_axis_aggregate,
                                y_axis_group_by: this.forms.widget.source.y_axis_group_by,
                                conditions: this.forms.widget.source.conditions
                            }]
                        };

                        let res = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(payload)
                        });

                        if (res.ok) {
                            this.modals.widget = false;
                            if (this.previewChartInstance) {
                                this.previewChartInstance.dispose();
                                this.previewChartInstance = null;
                            }
                            await this.selectDashboard(this.activeDashboard);
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                async deleteWidget(widget) {
                    if (!confirm(`Are you sure you want to delete widget "${widget.title}"?`)) return;
                    this.isLoading = true;
                    try {
                        let res = await fetch(`/api/dashboard/widgets/${widget.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            }
                        });
                        if (res.ok) {
                            await this.selectDashboard(this.activeDashboard);
                        }
                    } finally {
                        this.isLoading = false;
                    }
                },

                closeModals() {
                    this.modals.createDashboard = false;
                    this.modals.addTab = false;
                    this.modals.widget = false;
                    if (this.previewChartInstance) {
                        this.previewChartInstance.dispose();
                        this.previewChartInstance = null;
                    }
                },

                async fetchWidgetPreview() {
                    this.previewError = null;
                    
                    let module = this.forms.widget.source.module;
                    if (!module) {
                        this.previewRaw = [];
                        if (this.previewChartInstance) {
                            this.previewChartInstance.dispose();
                            this.previewChartInstance = null;
                        }
                        return;
                    }

                    this.isPreviewLoading = true;

                    try {
                        let payload = {
                            widget_type: this.forms.widget.widget_type,
                            module: module,
                            x_axis_field: this.forms.widget.source.x_axis_field,
                            x_axis_type: this.forms.widget.source.x_axis_type,
                            y_axis_field: this.forms.widget.source.y_axis_field,
                            y_axis_aggregate: this.forms.widget.source.y_axis_aggregate,
                            y_axis_group_by: this.forms.widget.source.y_axis_group_by,
                            conditions: this.forms.widget.source.conditions
                        };

                        let res = await fetch('/api/dashboard/widgets/preview', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify(payload)
                        });

                        let json = await res.json();
                        if (json.success) {
                            this.previewRaw = json.raw || [];
                            
                            if (this.isChartWidget(this.forms.widget.widget_type)) {
                                let option = json.formatted;
                                this.$nextTick(() => {
                                    let el = document.getElementById('widget-preview-chart');
                                    if (el) {
                                        if (this.previewChartInstance) {
                                            this.previewChartInstance.dispose();
                                        }
                                        this.previewChartInstance = echarts.init(el, 'dark', { backgroundColor: 'transparent' });
                                        if (option) {
                                            this.previewChartInstance.setOption(option);
                                        }
                                    }
                                });
                            } else {
                                if (this.previewChartInstance) {
                                    this.previewChartInstance.dispose();
                                    this.previewChartInstance = null;
                                }
                            }
                        } else {
                            this.previewError = json.message || 'Failed to fetch preview';
                        }
                    } catch (e) {
                        this.previewError = e.message || 'An error occurred during preview generation';
                        console.error("Preview generation failed:", e);
                    } finally {
                        this.isPreviewLoading = false;
                    }
                },

                // SVG Flowchart operations
                initFlowchartCanvas() {
                    // Custom interactive canvas logic
                },

                getNodeX(nodeId) {
                    let n = this.flowchart.nodes.find(n => n.id === nodeId);
                    return n ? n.position_x + 75 : 0; // offset center width
                },

                getNodeY(nodeId) {
                    let n = this.flowchart.nodes.find(n => n.id === nodeId);
                    return n ? n.position_y + 35 : 0; // offset center height
                },

                getSvgLines() {
                    let html = '';
                    if (!this.flowchart || !this.flowchart.edges) return html;
                    this.flowchart.edges.forEach(edge => {
                        let x1 = this.getNodeX(edge.source_node_id);
                        let y1 = this.getNodeY(edge.source_node_id);
                        let x2 = this.getNodeX(edge.target_node_id);
                        let y2 = this.getNodeY(edge.target_node_id);
                        if (x1 && y1 && x2 && y2) {
                            html += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#6366f1" stroke-width="2" marker-end="url(#arrow)" />`;
                        }
                    });
                    return html;
                },

                startDragNode(e, node) {
                    if (!this.isEditMode) return;
                    this.flowchart.activeDragNode = node;
                    this.flowchart.dragOffset.x = e.clientX - node.position_x;
                    this.flowchart.dragOffset.y = e.clientY - node.position_y;

                    const dragMove = (moveEvent) => {
                        if (this.flowchart.activeDragNode) {
                            this.flowchart.activeDragNode.position_x = moveEvent.clientX - this.flowchart.dragOffset.x;
                            this.flowchart.activeDragNode.position_y = moveEvent.clientY - this.flowchart.dragOffset.y;
                        }
                    };

                    const dragEnd = () => {
                        this.flowchart.activeDragNode = null;
                        document.removeEventListener('mousemove', dragMove);
                        document.removeEventListener('mouseup', dragEnd);
                    };

                    document.addEventListener('mousemove', dragMove);
                    document.addEventListener('mouseup', dragEnd);
                },

                addFlowchartNode() {
                    let id = Date.now();
                    this.flowchart.nodes.push({
                        id: id,
                        label: 'New Pipeline State',
                        module: 'leads',
                        conditions: {},
                        position_x: 100,
                        position_y: 150
                    });
                },

                addFlowchartEdge() {
                    if (this.flowchart.nodes.length < 2) return;
                    let source = this.flowchart.nodes[this.flowchart.nodes.length - 2].id;
                    let target = this.flowchart.nodes[this.flowchart.nodes.length - 1].id;
                    this.flowchart.edges.push({
                        source_node_id: source,
                        target_node_id: target
                    });
                },

                deleteNode(node) {
                    this.flowchart.nodes = this.flowchart.nodes.filter(n => n.id !== node.id);
                    this.flowchart.edges = this.flowchart.edges.filter(e => e.source_node_id !== node.id && e.target_node_id !== node.id);
                }
            }
        }
    </script>
</body>
</html>

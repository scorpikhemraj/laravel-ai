<template>
    <div class="calendar-showcase-container min-h-screen p-4 md:p-8 bg-gradient-to-tr from-zinc-950 via-zinc-900 to-zinc-950 text-white font-sans antialiased">
        <Toast />
        <ConfirmDialog />

        <!-- Header -->
        <header class="mb-8 border-b border-zinc-800/80 pb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent flex items-center gap-2.5">
                    <i class="pi pi-calendar-plus text-indigo-400 text-2xl"></i>
                    Vanilla Calendar Pro
                </h1>
                <p class="text-sm text-zinc-400 mt-1.5">
                    Full-featured calendar, date &amp; time picker with persistent event scheduling database integration.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Button label="Refresh Events" icon="pi pi-refresh" severity="secondary" text class="hover:bg-zinc-800/60" @click="fetchEvents" />
                <Button label="Schedule Event" icon="pi pi-plus" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 border-none shadow-md shadow-indigo-900/30" @click="openCreateEventDialog" />
            </div>
        </header>

        <!-- Main Workspace -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
            
            <!-- Left Side: Interactive Calendar & Active Events -->
            <div class="xl:col-span-8 space-y-6">
                <!-- Calendar Wrapper -->
                <div class="backdrop-blur-md bg-zinc-900/40 border border-zinc-800/60 shadow-2xl rounded-2xl p-6 relative overflow-hidden">
                    <div class="absolute -top-40 -right-40 w-80 h-80 bg-indigo-500/10 rounded-full blur-[100px] pointer-events-none"></div>
                    <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-500/10 rounded-full blur-[100px] pointer-events-none"></div>

                    <!-- Calendar Element Mount Point -->
                    <div class="flex justify-center">
                        <div ref="calendarEl" class="custom-calendar-theme w-full max-w-full"></div>
                    </div>
                </div>

                <!-- Selected Info & Selected Date Events -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Dates & Time Picker Details -->
                    <div class="backdrop-blur-sm bg-zinc-900/30 border border-zinc-800/40 rounded-xl p-5">
                        <h3 class="text-sm font-bold text-zinc-300 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <i class="pi pi-info-circle text-indigo-400"></i> Selection Info
                        </h3>
                        <div class="space-y-3.5 text-sm">
                            <div class="flex justify-between border-b border-zinc-800/60 pb-2">
                                <span class="text-zinc-400">Selection Mode</span>
                                <span class="font-semibold text-indigo-300 capitalize">{{ selectionDatesMode || 'None' }}</span>
                            </div>
                            <div class="flex flex-col gap-1 border-b border-zinc-800/60 pb-2">
                                <span class="text-zinc-400">Selected Date(s)</span>
                                <div class="flex flex-wrap gap-1.5 mt-1" v-if="selectedDates.length > 0">
                                    <span v-for="date in selectedDates" :key="date" class="px-2 py-0.5 bg-indigo-950/80 border border-indigo-800/60 text-indigo-300 rounded text-xs">
                                        {{ date }}
                                    </span>
                                </div>
                                <span class="text-zinc-500 italic" v-else>No date selected</span>
                            </div>
                            <div class="flex justify-between pb-1">
                                <span class="text-zinc-400">Time Picked</span>
                                <span class="font-mono text-purple-300 font-semibold" v-if="selectedTimeStr">
                                    {{ selectedTimeStr }}
                                </span>
                                <span class="text-zinc-500 italic" v-else>Time disabled or not picked</span>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Day Action Card -->
                    <div class="backdrop-blur-sm bg-zinc-900/30 border border-zinc-800/40 rounded-xl p-5 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-300 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="pi pi-sliders-h text-purple-400"></i> Context Action
                            </h3>
                            <p class="text-xs text-zinc-400 leading-relaxed mb-4">
                                Click on any date on the calendar to quickly assign an event, or schedule multiple events by defining custom colors and times.
                            </p>
                        </div>
                        <Button 
                            :label="selectedDates.length > 0 ? `Create Event for ${selectedDates[0]}` : 'Select a date to schedule'" 
                            :icon="selectedDates.length > 0 ? 'pi pi-calendar-plus' : 'pi pi-info'" 
                            :disabled="selectedDates.length === 0" 
                            class="w-full bg-zinc-800 border border-zinc-700/80 hover:bg-zinc-700/80 text-zinc-200 text-xs font-semibold py-2.5"
                            @click="openCreateEventDialog"
                        />
                    </div>
                </div>
            </div>

            <!-- Right Side: Configurator & Persistent Events List -->
            <div class="xl:col-span-4 space-y-6">
                <!-- Settings Panel -->
                <div class="backdrop-blur-md bg-zinc-900/40 border border-zinc-800/60 shadow-2xl rounded-2xl p-6">
                    <h2 class="text-xl font-bold mb-4 bg-gradient-to-r from-purple-400 to-indigo-400 bg-clip-text text-transparent flex items-center gap-2">
                        <i class="pi pi-cog"></i> Calendar Configurator
                    </h2>

                    <div class="space-y-5">
                        <!-- Calendar Type Selector -->
                        <div>
                            <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Calendar Type</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="t in typeOptions" :key="t.value" 
                                    @click="calendarType = t.value"
                                    :class="[
                                        'px-3 py-2 text-xs font-semibold rounded-lg border transition-all duration-200',
                                        calendarType === t.value 
                                            ? 'bg-indigo-600/20 border-indigo-500 text-indigo-300 shadow-md shadow-indigo-900/10'
                                            : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-400 hover:bg-zinc-800'
                                    ]"
                                >
                                    {{ t.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Date Selection Mode -->
                        <div>
                            <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Selection Dates Mode</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button v-for="mode in selectionModeOptions" :key="mode.value" 
                                    @click="selectionDatesMode = mode.value"
                                    :class="[
                                        'px-3 py-2 text-xs font-semibold rounded-lg border transition-all duration-200',
                                        selectionDatesMode === mode.value 
                                            ? 'bg-purple-600/20 border-purple-500 text-purple-300 shadow-md'
                                            : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-400 hover:bg-zinc-800'
                                    ]"
                                >
                                    {{ mode.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Time Picker Mode -->
                        <div>
                            <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Time Picker Mode</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="tMode in timeModeOptions" :key="tMode.value" 
                                    @click="selectionTimeMode = tMode.value"
                                    :class="[
                                        'px-2 py-2 text-xs font-semibold rounded-lg border transition-all duration-200',
                                        selectionTimeMode === tMode.value 
                                            ? 'bg-pink-600/20 border-pink-500 text-pink-300 shadow-md'
                                            : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-400 hover:bg-zinc-800'
                                    ]"
                                >
                                    {{ tMode.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Visual Toggles -->
                        <div class="border-t border-zinc-800/60 pt-4 space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="block text-sm font-semibold text-zinc-300">Show Week Numbers</span>
                                    <span class="text-[10px] text-zinc-500">Enable vertical week numbers panel</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="enableWeekNumbers" class="sr-only peer">
                                    <div class="w-9 h-5 bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-zinc-400 after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600 peer-checked:after:bg-white peer-checked:after:border-indigo-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="block text-sm font-semibold text-zinc-300">Show Outside Dates</span>
                                    <span class="text-[10px] text-zinc-500">Display dates from adjacent months</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="displayDatesOutside" class="sr-only peer">
                                    <div class="w-9 h-5 bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-zinc-400 after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600 peer-checked:after:bg-white peer-checked:after:border-indigo-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="block text-sm font-semibold text-zinc-300">Disable Past Dates</span>
                                    <span class="text-[10px] text-zinc-500">Prevent selection of older dates</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="disableDatesPast" class="sr-only peer">
                                    <div class="w-9 h-5 bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-zinc-400 after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600 peer-checked:after:bg-white peer-checked:after:border-indigo-600"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Theme Control -->
                        <div class="border-t border-zinc-800/60 pt-4">
                            <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Calendar Theme</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button v-for="theme in themeOptions" :key="theme.value" 
                                    @click="selectedTheme = theme.value"
                                    :class="[
                                        'px-2 py-2 text-xs font-semibold rounded-lg border transition-all duration-200',
                                        selectedTheme === theme.value 
                                            ? 'bg-zinc-200 text-zinc-950 border-white'
                                            : 'bg-zinc-800/50 border-zinc-700/50 text-zinc-400 hover:bg-zinc-800'
                                    ]"
                                >
                                    {{ theme.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scheduled Events Dashboard -->
                <div class="backdrop-blur-md bg-zinc-900/40 border border-zinc-800/60 shadow-2xl rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4 border-b border-zinc-800/60 pb-3">
                        <h2 class="text-xl font-bold bg-gradient-to-r from-emerald-400 to-indigo-400 bg-clip-text text-transparent flex items-center gap-2">
                            <i class="pi pi-list"></i> Scheduled Events
                        </h2>
                        <span class="text-xs bg-zinc-850 px-2.5 py-1 rounded-full text-zinc-400 border border-zinc-800 font-mono">
                            {{ events.length }} Total
                        </span>
                    </div>

                    <!-- Events list -->
                    <div class="space-y-3.5 max-h-[380px] overflow-y-auto pr-1 custom-scrollbar" v-if="events.length > 0">
                        <div v-for="event in events" :key="event.id" 
                            class="group relative backdrop-blur-sm bg-zinc-950/40 hover:bg-zinc-900/40 border border-zinc-850 hover:border-zinc-700/80 rounded-xl p-4 transition-all duration-200 shadow-sm"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex gap-2.5">
                                    <!-- Indicator -->
                                    <div class="w-1.5 self-stretch rounded-full" :style="{ backgroundColor: event.color || '#4f46e5' }"></div>
                                    
                                    <div>
                                        <h4 class="font-semibold text-sm text-zinc-200 group-hover:text-white transition-colors">
                                            {{ event.title }}
                                        </h4>
                                        <p class="text-xs text-zinc-400 mt-1" v-if="event.description">
                                            {{ event.description }}
                                        </p>
                                        <div class="flex flex-wrap gap-2 items-center mt-2.5 text-[10px] font-medium text-zinc-400">
                                            <span class="flex items-center gap-1 text-zinc-300">
                                                <i class="pi pi-calendar text-[10px]"></i>
                                                {{ event.event_date }}
                                            </span>
                                            <span class="w-1 h-1 bg-zinc-700 rounded-full" v-if="event.event_time"></span>
                                            <span class="flex items-center gap-1 text-zinc-300 font-mono" v-if="event.event_time">
                                                <i class="pi pi-clock text-[10px]"></i>
                                                {{ event.event_time }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <Button 
                                    icon="pi pi-trash" 
                                    severity="danger" 
                                    text 
                                    class="p-button-sm opacity-0 group-hover:opacity-100 transition-opacity focus:opacity-100 hover:text-red-400"
                                    aria-label="Delete Event"
                                    @click="confirmDeleteEvent(event.id)"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="text-center py-8 text-zinc-500 border border-dashed border-zinc-800 rounded-xl" v-else>
                        <i class="pi pi-calendar-times text-3xl text-zinc-700 mb-2"></i>
                        <p class="text-xs">No scheduled events. Click any date on the calendar to get started.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Creation Dialog (PrimeVue Dialog) -->
        <Dialog v-model:visible="showEventModal" header="Schedule New Event" :style="{ width: '450px' }" modal class="custom-dialog-dark" :dismissableMask="true">
            <form @submit.prevent="saveEvent" class="space-y-4 pt-2">
                <!-- Title -->
                <div class="flex flex-col gap-1.5">
                    <label for="event_title" class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Event Title <span class="text-rose-500">*</span></label>
                    <InputText id="event_title" v-model.trim="eventForm.title" class="w-full bg-zinc-950/80 border-zinc-800 text-white focus:border-indigo-500 text-sm" required autofocus placeholder="E.g., Design Sync Meeting" />
                </div>

                <!-- Description -->
                <div class="flex flex-col gap-1.5">
                    <label for="event_desc" class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Description</label>
                    <Textarea id="event_desc" v-model.trim="eventForm.description" rows="3" class="w-full bg-zinc-950/80 border-zinc-800 text-white focus:border-indigo-500 text-sm" placeholder="E.g., Review UI mockups with engineers" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Date -->
                    <div class="flex flex-col gap-1.5">
                        <label for="event_date" class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Date <span class="text-rose-500">*</span></label>
                        <InputText id="event_date" v-model="eventForm.event_date" class="w-full bg-zinc-950/80 border-zinc-800 text-white focus:border-indigo-500 text-sm font-mono" required placeholder="YYYY-MM-DD" />
                    </div>

                    <!-- Time -->
                    <div class="flex flex-col gap-1.5">
                        <label for="event_time" class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Time (24h format)</label>
                        <InputText id="event_time" v-model="eventForm.event_time" class="w-full bg-zinc-950/80 border-zinc-800 text-white focus:border-indigo-500 text-sm font-mono" placeholder="HH:MM (e.g. 14:30)" pattern="^(?:[01]\d|2[0-3]):[0-5]\d$" title="Please use 24h format (HH:MM)" />
                    </div>
                </div>

                <!-- Event Categorization Color -->
                <div>
                    <label class="block text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Category Color</label>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <button v-for="color in colorPresets" :key="color" type="button"
                                @click="eventForm.color = color"
                                :class="[
                                    'w-7 h-7 rounded-full border-2 transition-transform duration-150 active:scale-90',
                                    eventForm.color === color ? 'scale-110 border-white shadow-md shadow-zinc-850' : 'border-transparent'
                                ]"
                                :style="{ backgroundColor: color }"
                                :aria-label="`Color preset ${color}`"
                            ></button>
                        </div>
                        <div class="h-6 w-[1px] bg-zinc-850"></div>
                        <input type="color" v-model="eventForm.color" class="w-8 h-8 rounded border-none bg-transparent cursor-pointer" title="Custom color picker">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 border-t border-zinc-850 pt-4 mt-6">
                    <Button label="Cancel" icon="pi pi-times" text severity="secondary" class="hover:bg-zinc-850 text-zinc-400 text-sm px-4 py-2" @click="showEventModal = false"/>
                    <Button label="Save Event" icon="pi pi-check" type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 border-none text-white text-sm px-4 py-2" />
                </div>
            </form>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import axios from 'axios';

// PrimeVue standard imports
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';

// Vanilla Calendar Pro imports
import { Calendar } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.css';

// Toast and Confirm providers
const toast = useToast();
const confirm = useConfirm();

// State variables
const calendarEl = ref(null);
const calendarInstance = ref(null);
const events = ref([]);

// Calendar parameters
const calendarType = ref('default');
const selectionDatesMode = ref('single');
const selectionTimeMode = ref(24);
const enableWeekNumbers = ref(true);
const displayDatesOutside = ref(false);
const disableDatesPast = ref(false);
const selectedTheme = ref('dark');

// Selection states
const selectedDates = ref([]);
const selectedTimeStr = ref('');

// Dialog state
const showEventModal = ref(false);
const eventForm = ref({
    title: '',
    description: '',
    event_date: '',
    event_time: '',
    color: '#4f46e5',
});

// Config options
const typeOptions = [
    { label: 'Single Month', value: 'default' },
    { label: 'Multi Month', value: 'multiple' },
    { label: 'Month Selector', value: 'month' },
    { label: 'Year Selector', value: 'year' },
];

const selectionModeOptions = [
    { label: 'Single Date', value: 'single' },
    { label: 'Multiple Dates', value: 'multiple' },
    { label: 'Date Range', value: 'multiple-ranged' },
    { label: 'Disabled', value: false },
];

const timeModeOptions = [
    { label: '24h Clock', value: 24 },
    { label: '12h Clock', value: 12 },
    { label: 'Disabled', value: false },
];

const themeOptions = [
    { label: 'Light', value: 'light' },
    { label: 'Dark', value: 'dark' },
    { label: 'System', value: 'system' },
];

const colorPresets = [
    '#4f46e5', // indigo
    '#10b981', // emerald
    '#f59e0b', // amber
    '#ef4444', // red
    '#06b6d4', // cyan
];

// Helper to escape HTML to prevent XSS in popups
const escapeHtml = (text) => {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

// Map events into Vanilla Calendar Pro popup config format
const formattedPopups = computed(() => {
    const popupsObj = {};
    events.value.forEach(event => {
        const dateStr = event.event_date;
        popupsObj[dateStr] = {
            modifier: 'has-event-marker',
            html: `
                <div class="custom-calendar-popup">
                    <div class="popup-title">
                        <span class="popup-dot" style="background-color: ${event.color || '#4f46e5'}"></span>
                        ${escapeHtml(event.title)}
                    </div>
                    ${event.event_time ? `<div class="popup-time">${event.event_time}</div>` : ''}
                    ${event.description ? `<div class="popup-desc">${escapeHtml(event.description)}</div>` : ''}
                </div>
            `
        };
    });
    return popupsObj;
});

// Fetch events from API
const fetchEvents = async () => {
    try {
        const response = await axios.get('/api/events');
        if (response.data && response.data.success) {
            events.value = response.data.data;
            // Update calendar instance popups configuration
            if (calendarInstance.value) {
                calendarInstance.value.settings.popups = formattedPopups.value;
                calendarInstance.value.update();
            }
        }
    } catch (error) {
        console.error('Failed to load events', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not fetch events from database', life: 4000 });
    }
};

// Setup calendar instance
const initCalendar = () => {
    if (!calendarEl.value) return;

    if (calendarInstance.value) {
        calendarInstance.value.destroy();
    }

    // Set configuration options matching Vanilla Calendar Pro specifications
    calendarInstance.value = new Calendar(calendarEl.value, {
        type: calendarType.value,
        selectionDatesMode: selectionDatesMode.value,
        selectionTimeMode: selectionTimeMode.value,
        enableWeekNumbers: enableWeekNumbers.value,
        displayDatesOutside: displayDatesOutside.value,
        disableDatesPast: disableDatesPast.value,
        selectedTheme: selectedTheme.value,
        popups: formattedPopups.value,
        selectedDates: selectedDates.value,
        
        onClickDate(self) {
            selectedDates.value = self.selectedDates;
            if (self.selectedDates.length > 0) {
                // autofill event date
                eventForm.value.event_date = self.selectedDates[0];
            }
        },
        onChangeTime(self) {
            selectedTimeStr.value = self.selectedTime;
            eventForm.value.event_time = self.selectedTime;
        }
    });

    calendarInstance.value.init();
};

// Re-init calendar whenever toggles change to apply updates instantly
watch(
    [calendarType, selectionDatesMode, selectionTimeMode, enableWeekNumbers, displayDatesOutside, disableDatesPast, selectedTheme],
    () => {
        initCalendar();
    }
);

// Open scheduled event modal
const openCreateEventDialog = () => {
    if (selectedDates.value.length > 0) {
        eventForm.value.event_date = selectedDates.value[0];
    } else {
        // Default to today in YYYY-MM-DD
        eventForm.value.event_date = new Date().toISOString().split('T')[0];
    }
    
    eventForm.value.title = '';
    eventForm.value.description = '';
    eventForm.value.event_time = selectedTimeStr.value || '';
    eventForm.value.color = '#4f46e5';
    
    showEventModal.value = true;
};

// Persist new event via axios
const saveEvent = async () => {
    try {
        const payload = {
            title: eventForm.value.title,
            description: eventForm.value.description || null,
            event_date: eventForm.value.event_date,
            event_time: eventForm.value.event_time || null,
            color: eventForm.value.color,
        };

        const response = await axios.post('/api/events', payload);
        if (response.data && response.data.success) {
            toast.add({ severity: 'success', summary: 'Success', detail: 'Event scheduled successfully', life: 3000 });
            showEventModal.value = false;
            await fetchEvents();
            // Clear selections after successful add
            selectedDates.value = [];
            initCalendar();
        }
    } catch (error) {
        console.error('Failed to create event', error);
        const errorMsg = error.response?.data?.message || 'Error occurred while saving event.';
        toast.add({ severity: 'error', summary: 'Validation/System Error', detail: errorMsg, life: 4000 });
    }
};

// Delete verification dialog
const confirmDeleteEvent = (eventId) => {
    confirm.require({
        message: 'Are you sure you want to delete this scheduled event?',
        header: 'Confirm Deletion',
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Cancel',
        acceptLabel: 'Delete',
        rejectClass: 'p-button-secondary p-button-outlined',
        acceptClass: 'p-button-danger',
        accept: () => deleteEvent(eventId),
    });
};

// Perform delete operations
const deleteEvent = async (eventId) => {
    try {
        const response = await axios.delete(`/api/events/${eventId}`);
        if (response.data && response.data.success) {
            toast.add({ severity: 'success', summary: 'Deleted', detail: 'Event deleted successfully', life: 3000 });
            await fetchEvents();
            initCalendar();
        }
    } catch (error) {
        console.error('Failed to delete event', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Could not delete the event from server', life: 4000 });
    }
};

// Component Mounted Hook
onMounted(async () => {
    await fetchEvents();
    initCalendar();
});

// Clean up references to prevent memory leaks
onUnmounted(() => {
    if (calendarInstance.value) {
        calendarInstance.value.destroy();
    }
});
</script>

<style>
/* Scoped Calendar & Popover styling overrides for sleek dark aesthetics */
.custom-calendar-theme .vc-dates {
    grid-gap: 2px;
}

/* Base modifications for the Calendar popup tooltip */
.custom-calendar-popup {
    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #f4f4f5;
    padding: 0.25rem 0.5rem;
}

.popup-title {
    font-weight: 700;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: #ffffff;
}

.popup-dot {
    display: inline-block;
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
}

.popup-time {
    font-size: 0.65rem;
    color: #a1a1aa;
    margin-top: 0.15rem;
    font-family: monospace;
}

.popup-desc {
    font-size: 0.7rem;
    color: #d4d4d8;
    margin-top: 0.25rem;
    max-width: 180px;
    line-height: 1.25;
}

/* Event marker indicator beneath calendar dates */
.vc-day {
    position: relative;
}

.vc-day.has-event-marker::after {
    content: '';
    position: absolute;
    bottom: 4px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    border-radius: 9999px;
    background-color: #6366f1; /* default indigo highlight */
}

/* Dark mode styling details for dialog */
.custom-dialog-dark .p-dialog-header {
    background-color: #18181b !important;
    color: #ffffff !important;
    border-bottom: 1px solid #27272a !important;
}

.custom-dialog-dark .p-dialog-content {
    background-color: #18181b !important;
    color: #f4f4f5 !important;
}

.custom-dialog-dark .p-dialog-footer {
    background-color: #18181b !important;
    border-top: 1px solid #27272a !important;
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #27272a;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #3f3f46;
}
</style>

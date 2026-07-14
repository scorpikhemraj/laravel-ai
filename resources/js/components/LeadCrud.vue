<template>
    <div>
        <div>
            <div class="w-full">
                <Toast />
                <ConfirmDialog></ConfirmDialog>

                <!-- Custom Headless accessible TanStack Table -->
                <LeadTable
                    :data="leads"
                    :total-records="totalRecords"
                    :loading="loading"
                    v-model:page="lazyParams.page"
                    v-model:perPage="lazyParams.rows"
                    v-model:sortField="lazyParams.sortField"
                    v-model:sortOrder="lazyParams.sortOrder"
                    v-model:search="searchQuery"
                    v-model:selectedRowIds="selectedRowIds"
                    v-model:columnFilters="columnFilters"
                    @add-lead="openNew"
                    @edit-lead="editLead"
                    @delete-lead="confirmDeleteLead"
                    @bulk-delete="confirmBulkDeleteLeads"
                    @toggle-favorite="handleToggleFavorite"
                    @bulk-update="handleBulkUpdate"
                />
            </div>
        </div>

        <Drawer v-model:visible="leadDialog" position="right" :style="{width: '450px'}" header="Lead Details" class="p-fluid" :modal="false" :dismissable="false">
            <div class="flex flex-col gap-2 mb-4">
                <label for="first_name" class="font-semibold">First Name <span class="text-red-500">*</span></label>
                <InputText id="first_name" v-model.trim="lead.first_name" class="w-full" required="true" autofocus :invalid="submitted && !lead.first_name" />
                <small class="text-red-500" v-if="submitted && !lead.first_name">First Name is required.</small>
            </div>
            
            <div class="flex flex-col gap-2 mb-4">
                <label for="last_name" class="font-semibold">Last Name <span class="text-red-500">*</span></label>
                <InputText id="last_name" v-model.trim="lead.last_name" class="w-full" required="true" :invalid="submitted && !lead.last_name" />
                <small class="text-red-500" v-if="submitted && !lead.last_name">Last Name is required.</small>
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label for="email" class="font-semibold">Email</label>
                <InputText id="email" v-model.trim="lead.email" class="w-full" :invalid="submitted && lead.email && !isValidEmail(lead.email)" />
                <small class="text-red-500" v-if="submitted && lead.email && !isValidEmail(lead.email)">Please enter a valid email address.</small>
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label for="phone" class="font-semibold">Phone</label>
                <InputText id="phone" v-model.trim="lead.phone" class="w-full" />
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label for="company" class="font-semibold">Company</label>
                <InputText id="company" v-model.trim="lead.company" class="w-full" />
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label for="status" class="font-semibold">Status <span class="text-red-500">*</span></label>
                <Dropdown id="status" v-model="lead.status" :options="statuses" optionLabel="label" optionValue="value" placeholder="Select a Status" class="w-full" :invalid="submitted && !lead.status" />
                <small class="text-red-500" v-if="submitted && !lead.status">Status is required.</small>
            </div>

            <div class="flex flex-col gap-2 mb-4">
                <label for="source" class="font-semibold">Source <span class="text-red-500">*</span></label>
                <Dropdown id="source" v-model="lead.source" :options="sources" optionLabel="label" optionValue="value" placeholder="Select a Source" class="w-full" :invalid="submitted && !lead.source" />
                <small class="text-red-500" v-if="submitted && !lead.source">Source is required.</small>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <Button label="Cancel" icon="pi pi-times" text severity="secondary" @click="hideDialog"/>
                <Button label="Save" icon="pi pi-check" @click="saveLead" />
            </div>
        </Drawer>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import axios from 'axios';

// PrimeVue components for outer controls & modals
import Button from 'primevue/button';
import Drawer from 'primevue/drawer';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';

// Custom TanStack Table Component
import LeadTable from './LeadTable.vue';

const toast = useToast();
const confirm = useConfirm();

const leads = ref([]);
const loading = ref(true);
const leadDialog = ref(false);
const lead = ref({});
const totalRecords = ref(0);
const lazyParams = ref({
    rows: 10,
    page: 0,
    sortField: null,
    sortOrder: null
});
const searchQuery = ref('');
const selectedRowIds = ref({});
const columnFilters = ref({});
const submitted = ref(false);
const initialized = ref(false);

const statuses = ref([
    {label: 'New', value: 'new'},
    {label: 'Contacted', value: 'contacted'},
    {label: 'Qualified', value: 'qualified'},
    {label: 'Lost', value: 'lost'}
]);

const sources = ref([
    {label: 'Website', value: 'website'},
    {label: 'Referral', value: 'referral'},
    {label: 'Social Media', value: 'social_media'},
    {label: 'Cold Call', value: 'cold_call'},
    {label: 'Advertising', value: 'advertising'}
]);

onMounted(async () => {
    parseUrlParams();
    await loadLazyData();
    initialized.value = true;
});

// Parse URL Query Parameters on Load
const parseUrlParams = () => {
    const params = new URLSearchParams(window.location.search);
    
    const urlPage = parseInt(params.get('page') || '1');
    lazyParams.value.page = Math.max(0, urlPage - 1);
    
    const urlPerPage = parseInt(params.get('perPage') || '10');
    lazyParams.value.rows = urlPerPage;
    
    lazyParams.value.sortField = params.get('sortField') || null;
    
    const urlSortOrder = params.get('sortOrder');
    lazyParams.value.sortOrder = urlSortOrder ? parseInt(urlSortOrder) : null;
    
    searchQuery.value = params.get('search') || '';
    
    // Parse column filters from URL params starting with 'f_'
    const newFilters = {};
    params.forEach((val, key) => {
        if (key.startsWith('f_')) {
            const colId = key.substring(2);
            newFilters[colId] = val;
        }
    });
    columnFilters.value = newFilters;
};

// Update Browser URL Query String without reloading
const updateUrl = () => {
    const params = new URLSearchParams();
    
    if (lazyParams.value.page > 0) {
        params.set('page', (lazyParams.value.page + 1).toString());
    }
    
    if (lazyParams.value.rows !== 10) {
        params.set('perPage', lazyParams.value.rows.toString());
    }
    
    if (lazyParams.value.sortField) {
        params.set('sortField', lazyParams.value.sortField);
        if (lazyParams.value.sortOrder) {
            params.set('sortOrder', lazyParams.value.sortOrder.toString());
        }
    }
    
    if (searchQuery.value) {
        params.set('search', searchQuery.value);
    }
    
    // Add column filters to URL
    Object.entries(columnFilters.value).forEach(([colId, val]) => {
        if (val) {
            params.set(`f_${colId}`, val);
        }
    });
    
    const newQueryString = params.toString();
    const newUrl = window.location.pathname + (newQueryString ? '?' + newQueryString : '');
    window.history.replaceState({ path: newUrl }, '', newUrl);
};

// Load leads data from backend API
const loadLazyData = async () => {
    loading.value = true;
    try {
        const page = lazyParams.value.page + 1;
        const perPage = lazyParams.value.rows;
        const sortField = lazyParams.value.sortField;
        const sortOrder = lazyParams.value.sortOrder;
        const search = searchQuery.value;

        // Build base query parameters
        const apiParams = {
            page,
            perPage,
            sortField,
            sortOrder,
            search
        };

        // Add column-level filters to query parameters
        Object.entries(columnFilters.value).forEach(([colId, val]) => {
            if (val) {
                apiParams[`filter_${colId}`] = val;
            }
        });

        const response = await axios.get('/api/leads', {
            params: apiParams
        });
        leads.value = response.data.data;
        totalRecords.value = response.data.total;
    } catch (error) {
        toast.add({severity:'error', summary: 'Error', detail: 'Failed to fetch leads', life: 3000});
    } finally {
        loading.value = false;
    }
};

const triggerFetch = () => {
    if (!initialized.value) return;
    updateUrl();
    loadLazyData();
};

// Watchers for Table state changes
watch([
    () => lazyParams.value.page,
    () => lazyParams.value.rows,
    () => lazyParams.value.sortField,
    () => lazyParams.value.sortOrder,
], () => {
    triggerFetch();
});

let debounceTimer;
watch(() => searchQuery.value, () => {
    if (!initialized.value) return;
    
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        // Reset to first page when filtering
        lazyParams.value.page = 0;
        triggerFetch();
    }, 450);
});

watch(columnFilters, () => {
    if (!initialized.value) return;
    
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        // Reset to first page when filtering
        lazyParams.value.page = 0;
        triggerFetch();
    }, 450);
}, { deep: true });

const openNew = () => {
    lead.value = {
        status: 'new',
        source: 'website'
    };
    submitted.value = false;
    leadDialog.value = true;
};

const hideDialog = () => {
    leadDialog.value = false;
    submitted.value = false;
};

const isValidEmail = (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

const saveLead = async () => {
    submitted.value = true;

    const isFirstNameValid = !!lead.value.first_name?.trim();
    const isLastNameValid = !!lead.value.last_name?.trim();
    const isStatusValid = !!lead.value.status;
    const isSourceValid = !!lead.value.source;
    const isEmailValid = !lead.value.email || isValidEmail(lead.value.email);

    if (isFirstNameValid && isLastNameValid && isStatusValid && isSourceValid && isEmailValid) {
        try {
            if (lead.value.id) {
                const response = await axios.put(`/api/leads/${lead.value.id}`, lead.value);
                const index = leads.value.findIndex(l => l.id === lead.value.id);
                leads.value[index] = response.data;
                toast.add({severity:'success', summary: 'Successful', detail: 'Lead Updated', life: 3000});
            } else {
                const response = await axios.post('/api/leads', lead.value);
                leads.value.unshift(response.data);
                totalRecords.value++;
                toast.add({severity:'success', summary: 'Successful', detail: 'Lead Created', life: 3000});
            }
            leadDialog.value = false;
            lead.value = {};
        } catch (error) {
            console.error(error.response?.data);
            toast.add({severity:'error', summary: 'Error', detail: 'Failed to save lead', life: 3000});
        }
    }
};

const editLead = (prod) => {
    lead.value = {...prod};
    leadDialog.value = true;
};

const confirmDeleteLead = (prod) => {
    confirm.require({
        message: 'Are you sure you want to delete ' + prod.first_name + ' ' + prod.last_name + '?',
        header: 'Confirm Delete',
        icon: 'pi pi-exclamation-triangle',
        accept: async () => {
            try {
                await axios.delete(`/api/leads/${prod.id}`);
                leads.value = leads.value.filter(val => val.id !== prod.id);
                totalRecords.value--;
                // Remove from selection if deleted
                if (selectedRowIds.value[prod.id]) {
                    delete selectedRowIds.value[prod.id];
                }
                toast.add({severity:'success', summary: 'Successful', detail: 'Lead Deleted', life: 3000});
            } catch (error) {
                toast.add({severity:'error', summary: 'Error', detail: 'Failed to delete lead', life: 3000});
            }
        }
    });
};

const confirmBulkDeleteLeads = (ids) => {
    confirm.require({
        message: `Are you sure you want to delete the ${ids.length} selected leads?`,
        header: 'Confirm Bulk Delete',
        icon: 'pi pi-exclamation-triangle',
        accept: async () => {
            try {
                await axios.post('/api/leads/bulk-delete', { ids });
                leads.value = leads.value.filter(lead => !ids.includes(lead.id));
                totalRecords.value -= ids.length;
                selectedRowIds.value = {}; // Reset selection
                toast.add({severity:'success', summary: 'Successful', detail: 'Selected Leads Deleted', life: 3000});
            } catch (error) {
                toast.add({severity:'error', summary: 'Error', detail: 'Failed to delete selected leads', life: 3000});
            }
        }
    });
};

const handleToggleFavorite = async (lead) => {
    try {
        const newFavoriteState = !lead.is_favorite;
        await axios.post('/api/leads/bulk-update', {
            ids: [lead.id],
            field: 'is_favorite',
            value: newFavoriteState
        });
        lead.is_favorite = newFavoriteState;
        toast.add({
            severity: 'success',
            summary: 'Successful',
            detail: newFavoriteState ? 'Lead added to favorites' : 'Lead removed from favorites',
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to update favorite status',
            life: 3000
        });
    }
};

const handleBulkUpdate = async ({ ids, field, value }) => {
    if (!ids || ids.length === 0) return;
    try {
        await axios.post('/api/leads/bulk-update', { ids, field, value });
        
        // Update local leads data
        leads.value = leads.value.map(l => {
            if (ids.includes(l.id)) {
                return { ...l, [field]: value };
            }
            return l;
        });
        
        // Clear selection
        selectedRowIds.value = {};
        
        // Map field names to friendly terms for Toast
        const fieldLabels = {
            status: 'Status',
            source: 'Source',
            is_favorite: 'Favorite Status'
        };
        const friendlyField = fieldLabels[field] || field;

        toast.add({
            severity: 'success',
            summary: 'Successful',
            detail: `${ids.length} leads updated (${friendlyField})`,
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Failed to update selected leads',
            life: 3000
        });
    }
};
</script>

<style scoped>
/* Optional styling */
</style>

<template>
  <div class="relative w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-300 dark:border-surface-600 dark:bg-surface-700">
    <!-- Loader overlay -->
    <div 
      v-if="loading" 
      class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 backdrop-blur-xs transition-opacity duration-300 dark:bg-surface-700/70"
    >
      <div class="flex flex-col items-center gap-3">
        <i class="pi pi-spin pi-spinner text-3xl text-brand"></i>
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Loading leads...</span>
      </div>
    </div>

    <!-- Toolbar / Table Controls -->
    <div class="flex flex-col gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-surface-600">
      <!-- Left side: Search & Selected count -->
      <div class="flex flex-1 flex-wrap items-center gap-3">
        <!-- Search Input -->
        <div class="relative w-full max-w-xs">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3">
            <i class="pi pi-search text-gray-400"></i>
          </span>
          <input
            type="text"
            :value="search"
            @input="onSearchInput"
            placeholder="Search leads..."
            class="w-full rounded-lg border border-gray-300 bg-gray-50 py-2 pl-10 pr-4 text-sm outline-none transition-all duration-200 focus:border-brand focus:bg-white focus:ring-2 focus:ring-brand/20 dark:border-surface-500 dark:bg-surface-600 dark:text-white dark:focus:border-brand dark:focus:bg-surface-500"
            aria-label="Search leads"
          />
        </div>

        <!-- Selection Status & Bulk Actions -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="translate-y-1 opacity-0"
          enter-to-class="translate-y-0 opacity-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="translate-y-0 opacity-100"
          leave-to-class="translate-y-1 opacity-0"
        >
          <div v-if="selectedCount > 0" class="flex items-center gap-3 rounded-lg bg-brand/10 px-3 py-1.5 text-sm font-medium text-brand-dark dark:bg-brand/20 dark:text-brand-light">
            <span>{{ selectedCount }} selected</span>
            <button 
              @click="clearSelection" 
              class="text-xs hover:underline focus:outline-none"
              @mouseenter="showTooltip($event, 'Clear selection')"
              @mouseleave="hideTooltip"
            >
              Clear
            </button>
            <div class="h-4 w-px bg-brand/20"></div>
            <button 
              @click="emitBulkDelete"
              class="flex items-center gap-1 text-xs text-red-600 hover:text-red-700 focus:outline-none dark:text-red-400 dark:hover:text-red-300"
              @mouseenter="showTooltip($event, 'Delete selected leads')"
              @mouseleave="hideTooltip"
            >
              <i class="pi pi-trash text-xs"></i> Delete
            </button>
            <div class="h-4 w-px bg-brand/20"></div>
            <!-- Bulk Update Actions Dropdown -->
            <div class="relative">
              <button 
                @click="showBulkUpdateMenu = !showBulkUpdateMenu"
                class="flex items-center gap-1 text-xs text-brand hover:underline focus:outline-none dark:text-brand-light"
                @mouseenter="showTooltip($event, 'Bulk actions')"
                @mouseleave="hideTooltip"
                ref="bulkUpdateBtn"
              >
                <i class="pi pi-cog text-xs"></i> Bulk Update <i class="pi pi-chevron-down text-[10px]"></i>
              </button>

              <div 
                v-if="showBulkUpdateMenu" 
                class="absolute left-0 mt-2 w-64 origin-top-left rounded-lg border border-gray-200 bg-white p-2 shadow-lg ring-1 ring-black/5 focus:outline-none z-20 dark:border-surface-500 dark:bg-surface-600"
                role="menu"
                v-click-outside="closeBulkUpdateMenu"
              >
                <!-- Group 1: Status -->
                <div class="px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Update Status
                </div>
                <div class="grid grid-cols-2 gap-1 p-1">
                  <button 
                    v-for="status in ['new', 'contacted', 'qualified', 'lost']" 
                    :key="status"
                    @click="triggerBulkUpdate('status', status)"
                    class="rounded px-2 py-1 text-left text-xs capitalize hover:bg-gray-100 dark:hover:bg-surface-500 text-gray-700 dark:text-gray-200"
                  >
                    {{ status }}
                  </button>
                </div>

                <hr class="my-1 border-gray-100 dark:border-surface-500" />

                <!-- Group 2: Source -->
                <div class="px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Update Source
                </div>
                <div class="grid grid-cols-2 gap-1 p-1">
                  <button 
                    v-for="src in ['website', 'referral', 'social_media', 'cold_call', 'advertising']" 
                    :key="src"
                    @click="triggerBulkUpdate('source', src)"
                    class="rounded px-2 py-1 text-left text-xs capitalize hover:bg-gray-100 dark:hover:bg-surface-500 text-gray-700 dark:text-gray-200 whitespace-nowrap overflow-hidden text-ellipsis"
                    @mouseenter="showTooltip($event, formatSourceLabel(src))"
                    @mouseleave="hideTooltip"
                  >
                    {{ formatSourceLabel(src) }}
                  </button>
                </div>

                <hr class="my-1 border-gray-100 dark:border-surface-500" />

                <!-- Group 3: Favorite -->
                <div class="px-2 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Toggle Favorite
                </div>
                <div class="flex gap-1 p-1">
                  <button 
                    @click="triggerBulkUpdate('is_favorite', true)"
                    class="flex-1 rounded px-2 py-1 text-center text-xs hover:bg-gray-100 dark:hover:bg-surface-500 text-gray-700 dark:text-gray-200"
                  >
                    <i class="pi pi-star-fill text-amber-500 text-[10px]"></i> Favorite
                  </button>
                  <button 
                    @click="triggerBulkUpdate('is_favorite', false)"
                    class="flex-1 rounded px-2 py-1 text-center text-xs hover:bg-gray-100 dark:hover:bg-surface-500 text-gray-700 dark:text-gray-200"
                  >
                    <i class="pi pi-star text-[10px]"></i> Unfavorite
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
      <!-- Right side: Column Visibility Toggle & Add New -->
      <div class="flex items-center justify-end gap-3">
        <!-- Filters toggle button -->
        <button
          @click="showFilters = !showFilters"
          :class="[
            'flex items-center gap-2 rounded-lg border px-3.5 py-2 text-sm font-medium shadow-xs focus:outline-none focus:ring-2 focus:ring-brand/20 transition-all duration-200',
            showFilters 
              ? 'border-brand bg-brand/10 text-brand dark:border-brand dark:bg-brand/20 dark:text-brand-light' 
              : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-surface-500 dark:bg-surface-600 dark:text-gray-200 dark:hover:bg-surface-500'
          ]"
          @mouseenter="showTooltip($event, showFilters ? 'Hide column filters' : 'Show column filters')"
          @mouseleave="hideTooltip"
        >
          <i class="pi pi-filter"></i>
          <span>Filters</span>
          <span 
            v-if="activeFiltersCount > 0" 
            class="ml-1 flex h-4.5 w-4.5 items-center justify-center rounded-full bg-brand text-[10px] font-bold text-white dark:bg-brand-light dark:text-surface-900"
          >
            {{ activeFiltersCount }}
          </span>
        </button>

        <!-- Column Visibility Popover -->
        <div class="relative">
          <button
            @click="toggleVisibilityMenu"
            class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-xs hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand/20 dark:border-surface-500 dark:bg-surface-600 dark:text-gray-200 dark:hover:bg-surface-500"
            aria-haspopup="menu"
            :aria-expanded="showVisibilityMenu"
            ref="visibilityBtn"
          >
            <i class="pi pi-eye"></i>
            <span>Columns</span>
            <i class="pi pi-chevron-down text-xs"></i>
          </button>

          <!-- Dropdown menu -->
          <div 
            v-if="showVisibilityMenu" 
            class="absolute right-0 mt-2 w-64 origin-top-right rounded-xl border border-gray-200 bg-white p-3 shadow-xl ring-1 ring-black/5 focus:outline-none z-30 dark:border-surface-500 dark:bg-surface-600"
            role="menu"
            aria-label="Toggle column visibility"
            v-click-outside="closeVisibilityMenu"
          >
            <div class="flex items-center justify-between px-1 pb-2">
              <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Columns
              </span>
              <div class="flex gap-2">
                <button 
                  @click="showAllColumns" 
                  class="text-[11px] font-semibold text-brand hover:underline dark:text-brand-light"
                >
                  Show All
                </button>
                <span class="text-gray-300 dark:text-surface-500">|</span>
                <button 
                  @click="hideAllColumns" 
                  class="text-[11px] font-semibold text-gray-500 hover:underline dark:text-gray-400"
                >
                  Hide All
                </button>
              </div>
            </div>

            <!-- Column Search -->
            <div class="relative mb-2">
              <span class="absolute inset-y-0 left-0 flex items-center pl-2.5">
                <i class="pi pi-search text-[10px] text-gray-400"></i>
              </span>
              <input
                v-model="columnSearchQuery"
                type="text"
                placeholder="Search columns..."
                class="w-full rounded-md border border-gray-200 bg-gray-50 py-1 pl-7 pr-6 text-xs outline-none transition focus:border-brand focus:bg-white dark:border-surface-500 dark:bg-surface-700 dark:text-white dark:focus:border-brand dark:focus:bg-surface-700"
              />
              <button
                v-if="columnSearchQuery"
                @click="columnSearchQuery = ''"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white focus:outline-none"
              >
                <i class="pi pi-times text-[10px]"></i>
              </button>
            </div>

            <hr class="mb-2 border-gray-100 dark:border-surface-500" />
            
            <div class="max-h-64 overflow-y-auto custom-scrollbar space-y-3">
              <div v-for="group in groupedColumns" :key="group.name" class="space-y-1">
                <div class="px-1 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                  {{ group.name }}
                </div>
                <label 
                  v-for="column in group.columns" 
                  :key="column.id" 
                  class="flex cursor-pointer items-center justify-between rounded-md px-1.5 py-1 text-xs hover:bg-gray-100 dark:hover:bg-surface-500 transition-colors"
                >
                  <span class="flex items-center gap-2">
                    <input
                      type="checkbox"
                      :checked="column.getIsVisible()"
                      @change="column.toggleVisibility($event.target.checked)"
                      class="rounded border-gray-300 text-brand focus:ring-brand/20 dark:border-surface-400 dark:bg-surface-650 h-3.5 w-3.5"
                    />
                    <span class="capitalize text-gray-700 dark:text-gray-200">
                      {{ formatColumnHeader(column.id) }}
                    </span>
                  </span>
                  <!-- Small Pin Indicator -->
                  <i 
                    v-if="column.getIsPinned() === 'left'" 
                    class="pi pi-bookmark-fill text-[10px] text-brand"
                    @mouseenter="showTooltip($event, 'Pinned column')"
                    @mouseleave="hideTooltip"
                  ></i>
                </label>
              </div>
              <div v-if="groupedColumns.length === 0" class="text-center py-4 text-xs text-gray-400 dark:text-gray-500">
                No columns match search
              </div>
            </div>
          </div>
        </div>

        <!-- Add New button -->
        <button
          @click="$emit('add-lead')"
          class="flex items-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-xs transition-colors hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand/35"
        >
          <i class="pi pi-plus"></i>
          <span>New Lead</span>
        </button>
      </div>
    </div>

    <!-- Headless Accessible Table Grid -->
    <div class="w-full overflow-x-auto custom-scrollbar">
      <table class="w-full border-collapse text-left text-sm" role="grid" aria-label="Leads list">
        <thead class="bg-gray-50 dark:bg-surface-600/40 text-gray-700 dark:text-gray-300">
          <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" role="row">
            <th 
              v-for="header in headerGroup.headers" 
              :key="header.id" 
              role="columnheader"
              :aria-sort="getAriaSort(header.column)"
              class="group border-b border-gray-200 px-6 py-4 font-semibold tracking-wider dark:border-surface-600 uppercase text-xs"
              :class="{
                'sticky z-10 bg-gray-50 dark:bg-surface-600 shadow-[inset_-1px_0_0_0_rgba(0,0,0,0.1)] dark:shadow-[inset_-1px_0_0_0_rgba(255,255,255,0.1)]': header.column.getIsPinned(),
              }"
              :style="{
                width: header.column.getSize() + 'px',
                left: header.column.getIsPinned() ? header.column.getStart('left') + 'px' : undefined,
                position: header.column.getIsPinned() ? 'sticky' : undefined,
                zIndex: header.column.getIsPinned() ? 20 : undefined,
              }"
            >
              <!-- Checkbox header / No sort -->
              <div v-if="header.isPlaceholder"></div>
              
              <div v-else-if="header.column.id === 'select'" class="flex items-center justify-center">
                <input
                  type="checkbox"
                  :checked="table.getIsAllPageRowsSelected()"
                  :indeterminate="table.getIsSomePageRowsSelected()"
                  @change="table.getToggleAllPageRowsSelectedHandler()($event)"
                  class="h-4 w-4 rounded border-gray-300 text-brand focus:ring-brand/20 dark:border-surface-500 dark:bg-surface-600"
                  aria-label="Select all leads on current page"
                />
              </div>

              <!-- Favorite column header -->
              <div v-else-if="header.column.id === 'is_favorite'" class="flex items-center justify-center">
                <button
                  v-if="header.column.getCanSort()"
                  @click="header.column.getToggleSortingHandler()($event)"
                  class="group/fav flex items-center justify-center focus:outline-none"
                  @mouseenter="showTooltip($event, 'Sort by favorite')"
                  @mouseleave="hideTooltip"
                >
                  <i class="pi pi-star text-gray-400 group-hover/fav:text-amber-500 transition-colors text-sm"></i>
                  <i 
                    v-if="header.column.getIsSorted() === 'asc'"
                    class="pi pi-chevron-up text-brand font-bold text-[10px] ml-0.5"
                  ></i>
                  <i 
                    v-else-if="header.column.getIsSorted() === 'desc'"
                    class="pi pi-chevron-down text-brand font-bold text-[10px] ml-0.5"
                  ></i>
                </button>
              </div>

              <!-- Sortable / Regular headers -->
              <div v-else class="flex items-center justify-between gap-2">
                <button
                  v-if="header.column.getCanSort()"
                  @click="header.column.getToggleSortingHandler()($event)"
                  class="group flex flex-1 items-center gap-2 font-semibold hover:text-gray-900 focus:outline-none dark:hover:text-white"
                  @mouseenter="showTooltip($event, `Sort by ${formatColumnHeader(header.column.id)}`)"
                  @mouseleave="hideTooltip"
                >
                  <span>
                    <component :is="header.column.columnDef.header" v-if="typeof header.column.columnDef.header === 'function'" />
                    <span v-else>{{ header.column.columnDef.header }}</span>
                  </span>
                  
                  <span class="flex items-center">
                    <i 
                      v-if="header.column.getIsSorted() === 'asc'" 
                      class="pi pi-chevron-up text-brand font-bold text-xs"
                    ></i>
                    <i 
                      v-else-if="header.column.getIsSorted() === 'desc'" 
                      class="pi pi-chevron-down text-brand font-bold text-xs"
                    ></i>
                    <i 
                      v-else 
                      class="pi pi-sort text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity text-xs"
                    ></i>
                  </span>
                </button>

                <div v-else class="flex-1">
                  <component :is="header.column.columnDef.header" v-if="typeof header.column.columnDef.header === 'function'" />
                  <span v-else>{{ header.column.columnDef.header }}</span>
                </div>

                <!-- Column Pinning Control -->
                <button
                  v-if="header.column.id !== 'select' && header.column.id !== 'actions' && header.column.id !== 'is_favorite'"
                  @click.stop="togglePinColumn(header.column.id)"
                  class="text-gray-400 hover:text-brand focus:outline-none transition-opacity duration-200"
                  :class="{
                    'opacity-100 text-brand': header.column.getIsPinned() === 'left',
                    'opacity-0 group-hover:opacity-100': header.column.getIsPinned() !== 'left'
                  }"
                  @mouseenter="showTooltip($event, header.column.getIsPinned() === 'left' ? 'Unpin column' : 'Pin column to left')"
                  @mouseleave="hideTooltip"
                >
                  <i class="pi pi-bookmark text-xs" :class="{ 'text-brand': header.column.getIsPinned() === 'left' }"></i>
                </button>
              </div>
            </th>
          </tr>
          <!-- Column Filter Inputs -->
          <template v-if="showFilters">
            <tr v-for="headerGroup in table.getHeaderGroups()" :key="'filters-' + headerGroup.id" role="row">
              <th
                v-for="header in headerGroup.headers"
                :key="'filter-' + header.id"
                class="border-b border-gray-200 px-4 py-2 dark:border-surface-600 bg-gray-50/50 dark:bg-surface-650/40"
                :class="{
                  'sticky z-10 bg-gray-50 dark:bg-surface-600 shadow-[inset_-1px_0_0_0_rgba(0,0,0,0.1)] dark:shadow-[inset_-1px_0_0_0_rgba(255,255,255,0.1)]': header.column.getIsPinned(),
                }"
                :style="{
                  width: header.column.getSize() + 'px',
                  left: header.column.getIsPinned() ? header.column.getStart('left') + 'px' : undefined,
                  position: header.column.getIsPinned() ? 'sticky' : undefined,
                  zIndex: header.column.getIsPinned() ? 20 : undefined,
                }"
              >
                <!-- Selection checkbox has no filter -->
                <div v-if="header.column.id === 'select'" class="h-4"></div>
                
                <!-- Actions has no filter -->
                <div v-else-if="header.column.id === 'actions'" class="h-4"></div>
  
                <!-- Dropdown for Favorite column -->
                <div v-else-if="header.column.id === 'is_favorite'">
                  <select
                    :value="columnFilters[header.column.id] || ''"
                    @change="onColumnFilterChange(header.column.id, $event)"
                    class="w-full rounded-md border border-gray-300 bg-white py-1 px-0.5 text-xs outline-none transition focus:border-brand dark:border-surface-500 dark:bg-surface-600 dark:text-white"
                    aria-label="Filter favorites"
                  >
                    <option value="">All</option>
                    <option value="1">⭐ Yes</option>
                    <option value="0">☆ No</option>
                  </select>
                </div>
  
                <!-- Dropdown for Status column -->
                <div v-else-if="header.column.id === 'status'">
                  <select
                    :value="columnFilters[header.column.id] || ''"
                    @change="onColumnFilterChange(header.column.id, $event)"
                    class="w-full rounded-md border border-gray-300 bg-white py-1 px-1.5 text-xs outline-none transition focus:border-brand dark:border-surface-500 dark:bg-surface-600 dark:text-white"
                    aria-label="Filter status"
                  >
                    <option value="">All</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="qualified">Qualified</option>
                    <option value="lost">Lost</option>
                  </select>
                </div>
  
                <!-- Dropdown for Source column -->
                <div v-else-if="header.column.id === 'source'">
                  <select
                    :value="columnFilters[header.column.id] || ''"
                    @change="onColumnFilterChange(header.column.id, $event)"
                    class="w-full rounded-md border border-gray-300 bg-white py-1 px-1.5 text-xs outline-none transition focus:border-brand dark:border-surface-500 dark:bg-surface-600 dark:text-white"
                    aria-label="Filter source"
                  >
                    <option value="">All</option>
                    <option value="website">Website</option>
                    <option value="referral">Referral</option>
                    <option value="social_media">Social Media</option>
                    <option value="cold_call">Cold Call</option>
                    <option value="advertising">Advertising</option>
                  </select>
                </div>
  
                <!-- Text filter input for other columns -->
                <div v-else class="relative flex items-center">
                  <input
                    type="text"
                    :value="columnFilters[header.column.id] || ''"
                    @input="onColumnFilterChange(header.column.id, $event)"
                    :placeholder="`Filter ${formatColumnHeader(header.column.id)}...`"
                    class="w-full rounded-md border border-gray-300 bg-white py-1 px-2 pr-6 text-xs outline-none transition-all duration-200 focus:border-brand focus:ring-1 focus:ring-brand/20 dark:border-surface-500 dark:bg-surface-600 dark:text-white dark:focus:border-brand"
                    :aria-label="`Filter by ${formatColumnHeader(header.column.id)}`"
                  />
                  <button
                    v-if="columnFilters[header.column.id]"
                    @click="onColumnFilterChange(header.column.id, '')"
                    class="absolute right-1 text-gray-400 hover:text-gray-600 dark:hover:text-white focus:outline-none"
                    type="button"
                    @mouseenter="showTooltip($event, `Clear ${formatColumnHeader(header.column.id)} filter`)"
                    @mouseleave="hideTooltip"
                  >
                    <i class="pi pi-times text-[10px]"></i>
                  </button>
                </div>
              </th>
            </tr>
          </template>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-surface-600 text-gray-700 dark:text-gray-300">
          <tr 
            v-for="row in table.getRowModel().rows" 
            :key="row.id" 
            role="row"
            :aria-selected="row.getIsSelected()"
            class="group hover:bg-gray-50/70 transition-colors duration-250 dark:hover:bg-surface-600/20"
            :class="{ 'bg-brand/5 dark:bg-brand/5': row.getIsSelected() }"
          >
            <td 
              v-for="cell in row.getVisibleCells()" 
              :key="cell.id" 
              role="gridcell"
              class="px-6 py-4 whitespace-nowrap align-middle"
              :class="[
                cell.column.getIsPinned() ? 'sticky td-sticky z-10 bg-white dark:bg-surface-700' : ''
              ]"
              :style="{
                left: cell.column.getIsPinned() ? cell.column.getStart('left') + 'px' : undefined,
                position: cell.column.getIsPinned() ? 'sticky' : undefined,
                zIndex: cell.column.getIsPinned() ? 10 : undefined,
              }"
            >
              <!-- Checkbox Cell -->
              <div v-if="cell.column.id === 'select'" class="flex items-center justify-center">
                <input
                  type="checkbox"
                  :checked="row.getIsSelected()"
                  :disabled="!row.getCanSelect()"
                  @change="row.getToggleSelectedHandler()($event)"
                  class="h-4 w-4 rounded border-gray-300 text-brand focus:ring-brand/20 dark:border-surface-500 dark:bg-surface-600"
                  :aria-label="`Select lead ${row.original.first_name} ${row.original.last_name}`"
                />
              </div>

               <!-- Favorite star Cell -->
              <div v-else-if="cell.column.id === 'is_favorite'" class="flex items-center justify-center">
                <button
                  @click.stop="toggleFavoriteRow(row.original)"
                  class="text-gray-300 hover:text-amber-500 transition-colors focus:outline-none"
                  :class="{ 'text-amber-500': row.original.is_favorite }"
                  @mouseenter="showTooltip($event, row.original.is_favorite ? 'Remove from favorites' : 'Mark as favorite')"
                  @mouseleave="hideTooltip"
                >
                  <i :class="row.original.is_favorite ? 'pi pi-star-fill text-sm' : 'pi pi-star text-sm'"></i>
                </button>
              </div>

              <!-- Name Cell (Combining first + last name) -->
              <div v-else-if="cell.column.id === 'name'" class="flex items-center gap-3">
                <div class="h-9 w-9 flex items-center justify-center rounded-full bg-brand/10 text-brand font-semibold text-sm uppercase dark:bg-brand/20 dark:text-brand-light">
                  {{ row.original.first_name[0] }}{{ row.original.last_name[0] }}
                </div>
                <div class="flex flex-col">
                  <span class="font-medium text-gray-900 dark:text-white">
                    {{ row.original.first_name }} {{ row.original.last_name }}
                  </span>
                  <span v-if="row.original.title" class="text-xs text-gray-400 dark:text-gray-500">
                    {{ row.original.title }}
                  </span>
                </div>
              </div>

              <!-- Status badge cell -->
              <div v-else-if="cell.column.id === 'status'">
                <span 
                  class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider"
                  :class="getStatusBadgeClass(row.original.status)"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                  {{ row.original.status }}
                </span>
              </div>

              <!-- Source cell -->
              <div v-else-if="cell.column.id === 'source'">
                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-surface-600 dark:text-gray-300 capitalize">
                  {{ formatSourceLabel(row.original.source) }}
                </span>
              </div>

              <!-- Value cell -->
              <div v-else-if="cell.column.id === 'value'" class="font-medium text-gray-900 dark:text-white">
                {{ formatCurrency(row.original.value) }}
              </div>

              <!-- Annual Revenue cell -->
              <div v-else-if="cell.column.id === 'annual_revenue'" class="font-medium text-gray-900 dark:text-white">
                {{ formatCurrency(row.original.annual_revenue) }}
              </div>

              <!-- Email cell -->
              <div v-else-if="cell.column.id === 'email'">
                <a 
                  v-if="row.original.email" 
                  :href="`mailto:${row.original.email}`"
                  class="inline-flex items-center gap-1.5 text-gray-700 hover:text-brand hover:underline dark:text-gray-300 dark:hover:text-brand-light"
                  @mouseenter="showTooltip($event, row.original.email)"
                  @mouseleave="hideTooltip"
                >
                  <i class="pi pi-envelope text-[10px] text-gray-400"></i>
                  <span class="max-w-[150px] truncate font-medium">{{ row.original.email }}</span>
                </a>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- Phone cell -->
              <div v-else-if="cell.column.id === 'phone'">
                <a 
                  v-if="row.original.phone" 
                  :href="`tel:${row.original.phone}`"
                  class="inline-flex items-center gap-1.5 text-gray-700 hover:text-brand hover:underline dark:text-gray-300 dark:hover:text-brand-light"
                  @mouseenter="showTooltip($event, row.original.phone)"
                  @mouseleave="hideTooltip"
                >
                  <i class="pi pi-phone text-[10px] text-gray-400"></i>
                  <span class="max-w-[130px] truncate font-medium">{{ row.original.phone }}</span>
                </a>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- Industry cell -->
              <div v-else-if="cell.column.id === 'industry'">
                <span 
                  v-if="row.original.industry"
                  class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-950/20 dark:text-blue-400 dark:ring-blue-900/30"
                  @mouseenter="showTooltip($event, `Industry: ${row.original.industry}`)"
                  @mouseleave="hideTooltip"
                >
                  {{ row.original.industry }}
                </span>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- Employees cell -->
              <div v-else-if="cell.column.id === 'employees'" class="font-medium text-gray-900 dark:text-white">
                <span v-if="row.original.employees !== null && row.original.employees !== undefined">
                  {{ Number(row.original.employees).toLocaleString() }}
                </span>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- Address cell -->
              <div v-else-if="cell.column.id === 'address'" class="max-w-[200px] truncate text-gray-700 dark:text-gray-300">
                <span 
                  v-if="row.original.address || row.original.city"
                  class="cursor-help font-normal"
                  @mouseenter="showTooltip($event, formatFullAddress(row.original))"
                  @mouseleave="hideTooltip"
                >
                  {{ row.original.address || '' }}
                  <span v-if="row.original.city" class="text-gray-400 dark:text-gray-500 text-xs block truncate mt-0.5">
                    {{ row.original.city }}, {{ row.original.state || '' }} {{ row.original.postal_code || '' }}
                  </span>
                </span>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- Website cell -->
              <div v-else-if="cell.column.id === 'website'">
                <a 
                  v-if="row.original.website" 
                  :href="row.original.website.startsWith('http') ? row.original.website : `https://${row.original.website}`" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1 text-brand hover:underline dark:text-brand-light font-medium"
                  @mouseenter="showTooltip($event, row.original.website)"
                  @mouseleave="hideTooltip"
                >
                  <i class="pi pi-link text-xs"></i>
                  <span class="max-w-[150px] truncate">{{ row.original.website.replace(/^https?:\/\/(www\.)?/, '') }}</span>
                </a>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- LinkedIn cell -->
              <div v-else-if="cell.column.id === 'linkedin_url'">
                <a 
                  v-if="row.original.linkedin_url" 
                  :href="row.original.linkedin_url.startsWith('http') ? row.original.linkedin_url : `https://${row.original.linkedin_url}`" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-1 text-[#0a66c2] hover:underline dark:text-sky-400 font-medium"
                  @mouseenter="showTooltip($event, row.original.linkedin_url)"
                  @mouseleave="hideTooltip"
                >
                  <i class="pi pi-linkedin text-xs"></i>
                  <span>LinkedIn</span>
                </a>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- Lead Score cell -->
              <div v-else-if="cell.column.id === 'lead_score'">
                <span 
                  v-if="row.original.lead_score !== null && row.original.lead_score !== undefined"
                  class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold shadow-sm transition-all"
                  :class="getLeadScoreBadgeClass(row.original.lead_score)"
                  @mouseenter="showTooltip($event, `Lead Score: ${row.original.lead_score}`)"
                  @mouseleave="hideTooltip"
                >
                  <i :class="getLeadScoreIcon(row.original.lead_score)"></i>
                  {{ row.original.lead_score }}
                </span>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- Notes cell -->
              <div v-else-if="cell.column.id === 'notes'" class="max-w-[200px] truncate text-gray-700 dark:text-gray-300">
                <span
                  v-if="row.original.notes"
                  class="cursor-help border-b border-dashed border-gray-300 dark:border-surface-500"
                  @mouseenter="showTooltip($event, row.original.notes)"
                  @mouseleave="hideTooltip"
                >
                  {{ row.original.notes }}
                </span>
                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
              </div>

              <!-- Actions cell -->
              <div v-else-if="cell.column.id === 'actions'" class="flex items-center gap-2">
                <button 
                  @click="$emit('edit-lead', row.original)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-surface-500 dark:hover:text-gray-200"
                  @mouseenter="showTooltip($event, 'Edit Lead')"
                  @mouseleave="hideTooltip"
                >
                  <i class="pi pi-pencil text-sm"></i>
                </button>
                <button 
                  @click="$emit('delete-lead', row.original)"
                  class="flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-950/30 dark:hover:text-red-300"
                  @mouseenter="showTooltip($event, 'Delete Lead')"
                  @mouseleave="hideTooltip"
                >
                  <i class="pi pi-trash text-sm"></i>
                </button>
              </div>

              <!-- Standard Render for other columns -->
              <div v-else>
                {{ cell.getValue() || '-' }}
              </div>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="table.getRowModel().rows.length === 0 && !loading" role="row">
            <td :colspan="table.getVisibleFlatColumns().length" class="px-6 py-12 text-center align-middle" role="gridcell">
              <div class="flex flex-col items-center justify-center gap-2 text-gray-400 dark:text-gray-500">
                <i class="pi pi-inbox text-4xl"></i>
                <p class="text-base font-semibold">No leads found</p>
                <p class="text-sm">Try clearing filters or adding a new lead.</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Table Footer / Pagination -->
    <div class="flex flex-col gap-4 border-t border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-surface-600 bg-gray-50/50 dark:bg-surface-700/50">
      <!-- Left side: Range stats & items per page -->
      <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
        <div>
          Showing 
          <span class="font-semibold text-gray-900 dark:text-white">{{ rangeStart }}</span> 
          to 
          <span class="font-semibold text-gray-900 dark:text-white">{{ rangeEnd }}</span> 
          of 
          <span class="font-semibold text-gray-900 dark:text-white">{{ totalRecords }}</span> entries
        </div>

        <div class="flex items-center gap-2">
          <span>Show</span>
          <select 
            :value="perPage" 
            @change="onPerPageChange"
            class="rounded-lg border border-gray-300 bg-white py-1 pl-2 pr-8 text-sm font-medium outline-none transition focus:border-brand focus:ring-2 focus:ring-brand/20 dark:border-surface-500 dark:bg-surface-600 dark:text-white"
            aria-label="Rows per page"
          >
            <option v-for="size in [10, 25, 50, 100]" :key="size" :value="size">{{ size }}</option>
          </select>
          <span>entries</span>
        </div>
      </div>

      <!-- Right side: Pagination Navigation -->
      <nav 
        v-if="pageCount > 1" 
        class="flex items-center -space-x-px rounded-lg shadow-xs" 
        aria-label="Pagination Navigation"
      >
        <!-- First Page -->
        <button
          @click="table.setPageIndex(0)"
          :disabled="!table.getCanPreviousPage()"
          class="flex items-center justify-center rounded-l-lg border border-gray-300 bg-white px-3 py-2 text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand/20 disabled:cursor-not-allowed disabled:opacity-40 dark:border-surface-500 dark:bg-surface-600 dark:text-gray-400 dark:hover:bg-surface-500"
          aria-label="First page"
        >
          <i class="pi pi-angle-double-left text-xs"></i>
        </button>

        <!-- Previous Page -->
        <button
          @click="table.previousPage()"
          :disabled="!table.getCanPreviousPage()"
          class="flex items-center justify-center border border-gray-300 bg-white px-3 py-2 text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand/20 disabled:cursor-not-allowed disabled:opacity-40 dark:border-surface-500 dark:bg-surface-600 dark:text-gray-400 dark:hover:bg-surface-500"
          aria-label="Previous page"
        >
          <i class="pi pi-angle-left text-xs"></i>
        </button>

        <!-- Page Numbers -->
        <button
          v-for="pageNo in pageNumbers"
          :key="pageNo"
          @click="typeof pageNo === 'number' ? table.setPageIndex(pageNo - 1) : null"
          :disabled="pageNo === '...'"
          class="flex items-center justify-center border px-4.5 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-brand/20"
          :class="[
            pageNo === page + 1
              ? 'border-brand bg-brand text-white focus:z-10'
              : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-surface-500 dark:bg-surface-600 dark:text-gray-300 dark:hover:bg-surface-500',
            pageNo === '...' ? 'cursor-default select-none opacity-70' : ''
          ]"
          :aria-current="pageNo === page + 1 ? 'page' : undefined"
          :aria-label="`Page ${pageNo}`"
        >
          {{ pageNo }}
        </button>

        <!-- Next Page -->
        <button
          @click="table.nextPage()"
          :disabled="!table.getCanNextPage()"
          class="flex items-center justify-center border border-gray-300 bg-white px-3 py-2 text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand/20 disabled:cursor-not-allowed disabled:opacity-40 dark:border-surface-500 dark:bg-surface-600 dark:text-gray-400 dark:hover:bg-surface-500"
          aria-label="Next page"
        >
          <i class="pi pi-angle-right text-xs"></i>
        </button>

        <!-- Last Page -->
        <button
          @click="table.setPageIndex(pageCount - 1)"
          :disabled="!table.getCanNextPage()"
          class="flex items-center justify-center rounded-r-lg border border-gray-300 bg-white px-3 py-2 text-gray-500 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand/20 disabled:cursor-not-allowed disabled:opacity-40 dark:border-surface-500 dark:bg-surface-600 dark:text-gray-400 dark:hover:bg-surface-500"
          aria-label="Last page"
        >
          <i class="pi pi-angle-double-right text-xs"></i>
        </button>
      </nav>
    </div>

    <!-- Body-level Tooltip Portal -->
    <teleport to="body">
      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-if="tooltip.show"
          v-show="tooltip.visible"
          :style="{
            position: 'fixed',
            left: `${tooltip.x}px`,
            top: `${tooltip.y - 8}px`,
            transform: 'translate(-50%, -100%)',
            pointerEvents: 'none',
            zIndex: 9999
          }"
          class="px-2.5 py-1.5 text-xs font-medium text-white bg-gray-900 dark:bg-surface-800 rounded-lg shadow-md border border-gray-850 dark:border-surface-650 max-w-[280px] break-words text-center whitespace-pre-line"
        >
          {{ tooltip.text }}
          <!-- Small arrow -->
          <div 
            class="absolute left-1/2 bottom-0 w-2 h-2 -mb-1 bg-gray-900 dark:bg-surface-800 border-r border-b border-gray-850 dark:border-surface-650 transform -translate-x-1/2 rotate-45"
          ></div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, h, onMounted } from 'vue';
import {
  useVueTable,
  getCoreRowModel,
  createColumnHelper,
  SortingState,
  VisibilityState,
  RowSelectionState,
  Updater,
  Column,
  ColumnPinningState,
} from '@tanstack/vue-table';

// Types
interface Lead {
  id: number;
  first_name: string;
  last_name: string;
  email: string | null;
  phone: string | null;
  company: string | null;
  title: string | null;
  city: string | null;
  country: string | null;
  status: 'new' | 'contacted' | 'qualified' | 'lost';
  source: 'website' | 'referral' | 'social_media' | 'cold_call' | 'advertising';
  value: number | string | null;
  user_id: number | null;
  is_favorite: boolean | number;
  created_at: string;
  updated_at: string;
  address: string | null;
  state: string | null;
  postal_code: string | null;
  industry: string | null;
  annual_revenue: number | string | null;
  number_of_employees: number | null;
  website: string | null;
  linkedin_url: string | null;
  lead_score: number | null;
  notes: string | null;
}

// Props
const props = withDefaults(
  defineProps<{
    data: Lead[];
    totalRecords: number;
    loading: boolean;
    page: number; // 0-indexed page index (for TanStack compatibility)
    perPage: number;
    sortField: string | null;
    sortOrder: number | null; // 1 = asc, -1 = desc
    search: string;
    selectedRowIds: Record<string, boolean>;
    columnFilters: Record<string, string>;
  }>(),
  {
    data: () => [],
    totalRecords: 0,
    loading: false,
    page: 0,
    perPage: 10,
    sortField: null,
    sortOrder: null,
    search: '',
    selectedRowIds: () => ({}),
    columnFilters: () => ({}),
  }
);

// Emits
const emit = defineEmits<{
  (e: 'update:page', value: number): void;
  (e: 'update:perPage', value: number): void;
  (e: 'update:sortField', value: string | null): void;
  (e: 'update:sortOrder', value: number | null): void;
  (e: 'update:search', value: string): void;
  (e: 'update:selectedRowIds', value: Record<string, boolean>): void;
  (e: 'update:columnFilters', value: Record<string, string>): void;
  (e: 'add-lead'): void;
  (e: 'edit-lead', lead: Lead): void;
  (e: 'delete-lead', lead: Lead): void;
  (e: 'bulk-delete', leadIds: number[]): void;
  (e: 'bulk-update', payload: { ids: number[]; field: string; value: any }): void;
  (e: 'toggle-favorite', lead: Lead): void;
}>();

// Column Visibility state
const columnVisibility = ref<VisibilityState>({
  id: false,
  is_favorite: true,
  name: true,
  email: true,
  phone: true,
  company: true,
  status: true,
  source: true,
  value: true,
  address: false,
  state: false,
  postal_code: false,
  industry: false,
  annual_revenue: false,
  number_of_employees: false,
  website: false,
  linkedin_url: false,
  lead_score: false,
  notes: false,
  actions: true,
});

const showFilters = ref(false);

const activeFiltersCount = computed(() => {
  return Object.values(props.columnFilters || {}).filter(val => val !== undefined && val !== null && val !== '').length;
});

onMounted(() => {
  if (activeFiltersCount.value > 0) {
    showFilters.value = true;
  }
});

const showVisibilityMenu = ref(false);
const visibilityBtn = ref<HTMLElement | null>(null);

const showBulkUpdateMenu = ref(false);
const bulkUpdateBtn = ref<HTMLElement | null>(null);

const toggleVisibilityMenu = () => {
  showVisibilityMenu.value = !showVisibilityMenu.value;
};

const closeVisibilityMenu = () => {
  showVisibilityMenu.value = false;
};

const closeBulkUpdateMenu = () => {
  showBulkUpdateMenu.value = false;
};

// Custom directive for clicking outside dropdowns
const vClickOutside = {
  mounted(el: any, binding: any) {
    el.clickOutsideEvent = (event: Event) => {
      const clickedEl = event.target as HTMLElement;
      if (el === clickedEl || el.contains(clickedEl)) {
        return;
      }
      if (visibilityBtn.value && (visibilityBtn.value === clickedEl || visibilityBtn.value.contains(clickedEl))) {
        return;
      }
      if (bulkUpdateBtn.value && (bulkUpdateBtn.value === clickedEl || bulkUpdateBtn.value.contains(clickedEl))) {
        return;
      }
      binding.value();
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el: any) {
    document.removeEventListener('click', el.clickOutsideEvent);
  },
};

// Column definition
const columnHelper = createColumnHelper<Lead>();

const columns = [
  // Selection check column
  columnHelper.display({
    id: 'select',
    size: 50,
  }),
  
  columnHelper.accessor('is_favorite', {
    id: 'is_favorite',
    header: 'Fav',
    size: 60,
    enableSorting: true,
  }),
  
  columnHelper.accessor('id', {
    id: 'id',
    header: 'ID',
    size: 80,
    enableSorting: true,
  }),

  // Name (combines first_name & last_name in custom cell rendering)
  columnHelper.display({
    id: 'name',
    header: 'Name',
    size: 240,
    enableSorting: true, // We will trigger sorting on first_name
  }),

  columnHelper.accessor('email', {
    id: 'email',
    header: 'Email',
    enableSorting: true,
  }),

  columnHelper.accessor('phone', {
    id: 'phone',
    header: 'Phone',
    enableSorting: true,
  }),

  columnHelper.accessor('company', {
    id: 'company',
    header: 'Company',
    enableSorting: true,
  }),

  columnHelper.accessor('status', {
    id: 'status',
    header: 'Status',
    size: 150,
    enableSorting: true,
  }),

  columnHelper.accessor('source', {
    id: 'source',
    header: 'Source',
    size: 150,
    enableSorting: false,
  }),

  columnHelper.accessor('value', {
    id: 'value',
    header: 'Value',
    size: 150,
    enableSorting: true,
  }),

  columnHelper.accessor('address', {
    id: 'address',
    header: 'Address',
    size: 200,
    enableSorting: true,
  }),

  columnHelper.accessor('state', {
    id: 'state',
    header: 'State',
    size: 120,
    enableSorting: true,
  }),

  columnHelper.accessor('postal_code', {
    id: 'postal_code',
    header: 'Postal Code',
    size: 120,
    enableSorting: true,
  }),

  columnHelper.accessor('industry', {
    id: 'industry',
    header: 'Industry',
    size: 150,
    enableSorting: true,
  }),

  columnHelper.accessor('annual_revenue', {
    id: 'annual_revenue',
    header: 'Annual Revenue',
    size: 180,
    enableSorting: true,
  }),

  columnHelper.accessor('number_of_employees', {
    id: 'number_of_employees',
    header: 'Employees',
    size: 130,
    enableSorting: true,
  }),

  columnHelper.accessor('website', {
    id: 'website',
    header: 'Website',
    size: 200,
    enableSorting: true,
  }),

  columnHelper.accessor('linkedin_url', {
    id: 'linkedin_url',
    header: 'LinkedIn',
    size: 200,
    enableSorting: true,
  }),

  columnHelper.accessor('lead_score', {
    id: 'lead_score',
    header: 'Lead Score',
    size: 130,
    enableSorting: true,
  }),

  columnHelper.accessor('notes', {
    id: 'notes',
    header: 'Notes',
    size: 250,
    enableSorting: true,
  }),

  columnHelper.display({
    id: 'actions',
    header: 'Actions',
    size: 120,
  }),
];

// Helper to handle manual mapping of Column Sorting to props
const tableSorting = computed<SortingState>(() => {
  if (!props.sortField) return [];
  // For 'name' column definition, we trigger sorting on 'first_name' field
  const fieldId = props.sortField === 'first_name' ? 'name' : props.sortField;
  return [{ id: fieldId, desc: props.sortOrder === -1 }];
});

const columnPinning = ref<ColumnPinningState>({
  left: ['select', 'is_favorite'],
  right: ['actions'],
});

const togglePinColumn = (columnId: string) => {
  const currentLeft = [...(columnPinning.value.left || [])];
  if (currentLeft.includes(columnId)) {
    columnPinning.value.left = currentLeft.filter(id => id !== columnId);
  } else {
    columnPinning.value.left = [...currentLeft, columnId];
  }
};

// Setup TanStack Table with manual (server-side) state management
const table = useVueTable({
  get data() { return props.data; },
  columns,
  state: {
    get sorting() { return tableSorting.value; },
    get pagination() {
      return { pageIndex: props.page, pageSize: props.perPage };
    },
    get columnVisibility() { return columnVisibility.value; },
    get rowSelection() { return props.selectedRowIds; },
    get columnPinning() { return columnPinning.value; },
  },
  onColumnPinningChange: (updaterOrValue) => {
    columnPinning.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(columnPinning.value)
      : updaterOrValue;
  },
  onSortingChange: (updaterOrValue: Updater<SortingState>) => {
    const newSorting = typeof updaterOrValue === 'function' 
      ? updaterOrValue(tableSorting.value) 
      : updaterOrValue;

    if (newSorting.length > 0) {
      const activeSort = newSorting[0];
      // Map back 'name' to 'first_name' for database querying
      const sortField = activeSort.id === 'name' ? 'first_name' : activeSort.id;
      emit('update:sortField', sortField);
      emit('update:sortOrder', activeSort.desc ? -1 : 1);
    } else {
      emit('update:sortField', null);
      emit('update:sortOrder', null);
    }
  },
  onPaginationChange: (updaterOrValue) => {
    const currentPagination = { pageIndex: props.page, pageSize: props.perPage };
    const newPagination = typeof updaterOrValue === 'function' 
      ? updaterOrValue(currentPagination) 
      : updaterOrValue;

    emit('update:page', newPagination.pageIndex);
    emit('update:perPage', newPagination.pageSize);
  },
  onRowSelectionChange: (updaterOrValue: Updater<RowSelectionState>) => {
    const newSelection = typeof updaterOrValue === 'function'
      ? updaterOrValue(props.selectedRowIds)
      : updaterOrValue;

    emit('update:selectedRowIds', newSelection);
  },
  onColumnVisibilityChange: (updaterOrValue: Updater<VisibilityState>) => {
    columnVisibility.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(columnVisibility.value)
      : updaterOrValue;
  },
  manualSorting: true,
  manualPagination: true,
  pageCount: computed(() => Math.ceil(props.totalRecords / props.perPage)).value,
  getCoreRowModel: getCoreRowModel(),
  getRowId: (row) => row.id.toString(), // identify rows by their database IDs
  enableRowSelection: true,
});

// Computed Range calculations
const rangeStart = computed(() => {
  if (props.totalRecords === 0) return 0;
  return props.page * props.perPage + 1;
});

const rangeEnd = computed(() => {
  return Math.min((props.page + 1) * props.perPage, props.totalRecords);
});

const pageCount = computed(() => Math.ceil(props.totalRecords / props.perPage));

// Generate pagination page numbers array with ellipsis
const pageNumbers = computed(() => {
  const current = props.page + 1;
  const total = pageCount.value;
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }

  const pages: (number | string)[] = [];
  pages.push(1);

  if (current > 3) {
    pages.push('...');
  }

  const start = Math.max(2, current - 1);
  const end = Math.min(total - 1, current + 1);

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  if (current < total - 2) {
    pages.push('...');
  }

  pages.push(total);
  return pages;
});

// Total selected rows count
const selectedCount = computed(() => {
  return Object.values(props.selectedRowIds).filter(Boolean).length;
});

// Functions
const onSearchInput = (e: Event) => {
  const target = e.target as HTMLInputElement;
  emit('update:search', target.value);
};

const onColumnFilterChange = (columnId: string, eventOrValue: Event | string) => {
  let value = '';
  if (typeof eventOrValue === 'string') {
    value = eventOrValue;
  } else if (eventOrValue && eventOrValue.target) {
    value = (eventOrValue.target as HTMLInputElement | HTMLSelectElement).value;
  }
  
  const updatedFilters = { ...props.columnFilters, [columnId]: value };
  if (!value) {
    delete updatedFilters[columnId];
  }
  emit('update:columnFilters', updatedFilters);
};

const clearSelection = () => {
  emit('update:selectedRowIds', {});
};

const emitBulkDelete = () => {
  const selectedIds = Object.keys(props.selectedRowIds)
    .filter(id => props.selectedRowIds[id])
    .map(Number);
  emit('bulk-delete', selectedIds);
};

const getToggleableColumns = () => {
  return table.getAllLeafColumns().filter(col => col.id !== 'select' && col.id !== 'actions');
};

const columnSearchQuery = ref('');

const CORE_FIELDS = ['first_name', 'last_name', 'email', 'phone', 'company', 'title', 'status', 'source', 'value'];

const groupedColumns = computed(() => {
  const query = columnSearchQuery.value.trim().toLowerCase();
  
  // Filter toggleable columns
  const allToggleable = getToggleableColumns();
  
  const filtered = allToggleable.filter(col => {
    if (!query) return true;
    const name = formatColumnHeader(col.id).toLowerCase();
    return name.includes(query);
  });
  
  const coreCols = filtered.filter(col => CORE_FIELDS.includes(col.id));
  const crmCols = filtered.filter(col => !CORE_FIELDS.includes(col.id));
  
  const groups = [];
  if (coreCols.length > 0) {
    groups.push({ name: 'Core Fields', columns: coreCols });
  }
  if (crmCols.length > 0) {
    groups.push({ name: 'CRM Fields', columns: crmCols });
  }
  return groups;
});

const showAllColumns = () => {
  table.getAllLeafColumns().forEach(col => {
    if (col.getCanHide()) {
      col.toggleVisibility(true);
    }
  });
};

const hideAllColumns = () => {
  table.getAllLeafColumns().forEach(col => {
    if (col.getCanHide()) {
      col.toggleVisibility(false);
    }
  });
};

const formatColumnHeader = (id: string) => {
  if (id === 'first_name') return 'First Name';
  if (id === 'last_name') return 'Last Name';
  return id.replace(/_/g, ' ');
};

const getAriaSort = (column: Column<Lead, unknown>) => {
  if (!column.getCanSort()) return undefined;
  const isSorted = column.getIsSorted();
  if (isSorted === 'asc') return 'ascending';
  if (isSorted === 'desc') return 'descending';
  return 'none';
};

const formatSourceLabel = (src: string) => {
  return src.replace(/_/g, ' ');
};

const formatCurrency = (val: number | string | null) => {
  if (val === null || val === undefined) return '-';
  const num = typeof val === 'string' ? parseFloat(val) : val;
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(num);
};

// Status Badge styling helper
const getStatusBadgeClass = (status: string) => {
  switch (status) {
    case 'new':
      return 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-400/20';
    case 'contacted':
      return 'bg-amber-50 text-amber-800 ring-1 ring-inset ring-amber-600/25 dark:bg-amber-950/20 dark:text-amber-400 dark:ring-amber-400/20';
    case 'qualified':
      return 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-950/20 dark:text-emerald-400 dark:ring-emerald-400/20';
    case 'lost':
      return 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/10 dark:bg-rose-950/20 dark:text-rose-400 dark:ring-rose-400/20';
    default:
      return 'bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/10 dark:bg-surface-600 dark:text-gray-300';
  }
};

// Lead Score styling helper
const getLeadScoreBadgeClass = (score: number) => {
  if (score >= 80) {
    return 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-950/20 dark:text-rose-400 dark:ring-rose-900/30';
  } else if (score >= 50) {
    return 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-950/20 dark:text-amber-400 dark:ring-amber-900/30';
  } else {
    return 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950/20 dark:text-emerald-400 dark:ring-emerald-900/30';
  }
};

const getLeadScoreIcon = (score: number) => {
  if (score >= 80) return 'pi pi-bolt text-[10px] animate-pulse';
  if (score >= 50) return 'pi pi-star text-[10px]';
  return 'pi pi-check text-[10px]';
};

const formatFullAddress = (lead: Lead) => {
  const parts = [];
  if (lead.address) parts.push(lead.address);
  
  const cityStateZip = [
    lead.city,
    lead.state,
    lead.postal_code
  ].filter(Boolean).join(', ');
  
  if (cityStateZip) parts.push(cityStateZip);
  if (lead.country) parts.push(lead.country);
  
  return parts.join('\n');
};

// Tooltip State
const tooltip = ref({
  show: false,
  visible: false,
  text: '',
  x: 0,
  y: 0,
});

let tooltipTimeout: number | null = null;

const showTooltip = (event: MouseEvent, text: string | null) => {
  if (!text) return;
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout);
    tooltipTimeout = null;
  }
  
  const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
  tooltip.value = {
    show: true,
    visible: false,
    text,
    x: rect.left + rect.width / 2,
    y: rect.top,
  };
  
  requestAnimationFrame(() => {
    tooltip.value.visible = true;
  });
};

const hideTooltip = () => {
  tooltip.value.visible = false;
  tooltipTimeout = window.setTimeout(() => {
    tooltip.value.show = false;
  }, 150);
};

const toggleFavoriteRow = (lead: Lead) => {
  emit('toggle-favorite', lead);
};

const onPerPageChange = (e: Event) => {
  const target = e.target as HTMLSelectElement;
  emit('update:perPage', parseInt(target.value));
};

const triggerBulkUpdate = (field: string, value: any) => {
  const selectedIds = Object.keys(props.selectedRowIds)
    .filter(id => props.selectedRowIds[id])
    .map(Number);
  emit('bulk-update', { ids: selectedIds, field, value });
  showBulkUpdateMenu.value = false;
};
</script>

<style scoped>
/* Pinned Columns Sticky Shadows and Hover Background Overrides */
th.sticky, td.sticky {
  background-clip: padding-box;
}

/* Subtle vertical border dividing pinned columns from scrolling ones */
th.sticky::after, td.sticky::after {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: 1px;
  background-color: rgba(0, 0, 0, 0.1);
}

:global(.dark) th.sticky::after, :global(.dark) td.sticky::after {
  background-color: rgba(255, 255, 255, 0.15);
}

/* Ensure sticky table rows maintain background highlights on hover */
tr:hover td.td-sticky {
  background-color: #f9fafb !important;
}

:global(.dark) tr:hover td.td-sticky {
  background-color: #27272a !important;
}

/* Row selection background override for sticky columns */
tr[aria-selected="true"] td.td-sticky {
  background-color: #f0fdf4 !important; /* light green for selected */
}

:global(.dark) tr[aria-selected="true"] td.td-sticky {
  background-color: rgba(16, 185, 129, 0.15) !important;
}

tr[aria-selected="true"]:hover td.td-sticky {
  background-color: #e6fcf0 !important;
}

:global(.dark) tr[aria-selected="true"]:hover td.td-sticky {
  background-color: rgba(16, 185, 129, 0.2) !important;
}

/* Custom scrollbar to keep scrollbars looking premium */
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #475569;
}
</style>

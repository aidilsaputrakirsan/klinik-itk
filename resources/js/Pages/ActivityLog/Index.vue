<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from 'primevue/card';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { ref, watch } from 'vue';

interface ActivityLog {
    id: number;
    user_id: number | null;
    action: string;
    model_type: string;
    model_name: string;
    model_id: number | null;
    old_values: Record<string, any> | null;
    new_values: Record<string, any> | null;
    ip_address: string | null;
    created_at: string;
    user: {
        id: number;
        name: string;
    } | null;
}

interface Props {
    logs: {
        data: ActivityLog[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        action?: string;
        model_type?: string;
        start_date?: string;
        end_date?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    action: props.filters.action || '',
    model_type: props.filters.model_type || '',
    start_date: props.filters.start_date ? new Date(props.filters.start_date) : null,
    end_date: props.filters.end_date ? new Date(props.filters.end_date) : null,
});

const actionOptions = [
    { label: 'Semua Aksi', value: '' },
    { label: 'Membuat', value: 'created' },
    { label: 'Mengubah', value: 'updated' },
    { label: 'Menghapus', value: 'deleted' },
];

const modelOptions = [
    { label: 'Semua Model', value: '' },
    { label: 'Rekam Medis', value: 'RekamMedis' },
    { label: 'Pasien', value: 'Pasien' },
    { label: 'Pemeriksaan', value: 'Pemeriksaan' },
    { label: 'Anamnesis', value: 'Anamnesis' },
];

const getActionSeverity = (action: string) => {
    switch (action) {
        case 'created':
            return 'success';
        case 'updated':
            return 'info';
        case 'deleted':
            return 'danger';
        default:
            return 'secondary';
    }
};

const getActionLabel = (action: string) => {
    switch (action) {
        case 'created':
            return 'Membuat';
        case 'updated':
            return 'Mengubah';
        case 'deleted':
            return 'Menghapus';
        default:
            return action;
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const applyFilters = () => {
    router.get(route('activity-logs.index'), {
        action: filters.value.action || undefined,
        model_type: filters.value.model_type || undefined,
        start_date: filters.value.start_date
            ? filters.value.start_date.toISOString().split('T')[0]
            : undefined,
        end_date: filters.value.end_date
            ? filters.value.end_date.toISOString().split('T')[0]
            : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    filters.value = {
        action: '',
        model_type: '',
        start_date: null,
        end_date: null,
    };
    router.get(route('activity-logs.index'));
};

const onPage = (event: any) => {
    router.get(route('activity-logs.index'), {
        ...props.filters,
        page: event.page + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Log Aktivitas" />
    <AppLayout>
        <template #header>
            <div class="font-sans">
                <h2 class="text-xl font-bold text-gray-800 leading-tight">Log Aktivitas Sistem</h2>
                <p class="text-sm text-gray-500 mt-1">Pantau semua aktivitas yang terjadi dalam sistem</p>
            </div>
        </template>

        <div class="space-y-6 font-sans mt-4">
            <!-- Filters -->
            <Card class="shadow-sm border border-gray-100">
                <template #content>
                    <div class="flex flex-wrap md:flex-nowrap items-end gap-4">
                        <div class="flex flex-col gap-1.5 flex-1 min-w-[150px]">
                            <label class="text-xs font-semibold text-gray-700">Aksi</label>
                            <Select
                                v-model="filters.action"
                                :options="actionOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Semua Aksi"
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col gap-1.5 flex-1 min-w-[150px]">
                            <label class="text-xs font-semibold text-gray-700">Model</label>
                            <Select
                                v-model="filters.model_type"
                                :options="modelOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Semua Model"
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col gap-1.5 flex-1 min-w-[150px]">
                            <label class="text-xs font-semibold text-gray-700">Dari Tanggal</label>
                            <DatePicker
                                v-model="filters.start_date"
                                dateFormat="dd/mm/yy"
                                placeholder="Pilih tanggal"
                                showIcon
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col gap-1.5 flex-1 min-w-[150px]">
                            <label class="text-xs font-semibold text-gray-700">Sampai Tanggal</label>
                            <DatePicker
                                v-model="filters.end_date"
                                dateFormat="dd/mm/yy"
                                placeholder="Pilih tanggal"
                                showIcon
                                class="w-full"
                            />
                        </div>
                        <div class="flex items-end gap-2">
                            <Button
                                label="Filter"
                                icon="pi pi-search"
                                @click="applyFilters"
                                class="bg-emerald-600 hover:bg-emerald-700 border-emerald-600 hover:border-emerald-700 text-white h-[42px] px-4 font-semibold"
                            />
                            <Button
                                label="Reset"
                                icon="pi pi-refresh"
                                @click="resetFilters"
                                class="bg-emerald-600 hover:bg-emerald-700 border-emerald-600 hover:border-emerald-700 text-white h-[42px] px-4 font-semibold"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Data Table -->
            <Card class="shadow-sm border border-gray-100">
                <template #content>
                    <DataTable
                        :value="logs.data"
                        :paginator="true"
                        :rows="logs.per_page"
                        :totalRecords="logs.total"
                        :lazy="true"
                        @page="onPage"
                        :first="(logs.current_page - 1) * logs.per_page"
                        stripedRows
                        tableStyle="min-width: 50rem"
                    >
                        <Column field="created_at" header="Waktu" style="width: 180px">
                            <template #body="{ data }">
                                <span class="text-sm">{{ formatDate(data.created_at) }}</span>
                            </template>
                        </Column>
                        <Column field="user" header="User" style="width: 150px">
                            <template #body="{ data }">
                                <span>{{ data.user?.name || 'System' }}</span>
                            </template>
                        </Column>
                        <Column field="action" header="Aksi" style="width: 140px">
                            <template #body="{ data }">
                                <div v-if="data.action === 'created'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-600 text-xs font-semibold border border-emerald-100">
                                    <i class="pi pi-plus-circle text-[10px]"></i>
                                    <span>Membuat</span>
                                </div>
                                <div v-else-if="data.action === 'updated'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-600 text-xs font-semibold border border-emerald-100">
                                    <i class="pi pi-pencil text-[10px]"></i>
                                    <span>Mengubah</span>
                                </div>
                                <div v-else-if="data.action === 'deleted'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-rose-50 text-rose-600 text-xs font-semibold border border-rose-100">
                                    <i class="pi pi-trash text-[10px]"></i>
                                    <span>Menghapus</span>
                                </div>
                                <div v-else class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-50 text-gray-600 text-xs font-semibold border border-gray-100">
                                    <span>{{ data.action }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="model_name" header="Model" style="width: 150px">
                            <template #body="{ data }">
                                <span class="font-medium">{{ data.model_name }}</span>
                            </template>
                        </Column>
                        <Column field="model_id" header="ID" style="width: 80px">
                            <template #body="{ data }">
                                <span class="text-gray-500">#{{ data.model_id }}</span>
                            </template>
                        </Column>
                        <template #empty>
                            <div class="text-center py-8 text-gray-500">
                                <i class="pi pi-inbox text-4xl mb-2"></i>
                                <p>Tidak ada log aktivitas ditemukan</p>
                            </div>
                        </template>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

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
        <template #header>Log Aktivitas Sistem</template>

        <div class="space-y-4">
            <!-- Filters -->
            <Card>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-600">Aksi</label>
                            <Select
                                v-model="filters.action"
                                :options="actionOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Semua Aksi"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-600">Model</label>
                            <Select
                                v-model="filters.model_type"
                                :options="modelOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Semua Model"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-600">Dari Tanggal</label>
                            <DatePicker
                                v-model="filters.start_date"
                                dateFormat="dd/mm/yy"
                                placeholder="Pilih tanggal"
                                showIcon
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-gray-600">Sampai Tanggal</label>
                            <DatePicker
                                v-model="filters.end_date"
                                dateFormat="dd/mm/yy"
                                placeholder="Pilih tanggal"
                                showIcon
                            />
                        </div>
                        <div class="flex items-end gap-2">
                            <Button
                                label="Filter"
                                icon="pi pi-search"
                                @click="applyFilters"
                            />
                            <Button
                                label="Reset"
                                icon="pi pi-refresh"
                                severity="secondary"
                                @click="resetFilters"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Data Table -->
            <Card>
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
                        <Column field="action" header="Aksi" style="width: 120px">
                            <template #body="{ data }">
                                <Tag
                                    :value="getActionLabel(data.action)"
                                    :severity="getActionSeverity(data.action)"
                                />
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
                        <Column field="ip_address" header="IP Address" style="width: 130px">
                            <template #body="{ data }">
                                <span class="text-sm text-gray-500">{{ data.ip_address || '-' }}</span>
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

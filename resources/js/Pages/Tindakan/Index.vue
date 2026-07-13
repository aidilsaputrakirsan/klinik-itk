<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Tindakan } from '@/types';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dialog from 'primevue/dialog';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import { useToast } from 'primevue/usetoast';
import Swal from 'sweetalert2';

interface Props {
    tindakans: {
        data: Tindakan[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();
const toast = useToast();

const search = ref(props.filters.search || '');
const filterStatus = ref<string | null>(null);
const showDialog = ref(false);
const isEdit = ref(false);
const selectedTindakan = ref<Tindakan | null>(null);

const statusOptions = [
    { label: 'Semua Status', value: null },
    { label: 'Aktif', value: '1' },
    { label: 'Nonaktif', value: '0' },
];

const filteredTindakans = computed(() => {
    let result = props.tindakans.data;
    
    if (search.value) {
        const s = search.value.toLowerCase();
        result = result.filter(t => 
            t.nama.toLowerCase().includes(s) || 
            t.kode.toLowerCase().includes(s)
        );
    }
    
    if (filterStatus.value !== null) {
        const isActive = filterStatus.value === '1';
        result = result.filter(t => t.is_active === isActive);
    }
    
    return result;
});

const summaryStats = computed(() => {
    const total = props.tindakans.data.length;
    const active = props.tindakans.data.filter(t => t.is_active).length;
    return { total, active };
});

const form = ref({
    kode: '',
    nama: '',
    deskripsi: '',
    biaya: 0,
    is_active: true,
});

const doSearch = () => {
    router.get(route('tindakan.index'), { search: search.value }, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    filterStatus.value = null;
    doSearch();
};

const openCreateDialog = () => {
    isEdit.value = false;
    resetForm();
    showDialog.value = true;
};

const openEditDialog = (tindakan: Tindakan) => {
    isEdit.value = true;
    selectedTindakan.value = tindakan;
    form.value = {
        kode: tindakan.kode,
        nama: tindakan.nama,
        deskripsi: tindakan.deskripsi || '',
        biaya: tindakan.biaya,
        is_active: tindakan.is_active,
    };
    showDialog.value = true;
};

const resetForm = () => {
    form.value = {
        kode: '',
        nama: '',
        deskripsi: '',
        biaya: 0,
        is_active: true,
    };
    selectedTindakan.value = null;
};

const closeDialog = () => {
    showDialog.value = false;
    resetForm();
};

const submitForm = () => {
    if (isEdit.value && selectedTindakan.value) {
        router.put(route('tindakan.update', selectedTindakan.value.id), form.value, {
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Tindakan berhasil diperbarui', life: 3000 });
                closeDialog();
            }
        });
    } else {
        router.post(route('tindakan.store'), form.value, {
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Tindakan berhasil ditambahkan', life: 3000 });
                closeDialog();
            }
        });
    }
};

const deleteTindakan = (tindakan: Tindakan) => {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus tindakan "${tindakan.nama}"?`,
        icon: 'warning',
        iconColor: '#f43f5e',
        showCancelButton: true,
        buttonsStyling: false,
        background: '#ffffff',
        customClass: {
            popup: 'rounded-3xl shadow-2xl border border-gray-100',
            title: 'text-2xl font-bold !text-rose-700',
            htmlContainer: 'text-gray-500 text-sm mt-2',
            actions: 'flex gap-3 mt-6',
            confirmButton: '!bg-gradient-to-r !from-rose-500 !to-red-600 hover:!from-rose-600 hover:!to-red-700 !text-white rounded-xl px-6 py-2.5 font-semibold transition-all shadow-md hover:shadow-lg',
            cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl px-6 py-2.5 font-semibold transition-all border border-gray-200'
        },
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('tindakan.destroy', tindakan.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Tindakan berhasil dihapus', life: 3000 });
                }
            });
        }
    });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
};
</script>

<template>
    <Head title="Master Tindakan" />
    <AppLayout>
        <template #header>Master Tindakan</template>

        <div class="space-y-6">
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="pi pi-list text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Tindakan</p>
                        <p class="text-2xl font-bold text-gray-800">{{ summaryStats.total }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="pi pi-check-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Tindakan Aktif</p>
                        <p class="text-2xl font-bold text-gray-800">{{ summaryStats.active }}</p>
                    </div>
                </div>
            </div>

            <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                <template #content>
                    <!-- Filter & Actions -->
                    <div class="mb-6 space-y-4">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                                Daftar Layanan Tindakan Medis
                            </h3>
                            <Button 
                                label="Tambah Tindakan" 
                                icon="pi pi-plus" 
                                severity="success" 
                                class="!rounded-xl !text-xs font-bold shadow-sm"
                                @click="openCreateDialog" 
                            />
                        </div>

                        <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-xl shadow-sm space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
                                <!-- Field: Cari Tindakan -->
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Tindakan</span>
                                    <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                                        <InputGroupAddon class="!bg-white !border-0 !px-3">
                                            <i class="pi pi-search text-emerald-500 text-[10px]"></i>
                                        </InputGroupAddon>
                                        <InputText
                                            v-model="search"
                                            placeholder="Kode atau nama tindakan..."
                                            class="!border-0 !text-xs !py-2 !pl-0 focus:!ring-0 placeholder:text-gray-300"
                                            @keyup.enter="doSearch"
                                        />
                                    </InputGroup>
                                </div>

                                <!-- Field: Status -->
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Status</span>
                                    <div class="flex gap-2 items-center">
                                        <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all flex-1">
                                            <InputGroupAddon class="!bg-white !border-0 !px-3">
                                                <i class="pi pi-check-circle text-emerald-500 text-[10px]"></i>
                                            </InputGroupAddon>
                                            <Select
                                                v-model="filterStatus"
                                                :options="statusOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih Status"
                                                class="!border-0 !text-xs !py-0 focus:!ring-0 flex-1"
                                                :pt="{
                                                    root: { class: '!border-0 !shadow-none' },
                                                    label: { class: '!text-xs !py-2 !pl-0' },
                                                    dropdownIcon: { class: '!w-3 !h-3 text-emerald-500' }
                                                }"
                                            />
                                        </InputGroup>
                                        
                                        <Button 
                                            icon="pi pi-filter-slash" 
                                            severity="secondary" 
                                            text 
                                            v-tooltip.top="'Bersihkan Filter'"
                                            class="!rounded-xl !p-2 !h-9 !w-9 flex items-center justify-center border border-gray-200 hover:bg-gray-100 bg-white shrink-0"
                                            @click="clearFilters" 
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <DataTable 
                        :value="filteredTindakans" 
                        dataKey="id" 
                        responsiveLayout="scroll" 
                        class="p-datatable-sm"
                        stripedRows
                        :paginator="filteredTindakans.length > 10"
                        :rows="10"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        currentPageReportTemplate="Menampilkan {first} sampai {last} dari {totalRecords} tindakan"
                    >
                        <Column field="kode" header="Kode" style="width: 120px" sortable>
                            <template #body="{ data }">
                                <span class="font-mono text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-1 rounded border border-blue-100">
                                    {{ data.kode }}
                                </span>
                            </template>
                        </Column>
                        <Column field="nama" header="Nama Tindakan" sortable>
                            <template #body="{ data }">
                                <span class="font-medium text-gray-800">{{ data.nama }}</span>
                            </template>
                        </Column>
                        <Column field="biaya" header="Biaya" style="width: 160px" sortable>
                            <template #body="{ data }">
                                <span class="text-gray-700 font-bold">{{ formatCurrency(data.biaya) }}</span>
                            </template>
                        </Column>
                        <Column field="deskripsi" header="Deskripsi">
                            <template #body="{ data }">
                                <span class="text-gray-500 text-xs italic">{{ data.deskripsi || '-' }}</span>
                            </template>
                        </Column>
                        <Column field="is_active" header="Status" style="width: 100px" sortable>
                            <template #body="{ data }">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full" :class="data.is_active ? 'bg-green-500' : 'bg-gray-400'"></span>
                                    <span class="text-xs font-medium" :class="data.is_active ? 'text-green-700' : 'text-gray-500'">
                                        {{ data.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 120px" class="text-center">
                            <template #body="{ data }">
                                <div class="flex items-center justify-center gap-1">
                                    <Button 
                                        icon="pi pi-pencil" 
                                        severity="warn" 
                                        text 
                                        rounded 
                                        v-tooltip.top="'Edit Tindakan'"
                                        @click="openEditDialog(data)" 
                                    />
                                    <Button 
                                        icon="pi pi-trash" 
                                        severity="danger" 
                                        text 
                                        rounded 
                                        v-tooltip.top="'Hapus Tindakan'"
                                        @click="deleteTindakan(data)" 
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Dialog Form -->
        <Dialog
            v-model:visible="showDialog"
            modal
            :header="isEdit ? 'Edit Tindakan' : 'Tambah Tindakan Baru'"
            :style="{ width: '500px' }"
        >
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Kode Tindakan <span class="text-red-500">*</span></label>
                    <InputText v-model="form.kode" placeholder="Contoh: TDK0001" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Nama Tindakan <span class="text-red-500">*</span></label>
                    <InputText v-model="form.nama" placeholder="Nama tindakan" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Biaya <span class="text-red-500">*</span></label>
                    <InputNumber v-model="form.biaya" :min="0" mode="currency" currency="IDR" locale="id-ID" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Deskripsi</label>
                    <Textarea v-model="form.deskripsi" rows="2" placeholder="Deskripsi tindakan" />
                </div>
                <div class="flex flex-col gap-2" v-if="isEdit">
                    <label class="font-medium text-sm">Status <span class="text-red-500">*</span></label>
                    <Select
                        v-model="form.is_active"
                        :options="[{label: 'Aktif', value: true}, {label: 'Nonaktif', value: false}]"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Pilih Status"
                        class="w-full"
                    />
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" />
                <Button :label="isEdit ? 'Simpan' : 'Tambah'" icon="pi pi-check" @click="submitForm" />
            </template>
        </Dialog>
    </AppLayout>
</template>

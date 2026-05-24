<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Obat } from '@/types';
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
import { useToast } from 'primevue/usetoast';
import Swal from 'sweetalert2';

interface Props {
    obats: {
        data: Obat[];
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
const filterJenis = ref('');
const filterStatus = ref<string | null>(null);

const showDialog = ref(false);
const isEdit = ref(false);
const selectedObat = ref<Obat | null>(null);

const commonJenis = [
    { label: 'Semua Jenis', value: '' },
    { label: 'Tablet', value: 'tablet' },
    { label: 'Kapsul', value: 'kapsul' },
    { label: 'Sirup', value: 'sirup' },
    { label: 'Salep', value: 'salep' },
    { label: 'Injeksi', value: 'injeksi' },
    { label: 'Lainnya', value: 'lainnya' },
];

const statusOptions = [
    { label: 'Semua Status', value: null },
    { label: 'Aktif', value: '1' },
    { label: 'Nonaktif', value: '0' },
];

const filteredObats = computed(() => {
    let result = props.obats.data;
    
    if (search.value) {
        const s = search.value.toLowerCase();
        result = result.filter(o => 
            o.nama.toLowerCase().includes(s) || 
            o.kode.toLowerCase().includes(s)
        );
    }
    
    if (filterJenis.value) {
        result = result.filter(o => o.jenis?.toLowerCase() === filterJenis.value);
    }
    
    if (filterStatus.value !== null) {
        const isActive = filterStatus.value === '1';
        result = result.filter(o => o.is_active === isActive);
    }
    
    return result;
});

const summaryStats = computed(() => {
    const total = props.obats.data.length;
    const lowStock = props.obats.data.filter(o => o.stok <= (o.stok_minimum || 10)).length;
    const active = props.obats.data.filter(o => o.is_active).length;
    return { total, lowStock, active };
});

const form = ref({
    kode: '',
    nama: '',
    satuan: '',
    jenis: '',
    stok: 0,
    stok_minimum: 10,
    harga: 0,
    keterangan: '',
    is_active: true,
});

const doSearch = () => {
    router.get(route('obat.index'), { search: search.value }, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    filterJenis.value = '';
    filterStatus.value = null;
    doSearch();
};

const openCreateDialog = () => {
    isEdit.value = false;
    resetForm();
    showDialog.value = true;
};

const openEditDialog = (obat: Obat) => {
    isEdit.value = true;
    selectedObat.value = obat;
    form.value = {
        kode: obat.kode,
        nama: obat.nama,
        satuan: obat.satuan,
        jenis: obat.jenis || '',
        stok: obat.stok,
        stok_minimum: obat.stok_minimum || 10,
        harga: obat.harga,
        keterangan: obat.keterangan || '',
        is_active: obat.is_active,
    };
    showDialog.value = true;
};

const resetForm = () => {
    form.value = {
        kode: '',
        nama: '',
        satuan: '',
        jenis: '',
        stok: 0,
        stok_minimum: 10,
        harga: 0,
        keterangan: '',
        is_active: true,
    };
    selectedObat.value = null;
};

const closeDialog = () => {
    showDialog.value = false;
    resetForm();
};

const submitForm = () => {
    if (isEdit.value && selectedObat.value) {
        router.put(route('obat.update', selectedObat.value.id), form.value, {
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Obat berhasil diperbarui', life: 3000 });
                closeDialog();
            }
        });
    } else {
        router.post(route('obat.store'), form.value, {
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Obat berhasil ditambahkan', life: 3000 });
                closeDialog();
            }
        });
    }
};

const deleteObat = (obat: Obat) => {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus obat "${obat.nama}"?`,
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
            router.delete(route('obat.destroy', obat.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Obat berhasil dihapus', life: 3000 });
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
    <Head title="Master Obat" />
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <i class="pi pi-box text-emerald-600 text-xl"></i>
                <span>Master Obat</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i class="pi pi-database text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Obat</p>
                        <p class="text-2xl font-bold text-gray-800">{{ summaryStats.total }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-600">
                        <i class="pi pi-exclamation-triangle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Stok Rendah</p>
                        <p class="text-2xl font-bold text-gray-800">{{ summaryStats.lowStock }}</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="pi pi-check-circle text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Obat Aktif</p>
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
                                Daftar Inventaris Obat
                            </h3>
                            <Button 
                                label="Tambah Obat Baru" 
                                icon="pi pi-plus" 
                                severity="success" 
                                class="!rounded-xl !text-xs font-bold shadow-sm"
                                @click="openCreateDialog" 
                            />
                        </div>

                        <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full shadow-sm space-y-4">
                            <div class="flex flex-wrap items-end gap-3">
                                <div class="flex flex-col gap-1.5 min-w-[250px]">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Obat</span>
                                    <div class="w-full">
                                        <InputText
                                            v-model="search"
                                            placeholder="Kode atau nama obat..."
                                            class="!w-full !border-gray-200 !rounded-xl !text-xs shadow-sm focus:!ring-emerald-500/20"
                                            @keyup.enter="doSearch"
                                        />
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1.5 min-w-[150px]">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Jenis</span>
                                    <Select
                                        v-model="filterJenis"
                                        :options="commonJenis"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Pilih Jenis"
                                        class="!border-gray-200 !rounded-xl !text-xs w-full shadow-sm"
                                    />
                                </div>

                                <div class="flex flex-col gap-1.5 min-w-[150px]">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Status</span>
                                    <Select
                                        v-model="filterStatus"
                                        :options="statusOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Pilih Status"
                                        class="!border-gray-200 !rounded-xl !text-xs w-full shadow-sm"
                                    />
                                </div>

                                <Button 
                                    icon="pi pi-filter-slash" 
                                    severity="secondary" 
                                    text 
                                    v-tooltip.top="'Bersihkan Filter'"
                                    class="!rounded-xl"
                                    @click="clearFilters" 
                                />
                            </div>
                        </div>
                    </div>

                    <DataTable 
                        :value="filteredObats" 
                        dataKey="id" 
                        responsiveLayout="scroll" 
                        class="p-datatable-sm"
                        stripedRows
                        :paginator="filteredObats.length > 10"
                        :rows="10"
                        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                        currentPageReportTemplate="Menampilkan {first} sampai {last} dari {totalRecords} obat"
                    >
                        <Column field="kode" header="Kode" style="width: 120px" sortable>
                            <template #body="{ data }">
                                <span class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">
                                    {{ data.kode }}
                                </span>
                            </template>
                        </Column>
                        <Column field="nama" header="Nama Obat" sortable>
                            <template #body="{ data }">
                                <span class="font-medium text-gray-800">{{ data.nama }}</span>
                            </template>
                        </Column>
                        <Column field="jenis" header="Jenis" style="width: 120px" sortable>
                            <template #body="{ data }">
                                <Tag :value="data.jenis || '-'" severity="secondary" class="!text-[10px] !px-2" />
                            </template>
                        </Column>
                        <Column field="satuan" header="Satuan" style="width: 100px" />
                        <Column field="stok" header="Stok" style="width: 100px" sortable>
                            <template #body="{ data }">
                                <div class="flex flex-col gap-1">
                                    <Tag 
                                        :value="data.stok.toString()" 
                                        :severity="data.stok <= (data.stok_minimum || 10) ? 'danger' : data.stok < 50 ? 'warn' : 'success'" 
                                        class="!rounded-lg"
                                    />
                                    <span v-if="data.stok <= (data.stok_minimum || 10)" class="text-[9px] text-red-500 font-bold italic">Stok Rendah!</span>
                                </div>
                            </template>
                        </Column>
                        <Column field="harga" header="Harga" style="width: 140px" sortable>
                            <template #body="{ data }">
                                <span class="text-gray-700 font-semibold">{{ formatCurrency(data.harga) }}</span>
                            </template>
                        </Column>
                        <Column field="keterangan" header="Keterangan" style="width: 200px">
                            <template #body="{ data }">
                                <span class="text-xs text-gray-500 italic">{{ data.keterangan || '-' }}</span>
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
                                        v-tooltip.top="'Edit Obat'"
                                        @click="openEditDialog(data)" 
                                    />
                                    <Button 
                                        icon="pi pi-trash" 
                                        severity="danger" 
                                        text 
                                        rounded 
                                        v-tooltip.top="'Hapus Obat'"
                                        @click="deleteObat(data)" 
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
            :header="isEdit ? 'Edit Obat' : 'Tambah Obat Baru'"
            :style="{ width: '500px' }"
        >
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Kode Obat <span class="text-red-500">*</span></label>
                    <InputText v-model="form.kode" placeholder="Contoh: OBT0001" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Nama Obat <span class="text-red-500">*</span></label>
                    <InputText v-model="form.nama" placeholder="Nama obat" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Jenis</label>
                        <InputText v-model="form.jenis" placeholder="Tablet, Sirup, Salep, dll" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Satuan <span class="text-red-500">*</span></label>
                        <InputText v-model="form.satuan" placeholder="Tablet, Kapsul, Botol, dll" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Stok <span class="text-red-500">*</span></label>
                        <InputNumber v-model="form.stok" :min="0" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Stok Minimum</label>
                        <InputNumber v-model="form.stok_minimum" :min="0" placeholder="10" />
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Harga <span class="text-red-500">*</span></label>
                    <InputNumber v-model="form.harga" :min="0" mode="currency" currency="IDR" locale="id-ID" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Keterangan</label>
                    <Textarea v-model="form.keterangan" rows="2" placeholder="Keterangan tambahan" />
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

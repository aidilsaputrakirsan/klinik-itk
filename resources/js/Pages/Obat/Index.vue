<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Obat } from '@/types';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

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
const confirm = useConfirm();
const toast = useToast();

const search = ref(props.filters.search || '');
const showDialog = ref(false);
const isEdit = ref(false);
const selectedObat = ref<Obat | null>(null);

const form = ref({
    kode_obat: '',
    nama_obat: '',
    satuan: '',
    stok: 0,
    harga: 0,
    keterangan: '',
    is_active: true,
});

const doSearch = () => {
    router.get(route('obat.index'), { search: search.value }, { preserveState: true });
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
        kode_obat: obat.kode_obat,
        nama_obat: obat.nama_obat,
        satuan: obat.satuan,
        stok: obat.stok,
        harga: obat.harga,
        keterangan: obat.keterangan || '',
        is_active: obat.is_active,
    };
    showDialog.value = true;
};

const resetForm = () => {
    form.value = {
        kode_obat: '',
        nama_obat: '',
        satuan: '',
        stok: 0,
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
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus obat "${obat.nama_obat}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
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
        <template #header>Master Obat</template>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="p-input-icon-left w-full sm:w-80">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="search"
                            placeholder="Cari kode atau nama obat..."
                            class="w-full"
                            @keyup.enter="doSearch"
                        />
                    </span>
                    <Button icon="pi pi-search" @click="doSearch" />
                </div>
                <Button label="Tambah Obat" icon="pi pi-plus" @click="openCreateDialog" />
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <DataTable :value="obats.data" dataKey="id" responsiveLayout="scroll" class="p-datatable-sm">
                    <Column field="kode_obat" header="Kode" style="width: 100px" />
                    <Column field="nama_obat" header="Nama Obat" />
                    <Column field="satuan" header="Satuan" style="width: 100px" />
                    <Column field="stok" header="Stok" style="width: 100px">
                        <template #body="{ data }">
                            <Tag :value="data.stok.toString()" :severity="data.stok < 10 ? 'danger' : data.stok < 50 ? 'warn' : 'success'" />
                        </template>
                    </Column>
                    <Column field="harga" header="Harga" style="width: 140px">
                        <template #body="{ data }">
                            {{ formatCurrency(data.harga) }}
                        </template>
                    </Column>
                    <Column field="is_active" header="Status" style="width: 100px">
                        <template #body="{ data }">
                            <Tag :value="data.is_active ? 'Aktif' : 'Nonaktif'" :severity="data.is_active ? 'success' : 'secondary'" />
                        </template>
                    </Column>
                    <Column header="Aksi" style="width: 120px">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button icon="pi pi-pencil" severity="warn" text rounded size="small" @click="openEditDialog(data)" />
                                <Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="deleteObat(data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
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
                    <InputText v-model="form.kode_obat" placeholder="Contoh: OBT001" />
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Nama Obat <span class="text-red-500">*</span></label>
                    <InputText v-model="form.nama_obat" placeholder="Nama obat" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Satuan <span class="text-red-500">*</span></label>
                        <InputText v-model="form.satuan" placeholder="Tablet, Kapsul, dll" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Stok <span class="text-red-500">*</span></label>
                        <InputNumber v-model="form.stok" :min="0" />
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
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" />
                <Button :label="isEdit ? 'Simpan' : 'Tambah'" icon="pi pi-check" @click="submitForm" />
            </template>
        </Dialog>
    </AppLayout>
</template>

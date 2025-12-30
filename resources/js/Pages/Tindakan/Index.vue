<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Tindakan } from '@/types';
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
const confirm = useConfirm();
const toast = useToast();

const search = ref(props.filters.search || '');
const showDialog = ref(false);
const isEdit = ref(false);
const selectedTindakan = ref<Tindakan | null>(null);

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
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus tindakan "${tindakan.nama}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
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

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="p-input-icon-left w-full sm:w-80">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="search"
                            placeholder="Cari kode atau nama tindakan..."
                            class="w-full"
                            @keyup.enter="doSearch"
                        />
                    </span>
                    <Button icon="pi pi-search" @click="doSearch" />
                </div>
                <Button label="Tambah Tindakan" icon="pi pi-plus" @click="openCreateDialog" />
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <DataTable :value="tindakans.data" dataKey="id" responsiveLayout="scroll" class="p-datatable-sm">
                    <Column field="kode" header="Kode" style="width: 120px" />
                    <Column field="nama" header="Nama Tindakan" />
                    <Column field="biaya" header="Biaya" style="width: 160px">
                        <template #body="{ data }">
                            {{ formatCurrency(data.biaya) }}
                        </template>
                    </Column>
                    <Column field="deskripsi" header="Deskripsi">
                        <template #body="{ data }">
                            <span class="text-gray-600">{{ data.deskripsi || '-' }}</span>
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
                                <Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="deleteTindakan(data)" />
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
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" />
                <Button :label="isEdit ? 'Simpan' : 'Tambah'" icon="pi pi-check" @click="submitForm" />
            </template>
        </Dialog>
    </AppLayout>
</template>

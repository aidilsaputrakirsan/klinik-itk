<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Pasien } from '@/types';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

interface Props {
    pasiens: {
        data: Pasien[];
        links: object[];
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

const doSearch = () => {
    router.get(route('pasien.index'), { search: search.value }, { preserveState: true });
};

const getStatusSeverity = (status: string) => {
    const severities: Record<string, string> = {
        mahasiswa: 'info',
        dosen: 'success',
        tendik: 'warn',
        umum: 'secondary'
    };
    return severities[status] || 'secondary';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        mahasiswa: 'Mahasiswa',
        dosen: 'Dosen',
        tendik: 'Tendik',
        umum: 'Umum'
    };
    return labels[status] || status;
};

const getGenderLabel = (gender: string) => {
    return gender === 'L' ? 'Laki-laki' : 'Perempuan';
};

const deletePasien = (pasien: Pasien) => {
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus pasien "${pasien.nama}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('pasien.destroy', pasien.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pasien berhasil dihapus', life: 3000 });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Daftar Pasien" />
    <AppLayout>
        <template #header>Daftar Pasien</template>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="p-input-icon-left w-full sm:w-80">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="search"
                            placeholder="Cari nama, NIK, atau No. RM..."
                            class="w-full"
                            @keyup.enter="doSearch"
                        />
                    </span>
                    <Button icon="pi pi-search" @click="doSearch" />
                </div>
                <Link :href="route('pasien.create')">
                    <Button label="Tambah Pasien" icon="pi pi-plus" />
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <DataTable
                    :value="pasiens.data"
                    :paginator="false"
                    :rows="pasiens.per_page"
                    dataKey="id"
                    responsiveLayout="scroll"
                    class="p-datatable-sm"
                >
                    <Column field="nomor_rm" header="No. RM" sortable style="width: 120px" />
                    <Column field="nama" header="Nama Pasien" sortable>
                        <template #body="{ data }">
                            <div>
                                <p class="font-medium text-gray-900">{{ data.nama }}</p>
                                <p class="text-xs text-gray-500">{{ data.nik }}</p>
                            </div>
                        </template>
                    </Column>
                    <Column field="jenis_kelamin" header="L/P" style="width: 80px">
                        <template #body="{ data }">
                            {{ data.jenis_kelamin }}
                        </template>
                    </Column>
                    <Column field="tanggal_lahir" header="Tgl. Lahir" sortable style="width: 120px">
                        <template #body="{ data }">
                            {{ new Date(data.tanggal_lahir).toLocaleDateString('id-ID') }}
                        </template>
                    </Column>
                    <Column field="tipe_pasien" header="Status" style="width: 120px">
                        <template #body="{ data }">
                            <Tag :value="getStatusLabel(data.tipe_pasien)" :severity="getStatusSeverity(data.tipe_pasien)" />
                        </template>
                    </Column>
                    <Column field="phone" header="Telepon" style="width: 140px" />
                    <Column header="Aksi" style="width: 150px">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('pasien.show', data.id)">
                                    <Button icon="pi pi-eye" severity="info" text rounded size="small" />
                                </Link>
                                <Link :href="route('pasien.edit', data.id)">
                                    <Button icon="pi pi-pencil" severity="warn" text rounded size="small" />
                                </Link>
                                <Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="deletePasien(data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <div class="flex justify-between items-center text-sm text-gray-600">
                <span>Menampilkan {{ pasiens.data.length }} dari {{ pasiens.total }} data</span>
                <div class="flex gap-2">
                    <Button
                        icon="pi pi-chevron-left"
                        :disabled="pasiens.current_page === 1"
                        severity="secondary"
                        size="small"
                        @click="router.get(route('pasien.index', { page: pasiens.current_page - 1, search }))"
                    />
                    <span class="px-3 py-1">{{ pasiens.current_page }} / {{ pasiens.last_page }}</span>
                    <Button
                        icon="pi pi-chevron-right"
                        :disabled="pasiens.current_page === pasiens.last_page"
                        severity="secondary"
                        size="small"
                        @click="router.get(route('pasien.index', { page: pasiens.current_page + 1, search }))"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

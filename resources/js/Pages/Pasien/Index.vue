<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Pasien } from '@/types';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/dropdown';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Card from 'primevue/card';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import Swal from 'sweetalert2';
import { usePage, useForm } from '@inertiajs/vue3';
import { formatDateTimeToLocalString } from '@/utils/date';

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
        tipe_pasien?: string;
    };
}

const props = defineProps<Props>();

const toast = useToast();
const page = usePage();
const search = ref(props.filters.search || '');
const tipePasien = ref(props.filters.tipe_pasien || null);

const filterOptions = [
    { label: 'Semua Tipe', value: null },
    { label: 'Mahasiswa', value: 'mahasiswa' },
    { label: 'Dosen', value: 'dosen' },
    { label: 'Tendik', value: 'tendik' },
    { label: 'Umum', value: 'umum' },
];

const canEditPasien = computed(() => {
    // @ts-ignore
    const role = page.props.auth?.user?.role;
    return role === 'superadmin' || role === 'admin';
});

const doSearch = () => {
    const params: any = {};
    if (search.value) params.search = search.value;
    if (tipePasien.value) params.tipe_pasien = tipePasien.value;
    
    router.get(route('pasien.index'), params, { preserveState: true });
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
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus pasien "${pasien.nama}"?`,
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
            router.delete(route('pasien.destroy', pasien.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pasien berhasil dihapus', life: 3000 });
                }
            });
        }
    });
};

const showKunjunganDialog = ref(false);
const selectedPasien = ref<Pasien | null>(null);

const getClientTime = () => {
    const now = new Date();
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
};

// Removed localDate and localTime as we use the form directly

const kunjunganForm = useForm({
    tanggal_kunjungan: new Date(),
    client_time: '',
    jenis_layanan: 'berobat',
    catatan: ''
});

const jenisLayananOptions = [
    { label: 'Pemeriksaan Umum', value: 'berobat' },
    { label: 'Surat Sehat', value: 'surat_sehat' },
    { label: 'Screening', value: 'screening' }
];

const daftarKunjunganBaru = (pasien: Pasien) => {
    selectedPasien.value = pasien;
    kunjunganForm.tanggal_kunjungan = new Date();
    kunjunganForm.client_time = getClientTime();
    kunjunganForm.jenis_layanan = 'berobat';
    kunjunganForm.catatan = '';
    showKunjunganDialog.value = true;
};

const submitKunjungan = () => {
    if (!selectedPasien.value) return;

    kunjunganForm.client_time = getClientTime();
    kunjunganForm
        .transform((data) => ({
            ...data,
            tanggal_kunjungan: formatDateTimeToLocalString(data.tanggal_kunjungan),
        }))
        .post(route('pasien.kunjungan', selectedPasien.value.id), {
            onSuccess: () => {
                showKunjunganDialog.value = false;
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Kunjungan baru berhasil didaftarkan ke antrian', life: 3000 });
            },
            onError: () => {
                toast.add({ severity: 'error', summary: 'Gagal', detail: 'Terjadi kesalahan saat mendaftarkan kunjungan', life: 3000 });
            }
        });
};
</script>

<template>
    <Head title="Daftar Pasien" />
    <AppLayout>
        <template #header>Daftar Pasien</template>

        <div class="space-y-6">
            <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                <template #content>
                    <!-- Filter & Actions -->
                    <div class="mb-6 space-y-4">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                                Daftar Master Pasien
                            </h3>
                            <Link v-if="canEditPasien" :href="route('pasien.create')">
                                <Button 
                                    label="Tambah Pasien" 
                                    icon="pi pi-plus" 
                                    severity="success" 
                                    class="!rounded-xl !text-xs font-bold shadow-sm"
                                />
                            </Link>
                        </div>

                        <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-xl shadow-sm space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Field: Search -->
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Pasien</span>
                                    <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                                        <InputGroupAddon class="!bg-white !border-0 !px-3">
                                            <i class="pi pi-search text-emerald-500 text-[10px]"></i>
                                        </InputGroupAddon>
                                        <InputText
                                            v-model="search"
                                            placeholder="Nama / RM / NIK..."
                                            class="!border-0 !text-xs !py-2 !pl-0 focus:!ring-0 placeholder:text-gray-300"
                                            @keyup.enter="doSearch"
                                        />
                                    </InputGroup>
                                </div>

                                <!-- Field: Tipe Pasien -->
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tipe Pasien</span>
                                    <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                                        <InputGroupAddon class="!bg-white !border-0 !px-3">
                                            <i class="pi pi-users text-emerald-500 text-[10px]"></i>
                                        </InputGroupAddon>
                                        <Select 
                                            v-model="tipePasien" 
                                            :options="filterOptions" 
                                            optionLabel="label" 
                                            optionValue="value" 
                                            placeholder="Semua Tipe" 
                                            class="!border-0 !text-xs !py-0 focus:!ring-0 flex-1"
                                            :pt="{
                                                root: { class: '!border-0 !shadow-none' },
                                                label: { class: '!text-xs !py-2 !pl-0' },
                                                dropdownIcon: { class: '!w-3 !h-3 text-emerald-500' }
                                            }"
                                            @change="doSearch"
                                        />
                                    </InputGroup>
                                </div>
                            </div>
                        </div>
                    </div>

                    <DataTable
                        :value="pasiens.data"
                        :paginator="false"
                        :rows="pasiens.per_page"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        stripedRows
                    >
                        <Column field="nomor_rm" header="No. RM" sortable style="width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">
                                    {{ data.nomor_rm }}
                                </span>
                            </template>
                        </Column>
                        <Column field="nama" header="Nama Pasien" sortable>
                            <template #body="{ data }">
                                <div>
                                    <p class="font-medium text-gray-900">{{ data.nama }}</p>
                                    <p class="text-xs text-gray-500">{{ data.nik || '-' }}</p>
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
                                {{ data.tanggal_lahir ? new Date(data.tanggal_lahir).toLocaleDateString('id-ID') : '-' }}
                            </template>
                        </Column>
                        <Column field="tipe_pasien" header="Status" style="width: 120px">
                            <template #body="{ data }">
                                <Tag :value="getStatusLabel(data.tipe_pasien)" :severity="getStatusSeverity(data.tipe_pasien)" class="!text-[10px] !px-2" />
                            </template>
                        </Column>
                        <Column field="phone" header="Telepon" style="width: 140px">
                            <template #body="{ data }">
                                <span class="text-gray-700">{{ data.phone || '-' }}</span>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 200px">
                            <template #body="{ data }">
                                <div class="flex items-center gap-1">
                                    <Button
                                        v-if="canEditPasien"
                                        icon="pi pi-calendar-plus"
                                        severity="success"
                                        text
                                        rounded
                                        v-tooltip.top="'Daftar Kunjungan Baru'"
                                        @click="daftarKunjunganBaru(data)"
                                    />
                                    <Link :href="route('pasien.rekam-medis', data.id)">
                                        <Button icon="pi pi-folder-open" severity="help" text rounded v-tooltip.top="'Rekam Medis'" />
                                    </Link>
                                    <Link :href="route('pasien.show', data.id)">
                                        <Button icon="pi pi-eye" severity="info" text rounded v-tooltip.top="'Lihat Detail'" />
                                    </Link>
                                    <Link v-if="canEditPasien" :href="route('pasien.edit', data.id)">
                                        <Button icon="pi pi-pencil" severity="warn" text rounded v-tooltip.top="'Edit'" />
                                    </Link>
                                    <Button v-if="canEditPasien" icon="pi pi-trash" severity="danger" text rounded @click="deletePasien(data)" v-tooltip.top="'Hapus'" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                    <div class="mt-4 flex justify-between items-center text-sm text-gray-600 border-t pt-4">
                        <span>Menampilkan {{ pasiens.data.length }} dari {{ pasiens.total }} data</span>
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-chevron-left"
                                :disabled="pasiens.current_page === 1"
                                severity="secondary"
                                size="small"
                                class="!rounded-xl"
                                @click="router.get(route('pasien.index', { page: pasiens.current_page - 1, search }))"
                            />
                            <span class="px-3 py-1 font-medium bg-gray-50 rounded-xl">{{ pasiens.current_page }} / {{ pasiens.last_page }}</span>
                            <Button
                                icon="pi pi-chevron-right"
                                :disabled="pasiens.current_page === pasiens.last_page"
                                severity="secondary"
                                size="small"
                                class="!rounded-xl"
                                @click="router.get(route('pasien.index', { page: pasiens.current_page + 1, search }))"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Tambahkan Dialog Kunjungan Baru -->
        <Dialog v-model:visible="showKunjunganDialog" modal :header="'Daftarkan Ke Antrian : ' + (selectedPasien?.nama || '')" :style="{ width: '450px' }">
            <div class="flex flex-col gap-4 mt-2">
                <div class="flex flex-col gap-2">
                    <label for="waktu" class="text-sm font-semibold">Tgl & Waktu Kunjungan</label>
                    <DatePicker 
                        id="waktu_tanggal" 
                        v-model="kunjunganForm.tanggal_kunjungan" 
                        dateFormat="dd/mm/yy"
                        :showIcon="true"
                        :showTime="true"
                        hourFormat="24"
                        appendTo="body"
                        placeholder="Pilih Tanggal dan Jam"
                        class="w-full"
                    />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="layanan" class="text-sm font-semibold">Jenis Layanan</label>
                    <Select 
                        id="layanan" 
                        v-model="kunjunganForm.jenis_layanan" 
                        :options="jenisLayananOptions" 
                        optionLabel="label" 
                        optionValue="value" 
                        class="w-full"
                    />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="catatan" class="text-sm font-semibold">Catatan Awal (Opsional)</label>
                    <Textarea 
                        id="catatan" 
                        v-model="kunjunganForm.catatan" 
                        rows="3" 
                        placeholder="Misal: Pasien minta rujukan..."
                        class="w-full resize-none"
                    />
                </div>
            </div>
            <template #footer>
                <Button label="Batal" icon="pi pi-times" text severity="secondary" @click="showKunjunganDialog = false" />
                <Button label="Simpan Antrian" icon="pi pi-check" severity="success" :loading="kunjunganForm.processing" @click="submitKunjungan" />
            </template>
        </Dialog>
    </AppLayout>
</template>

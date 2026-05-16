<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Card from 'primevue/card';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import { useToast } from 'primevue/usetoast';
import { router } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useConfirm } from 'primevue/useconfirm';

interface AntrianItem {
    id: number;
    nomor_kunjungan: string;
    catatan: string;
    tanggal_kunjungan: string;
    status: string;
    pasien: {
        id: number;
        nomor_rm: string;
        nama: string;
        jenis_kelamin: string;
        tanggal_lahir: string;
        alamat: string;
    };
}

interface Props {
    antrian: AntrianItem[];
    pasiens?: Array<{ id: number, nama: string, nomor_rm: string }>;
    filters?: {
        filter_waktu: string;
        custom_date: string;
    };
}

const props = defineProps<Props>();
const toast = useToast();
const confirm = useConfirm();
const page = usePage<any>();
const currentUser = page.props.auth?.user;
const canManageAntrian = ['superadmin', 'admin', 'perawat'].includes(currentUser?.role);

const selectedFilterWaktu = ref(props.filters?.filter_waktu || 'semua');
const customDate = ref<Date | null>(props.filters?.custom_date ? new Date(props.filters.custom_date) : null);

const timeOptions = [
    { label: 'Semua Antrian', value: 'semua' },
    { label: 'Hari Ini', value: 'hari_ini' },
    { label: 'Minggu Ini', value: 'minggu_ini' },
    { label: 'Bulan Ini', value: 'bulan_ini' },
    { label: 'Kustom Tanggal', value: 'custom' }
];

const applyFilter = () => {
    let payload: any = { filter_waktu: selectedFilterWaktu.value };
    if (selectedFilterWaktu.value === 'custom' && customDate.value) {
        // Format to YYYY-MM-DD
        const d = customDate.value;
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        payload.custom_date = `${year}-${month}-${day}`;
    }
    
    router.get(
        route('perawat.antrian'),
        payload,
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

watch(selectedFilterWaktu, (newValue) => {
    if (newValue === 'custom' && !customDate.value) {
        return; // wait for date selection
    }
    applyFilter();
});

watch(customDate, (newValue) => {
    if (selectedFilterWaktu.value === 'custom') {
        applyFilter();
    }
});

const showAnamnesisDialog = ref(false);
const selectedPasien = ref<AntrianItem | null>(null);

const form = useForm({
    rekam_medis_id: 0,
    tekanan_darah_sistolik: null as number | null,
    tekanan_darah_diastolik: null as number | null,
    suhu: null as number | null,
    nadi: null as number | null,
    respirasi: null as number | null,
    tinggi_badan: null as number | null,
    berat_badan: null as number | null,
    keluhan_utama: '',
    riwayat_penyakit_sekarang: '',
    riwayat_penyakit_dahulu: '',
    riwayat_alergi: '',
    riwayat_obat: '',
    riwayat_keluarga: '',
    skala_nyeri: null as number | null,
    diagnosa_keperawatan: '',
    intervensi_keperawatan: '',
    implementasi_keperawatan: '',
    evaluasi_keperawatan: '',
});

const openAnamnesisDialog = (item: AntrianItem) => {
    selectedPasien.value = item;
    form.rekam_medis_id = item.id;
    showAnamnesisDialog.value = true;
};

const closeDialog = () => {
    showAnamnesisDialog.value = false;
    selectedPasien.value = null;
    resetForm();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

const submitAnamnesis = () => {
    form.post(route('perawat.anamnesis.store'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Berhasil',
                detail: 'Data anamnesis berhasil disimpan',
                life: 3000
            });
            closeDialog();
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Gagal',
                detail: 'Periksa kembali field yang ditandai merah',
                life: 5000
            });
        }
    });
};

const getAge = (birthDate: string) => {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
};

// -- CRUD ANTRIAN (ADMIN/SUPERADMIN) --
const showCrudDialog = ref(false);
const crudMode = ref<'create' | 'edit'>('create');
const selectedRekamMedisId = ref<number | null>(null);

const getClientTime = () => {
    const now = new Date();
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
};

const formAntrian = useForm({
    pasien_id: null as number | null,
    tanggal_kunjungan: new Date(),
    jenis_layanan: 'berobat',
    catatan: '',
    client_time: ''
});

const layananOptions = [
    { label: 'Berobat', value: 'berobat' },
    { label: 'Surat Sehat', value: 'surat_sehat' },
    { label: 'Screening', value: 'screening' }
];

const openCreateDialog = () => {
    crudMode.value = 'create';
    formAntrian.reset();
    formAntrian.tanggal_kunjungan = new Date();
    formAntrian.client_time = getClientTime();
    formAntrian.jenis_layanan = 'berobat';
    showCrudDialog.value = true;
};

const openEditDialog = (item: AntrianItem) => {
    crudMode.value = 'edit';
    selectedRekamMedisId.value = item.id;
    formAntrian.pasien_id = item.pasien.id;
    formAntrian.tanggal_kunjungan = new Date(item.tanggal_kunjungan);
    formAntrian.jenis_layanan = item.nomor_kunjungan.includes('KNJ') ? 'berobat' : 'berobat';
    formAntrian.catatan = item.catatan || '';
    showCrudDialog.value = true;
};

const submitAntrian = () => {
    if (crudMode.value === 'create') {
        formAntrian.client_time = getClientTime();
        formAntrian.post(route('antrian.store'), {
            onSuccess: () => {
                showCrudDialog.value = false;
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Antrian ditambahkan', life: 3000 });
            }
        });
    } else if (selectedRekamMedisId.value) {
        formAntrian.put(route('antrian.update', { rekamMedis: selectedRekamMedisId.value }), {
            onSuccess: () => {
                showCrudDialog.value = false;
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Antrian diperbarui', life: 3000 });
            }
        });
    }
};

const deleteAntrian = (item: AntrianItem) => {
    confirm.require({
        message: `Apakah Anda yakin ingin membatalkan/menghapus antrian untuk pasien ${item.pasien?.nama}?`,
        header: 'Konfirmasi Penghapusan',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('antrian.destroy', item.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Dihapus', detail: 'Antrian berhasil dibatalkan', life: 3000 });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Antrian Pasien" />
    <AppLayout>
        <template #header>Antrian Pasien - Anamnesis</template>

        <div class="space-y-4">
            <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                <template #content>
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                                Daftar Antrian Pasien
                            </h3>
                            <Tag :value="`${antrian.length} Pasien`" severity="success" class="!rounded-lg !px-3" />
                        </div>

                        <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-2xl shadow-sm space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <Button
                                    v-if="canManageAntrian"
                                    label="Tambah Jadwal"
                                    icon="pi pi-plus"
                                    severity="success"
                                    class="!rounded-xl !text-xs font-bold shadow-sm"
                                    @click="openCreateDialog"
                                />
                                
                                <div class="flex items-center gap-2 flex-1 min-w-[200px]">
                                    <div class="flex flex-col gap-1.5 flex-1">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Filter Waktu</span>
                                        <div class="flex gap-2">
                                            <Select
                                                v-model="selectedFilterWaktu"
                                                :options="timeOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih Waktu"
                                                class="!border-gray-200 !rounded-xl !text-xs flex-1 shadow-sm focus:!ring-emerald-500/20"
                                            >
                                                <template #value="slotProps">
                                                    <div v-if="slotProps.value" class="flex items-center gap-2">
                                                        <i class="pi pi-calendar text-emerald-500 text-[10px]"></i>
                                                        <span>{{ timeOptions.find(o => o.value === slotProps.value)?.label }}</span>
                                                    </div>
                                                </template>
                                            </Select>

                                            <div v-if="selectedFilterWaktu === 'custom'" class="w-40">
                                                <DatePicker
                                                    v-model="customDate"
                                                    dateFormat="dd/mm/yy"
                                                    placeholder="Pilih Tanggal"
                                                    class="!border-gray-200 !rounded-xl !text-xs w-full shadow-sm"
                                                    inputClass="!py-2 !px-3 !text-xs"
                                                    :showIcon="true"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <DataTable
                        :value="antrian"
                        :paginator="antrian.length > 10"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        emptyMessage="Tidak ada pasien dalam antrian"
                    >
                        <Column header="No" style="width: 60px">
                            <template #body="{ index }">
                                {{ index + 1 }}
                            </template>
                        </Column>
                        <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono text-[11px] text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100/50 shadow-sm">{{ data.nomor_kunjungan }}</span>
                            </template>
                        </Column>
                        <Column header="Pasien">
                            <template #body="{ data }">
                                <div>
                                    <p class="font-medium text-gray-900">{{ data.pasien?.nama || 'Pasien Tidak Diketahui' }}</p>
                                    <p class="text-xs text-gray-500" v-if="data.pasien">
                                        {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                        {{ getAge(data.pasien.tanggal_lahir) }} thn
                                    </p>
                                    <p class="text-xs text-red-400 italic" v-else>
                                        Data pasien telah dihapus
                                    </p>
                                </div>
                            </template>
                        </Column>
                        <Column field="catatan" header="Catatan">
                            <template #body="{ data }">
                                <span class="text-gray-600 text-sm italic">{{ data.catatan || '-' }}</span>
                            </template>
                        </Column>
                        <Column header="Jadwal Kunjungan" style="width: 180px">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700">
                                        {{ new Date(data.tanggal_kunjungan).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                                    </span>
                                    <div class="flex items-center gap-1 text-emerald-600 text-[10px]">
                                        <i class="pi pi-clock"></i>
                                        <span>{{ new Date(data.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB</span>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 250px" class="text-center">
                            <template #body="{ data }">
                                <div class="flex gap-2 justify-center">
                                    <Button
                                        label="Anamnesis"
                                        icon="pi pi-pencil"
                                        severity="success"
                                        class="!rounded-xl !text-[11px] !py-2 !px-4 shadow-sm hover:shadow-md transition-all font-bold"
                                        @click="openAnamnesisDialog(data)"
                                    />
                                    <Button
                                        v-if="canManageAntrian"
                                        icon="pi pi-file-edit"
                                        severity="info"
                                        class="!rounded-xl !w-9 !h-9 shadow-sm"
                                        v-tooltip.top="'Update Antrian'"
                                        @click="openEditDialog(data)"
                                    />
                                    <Button
                                        v-if="canManageAntrian"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        class="!rounded-xl !w-9 !h-9 shadow-sm"
                                        v-tooltip.top="'Batalkan / Hapus Antrian'"
                                        @click="deleteAntrian(data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Dialog Anamnesis -->
        <Dialog
            v-model:visible="showAnamnesisDialog"
            modal
            header="Input Data Anamnesis"
            :style="{ width: '800px' }"
            :closable="true"
            @hide="closeDialog"
        >
            <div v-if="selectedPasien" class="space-y-3">
                <!-- Info Pasien -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Nama Pasien:</span>
                            <p class="font-medium">{{ selectedPasien.pasien?.nama || 'Data Dihapus' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">No. RM:</span>
                            <p class="font-medium">{{ selectedPasien.pasien?.nomor_rm || '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Catatan:</span>
                            <p class="font-medium">{{ selectedPasien.catatan || '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">No. Kunjungan:</span>
                            <p class="font-medium">{{ selectedPasien.nomor_kunjungan }}</p>
                        </div>
                    </div>
                </div>

                <!-- Keluhan Utama (Required) -->
                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Keluhan Utama <span class="text-red-500">*</span></label>
                    <Textarea
                        v-model="form.keluhan_utama"
                        rows="2"
                        placeholder="Keluhan yang dirasakan pasien saat ini"
                        :class="{ 'p-invalid': form.errors.keluhan_utama }"
                    />
                    <small v-if="form.errors.keluhan_utama" class="text-red-500">{{ form.errors.keluhan_utama }}</small>
                </div>

                <!-- Form Anamnesis - Vital Signs -->
                <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Tekanan Darah (mmHg)</label>
                        <div class="flex items-center">
                            <InputNumber
                                v-model="form.tekanan_darah_sistolik"
                                size="small"
                                placeholder="120"
                                :inputStyle="{ width: '80px', textAlign: 'center' }"
                                :class="{ 'p-invalid': form.errors.tekanan_darah_sistolik }"
                            />
                            <span class="mx-3 font-bold text-gray-500 text-lg">/</span>
                            <InputNumber
                                v-model="form.tekanan_darah_diastolik"
                                size="small"
                                placeholder="80"
                                :inputStyle="{ width: '80px', textAlign: 'center' }"
                                :class="{ 'p-invalid': form.errors.tekanan_darah_diastolik }"
                            />
                        </div>
                        <small v-if="form.errors.tekanan_darah_sistolik" class="text-red-500">{{ form.errors.tekanan_darah_sistolik }}</small>
                        <small v-if="form.errors.tekanan_darah_diastolik" class="text-red-500">{{ form.errors.tekanan_darah_diastolik }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Suhu Tubuh (°C)</label>
                        <InputNumber
                            v-model="form.suhu"
                            size="small"
                            :minFractionDigits="1"
                            :maxFractionDigits="1"
                            placeholder="36.5"
                            :class="{ 'p-invalid': form.errors.suhu }"
                        />
                        <small v-if="form.errors.suhu" class="text-red-500">{{ form.errors.suhu }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Nadi (x/menit)</label>
                        <InputNumber
                            v-model="form.nadi"
                            size="small"
                            placeholder="80"
                            :class="{ 'p-invalid': form.errors.nadi }"
                        />
                        <small v-if="form.errors.nadi" class="text-red-500">{{ form.errors.nadi }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Respirasi (x/menit)</label>
                        <InputNumber
                            v-model="form.respirasi"
                            size="small"
                            placeholder="20"
                            :class="{ 'p-invalid': form.errors.respirasi }"
                        />
                        <small v-if="form.errors.respirasi" class="text-red-500">{{ form.errors.respirasi }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Tinggi Badan (cm)</label>
                        <InputNumber
                            v-model="form.tinggi_badan"
                            size="small"
                            placeholder="170"
                            :class="{ 'p-invalid': form.errors.tinggi_badan }"
                        />
                        <small v-if="form.errors.tinggi_badan" class="text-red-500">{{ form.errors.tinggi_badan }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Berat Badan (kg)</label>
                        <InputNumber
                            v-model="form.berat_badan"
                            size="small"
                            :minFractionDigits="1"
                            placeholder="65.0"
                            :class="{ 'p-invalid': form.errors.berat_badan }"
                        />
                        <small v-if="form.errors.berat_badan" class="text-red-500">{{ form.errors.berat_badan }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Skala Nyeri (0-10)</label>
                        <InputNumber
                            v-model="form.skala_nyeri"
                            size="small"
                            placeholder="0"
                            :min="0"
                            :max="10"
                            showButtons
                            buttonLayout="horizontal"
                            :step="1"
                            decrementButtonClass="p-button-secondary p-button-sm"
                            incrementButtonClass="p-button-secondary p-button-sm"
                            decrementButtonIcon="pi pi-minus text-sm"
                            incrementButtonIcon="pi pi-plus text-sm"
                            :class="{ 'p-invalid': form.errors.skala_nyeri }"
                        />
                        <small v-if="form.errors.skala_nyeri" class="text-red-500">{{ form.errors.skala_nyeri }}</small>
                    </div>
                </div>

                <!-- Riwayat -->
                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Penyakit Sekarang</label>
                    <Textarea
                        v-model="form.riwayat_penyakit_sekarang"
                        rows="1"
                        autoResize
                        placeholder="Riwayat penyakit yang sedang dialami"
                        :class="{ 'p-invalid': form.errors.riwayat_penyakit_sekarang }"
                    />
                    <small v-if="form.errors.riwayat_penyakit_sekarang" class="text-red-500">{{ form.errors.riwayat_penyakit_sekarang }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Penyakit Dahulu</label>
                    <Textarea
                        v-model="form.riwayat_penyakit_dahulu"
                        rows="1"
                        autoResize
                        placeholder="Riwayat penyakit yang pernah diderita sebelumnya"
                        :class="{ 'p-invalid': form.errors.riwayat_penyakit_dahulu }"
                    />
                    <small v-if="form.errors.riwayat_penyakit_dahulu" class="text-red-500">{{ form.errors.riwayat_penyakit_dahulu }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Alergi</label>
                    <Textarea
                        v-model="form.riwayat_alergi"
                        rows="1"
                        autoResize
                        placeholder="Alergi obat, makanan, dll"
                        :class="{ 'p-invalid': form.errors.riwayat_alergi }"
                    />
                    <small v-if="form.errors.riwayat_alergi" class="text-red-500">{{ form.errors.riwayat_alergi }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Obat</label>
                    <Textarea
                        v-model="form.riwayat_obat"
                        rows="1"
                        autoResize
                        placeholder="Obat-obatan yang sedang dikonsumsi"
                        :class="{ 'p-invalid': form.errors.riwayat_obat }"
                    />
                    <small v-if="form.errors.riwayat_obat" class="text-red-500">{{ form.errors.riwayat_obat }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Keluarga</label>
                    <Textarea
                        v-model="form.riwayat_keluarga"
                        rows="1"
                        autoResize
                        placeholder="Riwayat penyakit dalam keluarga"
                        :class="{ 'p-invalid': form.errors.riwayat_keluarga }"
                    />
                    <small v-if="form.errors.riwayat_keluarga" class="text-red-500">{{ form.errors.riwayat_keluarga }}</small>
                </div>

                <!-- Asuhan Keperawatan -->
                <div class="border-t pt-3 mt-2">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Asuhan Keperawatan</h3>
                    <div class="space-y-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Diagnosa Keperawatan</label>
                            <Textarea
                                v-model="form.diagnosa_keperawatan"
                                rows="2"
                                autoResize
                                placeholder="Tuliskan diagnosa keperawatan"
                                :class="{ 'p-invalid': form.errors.diagnosa_keperawatan }"
                            />
                            <small v-if="form.errors.diagnosa_keperawatan" class="text-red-500">{{ form.errors.diagnosa_keperawatan }}</small>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Intervensi Keperawatan</label>
                            <Textarea
                                v-model="form.intervensi_keperawatan"
                                rows="2"
                                autoResize
                                placeholder="Rencana tindakan keperawatan"
                                :class="{ 'p-invalid': form.errors.intervensi_keperawatan }"
                            />
                            <small v-if="form.errors.intervensi_keperawatan" class="text-red-500">{{ form.errors.intervensi_keperawatan }}</small>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Implementasi Keperawatan</label>
                            <Textarea
                                v-model="form.implementasi_keperawatan"
                                rows="2"
                                autoResize
                                placeholder="Tindakan yang telah dilakukan"
                                :class="{ 'p-invalid': form.errors.implementasi_keperawatan }"
                            />
                            <small v-if="form.errors.implementasi_keperawatan" class="text-red-500">{{ form.errors.implementasi_keperawatan }}</small>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Evaluasi Keperawatan (SOAP)</label>
                            <Textarea
                                v-model="form.evaluasi_keperawatan"
                                rows="2"
                                autoResize
                                placeholder="S: ... O: ... A: ... P: ..."
                                :class="{ 'p-invalid': form.errors.evaluasi_keperawatan }"
                            />
                            <small v-if="form.errors.evaluasi_keperawatan" class="text-red-500">{{ form.errors.evaluasi_keperawatan }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" :disabled="form.processing" />
                <Button
                    label="Simpan & Lanjut ke Dokter"
                    icon="pi pi-check"
                    @click="submitAnamnesis"
                    :loading="form.processing"
                    :disabled="form.processing"
                />
            </template>
        </Dialog>

        <!-- Dialog CRUD Antrian (Admin/Superadmin) -->
        <Dialog
            v-model:visible="showCrudDialog"
            modal
            header="CRUD Antrian"
            :style="{ width: '600px' }"
            :closable="true"
            @hide="showCrudDialog = false; selectedRekamMedisId = null"
        >
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Pasien</label>
                    <Select
                        v-model="formAntrian.pasien_id"
                        :options="pasiens"
                        optionLabel="nama"
                        optionValue="id"
                        placeholder="Pilih Pasien"
                        class="w-full"
                        :class="{ 'p-invalid': formAntrian.errors.pasien_id }"
                    />
                    <small v-if="formAntrian.errors.pasien_id" class="text-red-500">{{ formAntrian.errors.pasien_id }}</small>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Tanggal Kunjungan</label>
                        <DatePicker
                            v-model="formAntrian.tanggal_kunjungan"
                            dateFormat="dd/mm/yy"
                            placeholder="Pilih Tanggal dan Waktu"
                            class="w-full"
                            :showIcon="true"
                            :showTime="true"
                            hourFormat="24"
                            :class="{ 'p-invalid': formAntrian.errors.tanggal_kunjungan }"
                        />
                        <small v-if="formAntrian.errors.tanggal_kunjungan" class="text-red-500">{{ formAntrian.errors.tanggal_kunjungan }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Jenis Layanan</label>
                        <Select
                            v-model="formAntrian.jenis_layanan"
                            :options="layananOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Pilih Jenis Layanan"
                            class="w-full"
                            :class="{ 'p-invalid': formAntrian.errors.jenis_layanan }"
                        />
                        <small v-if="formAntrian.errors.jenis_layanan" class="text-red-500">{{ formAntrian.errors.jenis_layanan }}</small>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Catatan</label>
                    <Textarea
                        v-model="formAntrian.catatan"
                        rows="2"
                        placeholder="Catatan tambahan untuk antrian"
                        :class="{ 'p-invalid': formAntrian.errors.catatan }"
                    />
                    <small v-if="formAntrian.errors.catatan" class="text-red-500">{{ formAntrian.errors.catatan }}</small>
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="showCrudDialog = false; selectedRekamMedisId = null" />
                <Button
                    v-if="crudMode === 'create'"
                    label="Tambah Antrian"
                    icon="pi pi-plus"
                    @click="submitAntrian"
                    :loading="formAntrian.processing"
                    :disabled="formAntrian.processing"
                />
                <Button
                    v-else
                    label="Perbarui Antrian"
                    icon="pi pi-refresh"
                    @click="submitAntrian"
                    :loading="formAntrian.processing"
                    :disabled="formAntrian.processing"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import { useToast } from 'primevue/usetoast';

interface Obat {
    id: number;
    kode: string;
    nama: string;
    satuan: string;
    stok: number;
}

interface Tindakan {
    id: number;
    kode: string;
    nama: string;
    biaya: number;
}

interface AntrianItem {
    id: number;
    nomor_kunjungan: string;
    catatan: string;
    tanggal_kunjungan: string;
    pasien: {
        id: number;
        nomor_rm: string;
        nama: string;
        jenis_kelamin: string;
        tanggal_lahir: string;
    };
    anamnesis: {
        tekanan_darah: string;
        suhu: number;
        nadi: number;
        respirasi: number;
        tinggi_badan: number;
        berat_badan: number;
        keluhan_utama: string;
        riwayat_alergi: string;
    } | null;
}

interface Props {
    antrian: AntrianItem[];
    obats: Obat[];
    tindakans: Tindakan[];
}

const props = defineProps<Props>();
const toast = useToast();

const showPemeriksaanDialog = ref(false);
const selectedPasien = ref<AntrianItem | null>(null);

const form = useForm({
    rekam_medis_id: 0,
    pemeriksaan_fisik: '',
    hasil_pemeriksaan: '',
    diagnosis_utama: '',
    diagnosis_sekunder: '',
    kode_icd10: '',
    prognosis: '',
    anjuran: '',
    penatalaksanaan_medis: '',
    selectedTindakans: [] as number[],
    resepObat: [] as { obat_id: number; jumlah: number; dosis: string; aturan_pakai: string; keterangan: string }[],
    // Surat Keterangan Dokter
    buat_surat: false,
    jenis_surat: '' as string,
    keperluan_surat: '',
    jumlah_hari_istirahat: 1,
    tanggal_mulai: null as Date | null,
    tanggal_selesai: null as Date | null,
});

const jenisSuratOptions = [
    { label: 'Surat Keterangan Sehat', value: 'surat_sehat' },
    { label: 'Surat Keterangan Sakit', value: 'surat_sakit' },
];

const openPemeriksaanDialog = (item: AntrianItem) => {
    selectedPasien.value = item;
    form.rekam_medis_id = item.id;
    showPemeriksaanDialog.value = true;
};

const closeDialog = () => {
    showPemeriksaanDialog.value = false;
    selectedPasien.value = null;
    resetForm();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

const addResepObat = () => {
    form.resepObat.push({
        obat_id: 0,
        jumlah: 1,
        dosis: '',
        aturan_pakai: '',
        keterangan: ''
    });
};

const removeResepObat = (index: number) => {
    form.resepObat.splice(index, 1);
};

const submitPemeriksaan = () => {
    form.post(route('dokter.pemeriksaan.store'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Berhasil',
                detail: 'Data pemeriksaan berhasil disimpan',
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
</script>

<template>
    <Head title="Pasien Siap Periksa" />
    <AppLayout>
        <template #header>Pasien Siap Periksa</template>

        <div class="space-y-4">
            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-check-circle text-emerald-600"></i>
                        <span>Daftar Pasien Siap Diperiksa</span>
                        <Tag :value="`${antrian.length} Pasien`" severity="success" class="ml-auto" />
                    </div>
                </template>
                <template #content>
                    <DataTable
                        :value="antrian"
                        :paginator="antrian.length > 10"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        emptyMessage="Tidak ada pasien yang siap diperiksa"
                    >
                        <Column header="No" style="width: 60px">
                            <template #body="{ index }">
                                {{ index + 1 }}
                            </template>
                        </Column>
                        <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 140px" />
                        <Column header="Pasien">
                            <template #body="{ data }">
                                <div>
                                    <p class="font-medium text-gray-900">{{ data.pasien.nama }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                        {{ getAge(data.pasien.tanggal_lahir) }} thn
                                    </p>
                                </div>
                            </template>
                        </Column>
                        <Column header="Vital Sign" style="width: 200px">
                            <template #body="{ data }">
                                <div class="text-xs space-y-1" v-if="data.anamnesis">
                                    <p>TD: {{ data.anamnesis.tekanan_darah || '-' }} mmHg</p>
                                    <p>Suhu: {{ data.anamnesis.suhu }}°C | Nadi: {{ data.anamnesis.nadi }}x/m</p>
                                    <p class="text-gray-600">{{ data.anamnesis.keluhan_utama }}</p>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </template>
                        </Column>
                        <Column field="catatan" header="Catatan">
                            <template #body="{ data }">
                                <span class="text-gray-700">{{ data.catatan || '-' }}</span>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 120px">
                            <template #body="{ data }">
                                <Button
                                    label="Periksa"
                                    icon="pi pi-stethoscope"
                                    size="small"
                                    @click="openPemeriksaanDialog(data)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Dialog Pemeriksaan -->
        <Dialog
            v-model:visible="showPemeriksaanDialog"
            modal
            header="Pemeriksaan Dokter"
            :style="{ width: '900px' }"
            :closable="true"
            @hide="closeDialog"
        >
            <div v-if="selectedPasien" class="space-y-4">
                <!-- Info Pasien & Anamnesis -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium mb-2">Data Pasien</h4>
                        <div class="text-sm space-y-1">
                            <p><span class="text-gray-500">Nama:</span> {{ selectedPasien.pasien.nama }}</p>
                            <p><span class="text-gray-500">No. RM:</span> {{ selectedPasien.pasien.nomor_rm }}</p>
                            <p><span class="text-gray-500">Catatan:</span> {{ selectedPasien.catatan || '-' }}</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg" v-if="selectedPasien.anamnesis">
                        <h4 class="font-medium mb-2">Hasil Anamnesis</h4>
                        <div class="text-sm space-y-1">
                            <p><span class="text-gray-500">Keluhan:</span> {{ selectedPasien.anamnesis.keluhan_utama }}</p>
                            <p>TD: {{ selectedPasien.anamnesis.tekanan_darah || '-' }} mmHg</p>
                            <p>Suhu: {{ selectedPasien.anamnesis.suhu }}°C | Nadi: {{ selectedPasien.anamnesis.nadi }}x/m | RR: {{ selectedPasien.anamnesis.respirasi }}x/m</p>
                            <p>BB: {{ selectedPasien.anamnesis.berat_badan }} kg | TB: {{ selectedPasien.anamnesis.tinggi_badan }} cm</p>
                            <p v-if="selectedPasien.anamnesis.riwayat_alergi">
                                <span class="text-red-600">Alergi: {{ selectedPasien.anamnesis.riwayat_alergi }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Pemeriksaan Fisik -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Pemeriksaan Fisik</label>
                        <Textarea
                            v-model="form.pemeriksaan_fisik"
                            rows="2"
                            placeholder="Hasil pemeriksaan fisik"
                            :class="{ 'p-invalid': form.errors.pemeriksaan_fisik }"
                        />
                        <small v-if="form.errors.pemeriksaan_fisik" class="text-red-500">{{ form.errors.pemeriksaan_fisik }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Hasil Pemeriksaan</label>
                        <Textarea
                            v-model="form.hasil_pemeriksaan"
                            rows="2"
                            placeholder="Hasil pemeriksaan penunjang"
                            :class="{ 'p-invalid': form.errors.hasil_pemeriksaan }"
                        />
                        <small v-if="form.errors.hasil_pemeriksaan" class="text-red-500">{{ form.errors.hasil_pemeriksaan }}</small>
                    </div>
                </div>

                <!-- Form Diagnosis -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Diagnosis Utama <span class="text-red-500">*</span></label>
                        <Textarea
                            v-model="form.diagnosis_utama"
                            rows="2"
                            placeholder="Masukkan diagnosis utama"
                            :class="{ 'p-invalid': form.errors.diagnosis_utama }"
                        />
                        <small v-if="form.errors.diagnosis_utama" class="text-red-500">{{ form.errors.diagnosis_utama }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Diagnosis Sekunder</label>
                        <Textarea
                            v-model="form.diagnosis_sekunder"
                            rows="2"
                            placeholder="Diagnosis sekunder (opsional)"
                            :class="{ 'p-invalid': form.errors.diagnosis_sekunder }"
                        />
                        <small v-if="form.errors.diagnosis_sekunder" class="text-red-500">{{ form.errors.diagnosis_sekunder }}</small>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Kode ICD-10</label>
                        <InputText
                            v-model="form.kode_icd10"
                            placeholder="Contoh: J00"
                            :class="{ 'p-invalid': form.errors.kode_icd10 }"
                        />
                        <small v-if="form.errors.kode_icd10" class="text-red-500">{{ form.errors.kode_icd10 }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Prognosis</label>
                        <InputText
                            v-model="form.prognosis"
                            placeholder="Baik / Sedang / Buruk"
                            :class="{ 'p-invalid': form.errors.prognosis }"
                        />
                        <small v-if="form.errors.prognosis" class="text-red-500">{{ form.errors.prognosis }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Anjuran</label>
                        <InputText
                            v-model="form.anjuran"
                            placeholder="Anjuran untuk pasien"
                            :class="{ 'p-invalid': form.errors.anjuran }"
                        />
                        <small v-if="form.errors.anjuran" class="text-red-500">{{ form.errors.anjuran }}</small>
                    </div>
                </div>

                <!-- Penatalaksanaan Medis (NEW) -->
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Penatalaksanaan Medis (Catatan Tambahan)</label>
                    <Textarea
                        v-model="form.penatalaksanaan_medis"
                        rows="3"
                        placeholder="Catatan penatalaksanaan medis selain resep"
                        :class="{ 'p-invalid': form.errors.penatalaksanaan_medis }"
                    />
                    <small v-if="form.errors.penatalaksanaan_medis" class="text-red-500">{{ form.errors.penatalaksanaan_medis }}</small>
                </div>

                <!-- Tindakan -->
                <div class="border-t pt-4">
                    <h4 class="font-medium mb-3">Tindakan yang Dilakukan</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="tindakan in tindakans" :key="tindakan.id" class="flex items-center gap-2">
                            <Checkbox
                                v-model="form.selectedTindakans"
                                :inputId="`tindakan-${tindakan.id}`"
                                :value="tindakan.id"
                            />
                            <label :for="`tindakan-${tindakan.id}`" class="text-sm">{{ tindakan.nama }}</label>
                        </div>
                    </div>
                </div>

                <!-- Resep Obat -->
                <div class="border-t pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium">Resep Obat</h4>
                        <Button label="Tambah Obat" icon="pi pi-plus" size="small" severity="secondary" @click="addResepObat" />
                    </div>
                    <div v-for="(item, index) in form.resepObat" :key="index" class="grid grid-cols-12 gap-2 mb-2 items-end">
                        <div class="col-span-3">
                            <label class="text-xs text-gray-500">Obat</label>
                            <select v-model="item.obat_id" class="w-full border rounded px-2 py-1.5 text-sm">
                                <option :value="0">Pilih obat...</option>
                                <option v-for="obat in obats" :key="obat.id" :value="obat.id">
                                    {{ obat.nama }} ({{ obat.satuan }}) - Stok: {{ obat.stok }}
                                </option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Jumlah</label>
                            <InputNumber v-model="item.jumlah" :min="1" class="w-full" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Dosis</label>
                            <InputText v-model="item.dosis" placeholder="500mg" class="w-full" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Aturan Pakai</label>
                            <InputText v-model="item.aturan_pakai" placeholder="3x1" class="w-full" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Keterangan</label>
                            <InputText v-model="item.keterangan" placeholder="Sesudah makan" class="w-full" />
                        </div>
                        <div class="col-span-1">
                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="removeResepObat(index)" />
                        </div>
                    </div>
                </div>

                <!-- Surat Keterangan Dokter -->
                <div class="border-t pt-4">
                    <div class="flex items-center gap-3 mb-3">
                        <Checkbox v-model="form.buat_surat" :binary="true" inputId="buat_surat" />
                        <label for="buat_surat" class="font-medium">Buat Surat Keterangan Dokter</label>
                    </div>

                    <div v-if="form.buat_surat" class="bg-amber-50 p-4 rounded-lg space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Jenis Surat <span class="text-red-500">*</span></label>
                                <Select
                                    v-model="form.jenis_surat"
                                    :options="jenisSuratOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih jenis surat"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.jenis_surat }"
                                />
                                <small v-if="form.errors.jenis_surat" class="text-red-500">{{ form.errors.jenis_surat }}</small>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Keperluan</label>
                                <InputText
                                    v-model="form.keperluan_surat"
                                    placeholder="Misal: Pendaftaran beasiswa"
                                    :class="{ 'p-invalid': form.errors.keperluan_surat }"
                                />
                                <small v-if="form.errors.keperluan_surat" class="text-red-500">{{ form.errors.keperluan_surat }}</small>
                            </div>
                        </div>

                        <!-- Fields khusus Surat Sakit -->
                        <div v-if="form.jenis_surat === 'surat_sakit'" class="grid grid-cols-3 gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Jumlah Hari Istirahat</label>
                                <InputNumber
                                    v-model="form.jumlah_hari_istirahat"
                                    :min="1"
                                    :max="14"
                                    suffix=" hari"
                                    class="w-full"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Tanggal Mulai</label>
                                <DatePicker
                                    v-model="form.tanggal_mulai"
                                    dateFormat="dd/mm/yy"
                                    placeholder="Pilih tanggal"
                                    class="w-full"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Tanggal Selesai</label>
                                <DatePicker
                                    v-model="form.tanggal_selesai"
                                    dateFormat="dd/mm/yy"
                                    placeholder="Pilih tanggal"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" :disabled="form.processing" />
                <Button
                    label="Simpan Pemeriksaan"
                    icon="pi pi-check"
                    @click="submitPemeriksaan"
                    :loading="form.processing"
                    :disabled="form.processing"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
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
import { useToast } from 'primevue/usetoast';

interface Obat {
    id: number;
    kode_obat: string;
    nama_obat: string;
    satuan: string;
    stok: number;
}

interface Tindakan {
    id: number;
    kode_tindakan: string;
    nama_tindakan: string;
    tarif: number;
}

interface AntrianItem {
    id: number;
    no_kunjungan: string;
    keluhan_utama: string;
    tanggal_kunjungan: string;
    pasien: {
        id: number;
        no_rm: string;
        nama: string;
        jenis_kelamin: string;
        tanggal_lahir: string;
    };
    anamnesis: {
        tekanan_darah_sistolik: number;
        tekanan_darah_diastolik: number;
        suhu_tubuh: number;
        nadi: number;
        pernapasan: number;
        tinggi_badan: number;
        berat_badan: number;
        riwayat_penyakit: string;
        riwayat_alergi: string;
    };
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

const pemeriksaanForm = ref({
    rekam_medis_id: 0,
    diagnosis: '',
    diagnosis_sekunder: '',
    icd_10: '',
    catatan_pemeriksaan: '',
    anjuran: '',
    selectedTindakans: [] as number[],
    resepObat: [] as { obat_id: number; jumlah: number; aturan_pakai: string; catatan: string }[],
});

const openPemeriksaanDialog = (item: AntrianItem) => {
    selectedPasien.value = item;
    pemeriksaanForm.value.rekam_medis_id = item.id;
    showPemeriksaanDialog.value = true;
};

const closeDialog = () => {
    showPemeriksaanDialog.value = false;
    selectedPasien.value = null;
    resetForm();
};

const resetForm = () => {
    pemeriksaanForm.value = {
        rekam_medis_id: 0,
        diagnosis: '',
        diagnosis_sekunder: '',
        icd_10: '',
        catatan_pemeriksaan: '',
        anjuran: '',
        selectedTindakans: [],
        resepObat: [],
    };
};

const addResepObat = () => {
    pemeriksaanForm.value.resepObat.push({
        obat_id: 0,
        jumlah: 1,
        aturan_pakai: '',
        catatan: ''
    });
};

const removeResepObat = (index: number) => {
    pemeriksaanForm.value.resepObat.splice(index, 1);
};

const submitPemeriksaan = () => {
    router.post(route('dokter.pemeriksaan.store'), pemeriksaanForm.value, {
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
                detail: 'Terjadi kesalahan saat menyimpan data',
                life: 3000
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
                        <Column field="no_kunjungan" header="No. Kunjungan" style="width: 140px" />
                        <Column header="Pasien">
                            <template #body="{ data }">
                                <div>
                                    <p class="font-medium text-gray-900">{{ data.pasien.nama }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ data.pasien.no_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                        {{ getAge(data.pasien.tanggal_lahir) }} thn
                                    </p>
                                </div>
                            </template>
                        </Column>
                        <Column header="Vital Sign" style="width: 200px">
                            <template #body="{ data }">
                                <div class="text-xs space-y-1" v-if="data.anamnesis">
                                    <p>TD: {{ data.anamnesis.tekanan_darah_sistolik }}/{{ data.anamnesis.tekanan_darah_diastolik }} mmHg</p>
                                    <p>Suhu: {{ data.anamnesis.suhu_tubuh }}°C | Nadi: {{ data.anamnesis.nadi }}x/m</p>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </template>
                        </Column>
                        <Column field="keluhan_utama" header="Keluhan">
                            <template #body="{ data }">
                                <span class="text-gray-700">{{ data.keluhan_utama || '-' }}</span>
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
                            <p><span class="text-gray-500">No. RM:</span> {{ selectedPasien.pasien.no_rm }}</p>
                            <p><span class="text-gray-500">Keluhan:</span> {{ selectedPasien.keluhan_utama || '-' }}</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg" v-if="selectedPasien.anamnesis">
                        <h4 class="font-medium mb-2">Hasil Anamnesis</h4>
                        <div class="text-sm space-y-1">
                            <p>TD: {{ selectedPasien.anamnesis.tekanan_darah_sistolik }}/{{ selectedPasien.anamnesis.tekanan_darah_diastolik }} mmHg</p>
                            <p>Suhu: {{ selectedPasien.anamnesis.suhu_tubuh }}°C | Nadi: {{ selectedPasien.anamnesis.nadi }}x/m</p>
                            <p>BB: {{ selectedPasien.anamnesis.berat_badan }} kg | TB: {{ selectedPasien.anamnesis.tinggi_badan }} cm</p>
                            <p v-if="selectedPasien.anamnesis.riwayat_alergi">
                                <span class="text-red-600">Alergi: {{ selectedPasien.anamnesis.riwayat_alergi }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Diagnosis -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Diagnosis Utama <span class="text-red-500">*</span></label>
                        <Textarea v-model="pemeriksaanForm.diagnosis" rows="2" placeholder="Masukkan diagnosis utama" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Diagnosis Sekunder</label>
                        <Textarea v-model="pemeriksaanForm.diagnosis_sekunder" rows="2" placeholder="Diagnosis sekunder (opsional)" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Kode ICD-10</label>
                        <InputText v-model="pemeriksaanForm.icd_10" placeholder="Contoh: J00" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Catatan Pemeriksaan</label>
                        <Textarea v-model="pemeriksaanForm.catatan_pemeriksaan" rows="2" placeholder="Catatan pemeriksaan fisik" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Anjuran</label>
                    <Textarea v-model="pemeriksaanForm.anjuran" rows="2" placeholder="Anjuran untuk pasien" />
                </div>

                <!-- Tindakan -->
                <div class="border-t pt-4">
                    <h4 class="font-medium mb-3">Tindakan yang Dilakukan</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="tindakan in tindakans" :key="tindakan.id" class="flex items-center gap-2">
                            <Checkbox
                                v-model="pemeriksaanForm.selectedTindakans"
                                :inputId="`tindakan-${tindakan.id}`"
                                :value="tindakan.id"
                            />
                            <label :for="`tindakan-${tindakan.id}`" class="text-sm">{{ tindakan.nama_tindakan }}</label>
                        </div>
                    </div>
                </div>

                <!-- Resep Obat -->
                <div class="border-t pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium">Resep Obat</h4>
                        <Button label="Tambah Obat" icon="pi pi-plus" size="small" severity="secondary" @click="addResepObat" />
                    </div>
                    <div v-for="(item, index) in pemeriksaanForm.resepObat" :key="index" class="grid grid-cols-12 gap-2 mb-2 items-end">
                        <div class="col-span-4">
                            <label class="text-xs text-gray-500">Obat</label>
                            <select v-model="item.obat_id" class="w-full border rounded px-2 py-1.5 text-sm">
                                <option value="0">Pilih obat...</option>
                                <option v-for="obat in obats" :key="obat.id" :value="obat.id">
                                    {{ obat.nama_obat }} ({{ obat.satuan }})
                                </option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Jumlah</label>
                            <InputNumber v-model="item.jumlah" :min="1" class="w-full" />
                        </div>
                        <div class="col-span-3">
                            <label class="text-xs text-gray-500">Aturan Pakai</label>
                            <InputText v-model="item.aturan_pakai" placeholder="3x1" class="w-full" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Catatan</label>
                            <InputText v-model="item.catatan" placeholder="Sesudah makan" class="w-full" />
                        </div>
                        <div class="col-span-1">
                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="removeResepObat(index)" />
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" />
                <Button label="Simpan Pemeriksaan" icon="pi pi-check" @click="submitPemeriksaan" />
            </template>
        </Dialog>
    </AppLayout>
</template>

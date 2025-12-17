<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { RekamMedis } from '@/types';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Card from 'primevue/card';
import { useToast } from 'primevue/usetoast';

interface AntrianItem extends RekamMedis {
    pasien: {
        id: number;
        no_rm: string;
        nama: string;
        jenis_kelamin: string;
        tanggal_lahir: string;
        alamat: string;
    };
}

interface Props {
    antrian: AntrianItem[];
}

const props = defineProps<Props>();
const toast = useToast();

const showAnamnesisDialog = ref(false);
const selectedPasien = ref<AntrianItem | null>(null);

const anamnesisForm = ref({
    rekam_medis_id: 0,
    tekanan_darah_sistolik: null as number | null,
    tekanan_darah_diastolik: null as number | null,
    suhu_tubuh: null as number | null,
    nadi: null as number | null,
    pernapasan: null as number | null,
    tinggi_badan: null as number | null,
    berat_badan: null as number | null,
    riwayat_penyakit: '',
    riwayat_alergi: '',
    catatan: '',
});

const openAnamnesisDialog = (item: AntrianItem) => {
    selectedPasien.value = item;
    anamnesisForm.value.rekam_medis_id = item.id;
    showAnamnesisDialog.value = true;
};

const closeDialog = () => {
    showAnamnesisDialog.value = false;
    selectedPasien.value = null;
    resetForm();
};

const resetForm = () => {
    anamnesisForm.value = {
        rekam_medis_id: 0,
        tekanan_darah_sistolik: null,
        tekanan_darah_diastolik: null,
        suhu_tubuh: null,
        nadi: null,
        pernapasan: null,
        tinggi_badan: null,
        berat_badan: null,
        riwayat_penyakit: '',
        riwayat_alergi: '',
        catatan: '',
    };
};

const submitAnamnesis = () => {
    router.post(route('perawat.anamnesis.store'), anamnesisForm.value, {
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
    <Head title="Antrian Pasien" />
    <AppLayout>
        <template #header>Antrian Pasien - Anamnesis</template>

        <div class="space-y-4">
            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-list text-emerald-600"></i>
                        <span>Daftar Antrian Hari Ini</span>
                        <Tag :value="`${antrian.length} Pasien`" severity="info" class="ml-auto" />
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
                        emptyMessage="Tidak ada pasien dalam antrian"
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
                                        {{ data.pasien.no_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} |
                                        {{ getAge(data.pasien.tanggal_lahir) }} tahun
                                    </p>
                                </div>
                            </template>
                        </Column>
                        <Column field="keluhan_utama" header="Keluhan Utama">
                            <template #body="{ data }">
                                <span class="text-gray-700">{{ data.keluhan_utama || '-' }}</span>
                            </template>
                        </Column>
                        <Column header="Waktu Daftar" style="width: 140px">
                            <template #body="{ data }">
                                {{ new Date(data.tanggal_kunjungan).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 120px">
                            <template #body="{ data }">
                                <Button
                                    label="Anamnesis"
                                    icon="pi pi-pencil"
                                    size="small"
                                    @click="openAnamnesisDialog(data)"
                                />
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
            :style="{ width: '700px' }"
            :closable="true"
            @hide="closeDialog"
        >
            <div v-if="selectedPasien" class="space-y-4">
                <!-- Info Pasien -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Nama Pasien:</span>
                            <p class="font-medium">{{ selectedPasien.pasien.nama }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">No. RM:</span>
                            <p class="font-medium">{{ selectedPasien.pasien.no_rm }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Keluhan:</span>
                            <p class="font-medium">{{ selectedPasien.keluhan_utama || '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">No. Kunjungan:</span>
                            <p class="font-medium">{{ selectedPasien.no_kunjungan }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form Anamnesis -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Tekanan Darah (mmHg)</label>
                        <div class="flex items-center gap-2">
                            <InputNumber v-model="anamnesisForm.tekanan_darah_sistolik" placeholder="Sistolik" class="w-full" />
                            <span>/</span>
                            <InputNumber v-model="anamnesisForm.tekanan_darah_diastolik" placeholder="Diastolik" class="w-full" />
                        </div>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Suhu Tubuh (°C)</label>
                        <InputNumber v-model="anamnesisForm.suhu_tubuh" :minFractionDigits="1" :maxFractionDigits="1" placeholder="36.5" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Nadi (x/menit)</label>
                        <InputNumber v-model="anamnesisForm.nadi" placeholder="80" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Pernapasan (x/menit)</label>
                        <InputNumber v-model="anamnesisForm.pernapasan" placeholder="20" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Tinggi Badan (cm)</label>
                        <InputNumber v-model="anamnesisForm.tinggi_badan" placeholder="170" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Berat Badan (kg)</label>
                        <InputNumber v-model="anamnesisForm.berat_badan" :minFractionDigits="1" placeholder="65.0" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Riwayat Penyakit</label>
                    <Textarea v-model="anamnesisForm.riwayat_penyakit" rows="2" placeholder="Riwayat penyakit yang pernah diderita" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Riwayat Alergi</label>
                    <Textarea v-model="anamnesisForm.riwayat_alergi" rows="2" placeholder="Alergi obat, makanan, dll" />
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Catatan Tambahan</label>
                    <Textarea v-model="anamnesisForm.catatan" rows="2" placeholder="Catatan lainnya" />
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" />
                <Button label="Simpan & Lanjut ke Dokter" icon="pi pi-check" @click="submitAnamnesis" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
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
import { useToast } from 'primevue/usetoast';

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
}

const props = defineProps<Props>();
const toast = useToast();

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
                        <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 140px" />
                        <Column header="Pasien">
                            <template #body="{ data }">
                                <div>
                                    <p class="font-medium text-gray-900">{{ data.pasien.nama }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} |
                                        {{ getAge(data.pasien.tanggal_lahir) }} tahun
                                    </p>
                                </div>
                            </template>
                        </Column>
                        <Column field="catatan" header="Catatan">
                            <template #body="{ data }">
                                <span class="text-gray-700">{{ data.catatan || '-' }}</span>
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
                            <p class="font-medium">{{ selectedPasien.pasien.nomor_rm }}</p>
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
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Keluhan Utama <span class="text-red-500">*</span></label>
                    <Textarea
                        v-model="form.keluhan_utama"
                        rows="2"
                        placeholder="Keluhan yang dirasakan pasien saat ini"
                        :class="{ 'p-invalid': form.errors.keluhan_utama }"
                    />
                    <small v-if="form.errors.keluhan_utama" class="text-red-500">{{ form.errors.keluhan_utama }}</small>
                </div>

                <!-- Form Anamnesis - Vital Signs -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Tekanan Darah (mmHg)</label>
                        <div class="flex items-center gap-2">
                            <InputNumber
                                v-model="form.tekanan_darah_sistolik"
                                placeholder="Sistolik"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.tekanan_darah_sistolik }"
                            />
                            <span>/</span>
                            <InputNumber
                                v-model="form.tekanan_darah_diastolik"
                                placeholder="Diastolik"
                                class="w-full"
                                :class="{ 'p-invalid': form.errors.tekanan_darah_diastolik }"
                            />
                        </div>
                        <small v-if="form.errors.tekanan_darah_sistolik" class="text-red-500">{{ form.errors.tekanan_darah_sistolik }}</small>
                        <small v-if="form.errors.tekanan_darah_diastolik" class="text-red-500">{{ form.errors.tekanan_darah_diastolik }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Suhu Tubuh (°C)</label>
                        <InputNumber
                            v-model="form.suhu"
                            :minFractionDigits="1"
                            :maxFractionDigits="1"
                            placeholder="36.5"
                            :class="{ 'p-invalid': form.errors.suhu }"
                        />
                        <small v-if="form.errors.suhu" class="text-red-500">{{ form.errors.suhu }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Nadi (x/menit)</label>
                        <InputNumber
                            v-model="form.nadi"
                            placeholder="80"
                            :class="{ 'p-invalid': form.errors.nadi }"
                        />
                        <small v-if="form.errors.nadi" class="text-red-500">{{ form.errors.nadi }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Respirasi (x/menit)</label>
                        <InputNumber
                            v-model="form.respirasi"
                            placeholder="20"
                            :class="{ 'p-invalid': form.errors.respirasi }"
                        />
                        <small v-if="form.errors.respirasi" class="text-red-500">{{ form.errors.respirasi }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Tinggi Badan (cm)</label>
                        <InputNumber
                            v-model="form.tinggi_badan"
                            placeholder="170"
                            :class="{ 'p-invalid': form.errors.tinggi_badan }"
                        />
                        <small v-if="form.errors.tinggi_badan" class="text-red-500">{{ form.errors.tinggi_badan }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Berat Badan (kg)</label>
                        <InputNumber
                            v-model="form.berat_badan"
                            :minFractionDigits="1"
                            placeholder="65.0"
                            :class="{ 'p-invalid': form.errors.berat_badan }"
                        />
                        <small v-if="form.errors.berat_badan" class="text-red-500">{{ form.errors.berat_badan }}</small>
                    </div>
                </div>

                <!-- Riwayat -->
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Riwayat Penyakit Sekarang</label>
                    <Textarea
                        v-model="form.riwayat_penyakit_sekarang"
                        rows="2"
                        placeholder="Riwayat penyakit yang sedang dialami"
                        :class="{ 'p-invalid': form.errors.riwayat_penyakit_sekarang }"
                    />
                    <small v-if="form.errors.riwayat_penyakit_sekarang" class="text-red-500">{{ form.errors.riwayat_penyakit_sekarang }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Riwayat Penyakit Dahulu</label>
                    <Textarea
                        v-model="form.riwayat_penyakit_dahulu"
                        rows="2"
                        placeholder="Riwayat penyakit yang pernah diderita sebelumnya"
                        :class="{ 'p-invalid': form.errors.riwayat_penyakit_dahulu }"
                    />
                    <small v-if="form.errors.riwayat_penyakit_dahulu" class="text-red-500">{{ form.errors.riwayat_penyakit_dahulu }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Riwayat Alergi</label>
                    <Textarea
                        v-model="form.riwayat_alergi"
                        rows="2"
                        placeholder="Alergi obat, makanan, dll"
                        :class="{ 'p-invalid': form.errors.riwayat_alergi }"
                    />
                    <small v-if="form.errors.riwayat_alergi" class="text-red-500">{{ form.errors.riwayat_alergi }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Riwayat Obat</label>
                    <Textarea
                        v-model="form.riwayat_obat"
                        rows="2"
                        placeholder="Obat-obatan yang sedang dikonsumsi"
                        :class="{ 'p-invalid': form.errors.riwayat_obat }"
                    />
                    <small v-if="form.errors.riwayat_obat" class="text-red-500">{{ form.errors.riwayat_obat }}</small>
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
    </AppLayout>
</template>

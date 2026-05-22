<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Card from 'primevue/card';
import { useToast } from 'primevue/usetoast';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps<{
    draftPasiens: any[];
}>();

const toast = useToast();
const confirm = useConfirm();
const activeTab = ref('0');

const form = useForm({
    nik: '',
    nama: '',
    tanggal_lahir: null as Date | null,
    jenis_kelamin: '',
    alamat: '',
    phone: '',
    email: '',
    golongan_darah: '',
    status_pasien: '',
    nim_nip: '',
    fakultas: '',
    program_studi: '',
    pekerjaan: '',
    status_perkawinan: '',
    agama: '',
    pendidikan_terakhir: '',
});

const genderOptions = [
    { label: 'Laki-laki', value: 'L' },
    { label: 'Perempuan', value: 'P' },
];

const statusOptions = [
    { label: 'Mahasiswa', value: 'mahasiswa' },
    { label: 'Dosen', value: 'dosen' },
    { label: 'Tendik', value: 'tendik' },
    { label: 'Umum', value: 'umum' },
];

const golonganDarahOptions = [
    { label: 'A', value: 'A' },
    { label: 'B', value: 'B' },
    { label: 'AB', value: 'AB' },
    { label: 'O', value: 'O' },
];

const pekerjaanOptions = [
    { label: 'PNS', value: 'pns' },
    { label: 'PPPK', value: 'pppk' },
    { label: 'Swasta', value: 'swasta' },
    { label: 'Wiraswasta', value: 'wiraswasta' },
    { label: 'Pelajar/Mahasiswa', value: 'pelajar_mahasiswa' },
    { label: 'Lainnya', value: 'lainnya' },
];

const statusPerkawinanOptions = [
    { label: 'Belum Kawin', value: 'belum_kawin' },
    { label: 'Kawin', value: 'kawin' },
    { label: 'Cerai Hidup', value: 'cerai_hidup' },
    { label: 'Cerai Mati', value: 'cerai_mati' },
];

const agamaOptions = [
    { label: 'Islam', value: 'islam' },
    { label: 'Kristen', value: 'kristen' },
    { label: 'Katolik', value: 'katolik' },
    { label: 'Hindu', value: 'hindu' },
    { label: 'Buddha', value: 'buddha' },
    { label: 'Konghucu', value: 'konghucu' },
    { label: 'Lainnya', value: 'lainnya' },
];

const pendidikanOptions = [
    { label: 'SD', value: 'sd' },
    { label: 'SMP', value: 'smp' },
    { label: 'SMA/SMK', value: 'sma_smk' },
    { label: 'D1', value: 'd1' },
    { label: 'D2', value: 'd2' },
    { label: 'D3', value: 'd3' },
    { label: 'D4/S1', value: 'd4_s1' },
    { label: 'S2', value: 's2' },
    { label: 'S3', value: 's3' },
];

const submit = () => {
    form.transform((data) => ({
        ...data,
        tanggal_lahir: data.tanggal_lahir ? data.tanggal_lahir.toISOString().split('T')[0] : null,
    })).post(route('pasien.store'), {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pasien berhasil didaftarkan ke draf', life: 3000 });
            form.reset();
            activeTab.value = '1'; // Pindah ke tab 2 setelah simpan
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Gagal', detail: 'Periksa kembali field yang ditandai merah', life: 5000 });
        }
    });
};

const activatePasien = (pasien: any) => {
    confirm.require({
        message: `Apakah Anda yakin ingin memasukkan pasien "${pasien.nama}" ke dalam Daftar Pasien utama?`,
        header: 'Konfirmasi',
        icon: 'pi pi-info-circle',
        acceptClass: 'p-button-success',
        accept: () => {
            router.post(route('pasien.activate', pasien.id), {}, {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pasien ditambahkan ke Daftar Utama', life: 3000 });
                }
            });
        }
    });
};

const cetakPdf = (pasien: any) => {
    window.open(route('pasien.draf.pdf', pasien.id), '_blank');
};
</script>

<template>
    <Head title="Registrasi Pasien" />
    <AppLayout>
        <template #header>Registrasi Pasien Baru</template>

        <div class="max-w-5xl mx-auto space-y-6">
            <Tabs v-model:value="activeTab" class="bg-transparent">
                <TabList class="!bg-white/80 !backdrop-blur-md !border-b !border-gray-200 !rounded-t-xl overflow-hidden shadow-sm sticky top-0 z-10">
                    <Tab value="0" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === '0' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-user-plus text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === '0' ? 'text-emerald-700' : 'text-gray-500'">Form Registrasi</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">Input Data Pasien</span>
                            </div>
                        </div>
                    </Tab>
                    <Tab value="1" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === '1' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-folder-open text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === '1' ? 'text-blue-700' : 'text-gray-500'">Pasien Tersimpan</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">{{ draftPasiens?.length || 0 }} Draft Pasien</span>
                            </div>
                        </div>
                    </Tab>
                </TabList>

                <TabPanels class="!bg-transparent !p-0 !mt-4">
                    <TabPanel value="0" class="!p-0">
                        <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                            <template #content>
                                <form @submit.prevent="submit" class="space-y-6 p-2">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- NIK -->
                                        <div class="flex flex-col gap-2">
                                            <label for="nik" class="font-medium text-gray-700">NIK <span class="text-red-500">*</span></label>
                                            <InputText
                                                id="nik"
                                                v-model="form.nik"
                                                placeholder="Masukkan NIK (16 digit)"
                                                maxlength="16"
                                                :class="{ 'p-invalid': form.errors.nik }"
                                            />
                                            <small v-if="form.errors.nik" class="text-red-500">{{ form.errors.nik }}</small>
                                        </div>

                                        <!-- Nama -->
                                        <div class="flex flex-col gap-2">
                                            <label for="nama" class="font-medium text-gray-700">Nama Lengkap <span class="text-red-500">*</span></label>
                                            <InputText
                                                id="nama"
                                                v-model="form.nama"
                                                placeholder="Masukkan nama lengkap"
                                                :class="{ 'p-invalid': form.errors.nama }"
                                            />
                                            <small v-if="form.errors.nama" class="text-red-500">{{ form.errors.nama }}</small>
                                        </div>

                                        <!-- Tanggal Lahir -->
                                        <div class="flex flex-col gap-2">
                                            <label for="tanggal_lahir" class="font-medium text-gray-700">Tanggal Lahir <span class="text-red-500">*</span></label>
                                            <DatePicker
                                                id="tanggal_lahir"
                                                v-model="form.tanggal_lahir"
                                                dateFormat="dd/mm/yy"
                                                placeholder="Pilih tanggal lahir"
                                                showIcon
                                                :class="{ 'p-invalid': form.errors.tanggal_lahir }"
                                            />
                                            <small v-if="form.errors.tanggal_lahir" class="text-red-500">{{ form.errors.tanggal_lahir }}</small>
                                        </div>

                                        <!-- Jenis Kelamin -->
                                        <div class="flex flex-col gap-2">
                                            <label for="jenis_kelamin" class="font-medium text-gray-700">Jenis Kelamin <span class="text-red-500">*</span></label>
                                            <Select
                                                id="jenis_kelamin"
                                                v-model="form.jenis_kelamin"
                                                :options="genderOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih jenis kelamin"
                                                :class="{ 'p-invalid': form.errors.jenis_kelamin }"
                                            />
                                            <small v-if="form.errors.jenis_kelamin" class="text-red-500">{{ form.errors.jenis_kelamin }}</small>
                                        </div>

                                        <!-- Status Pasien -->
                                        <div class="flex flex-col gap-2">
                                            <label for="status_pasien" class="font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                                            <Select
                                                id="status_pasien"
                                                v-model="form.status_pasien"
                                                :options="statusOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih status pasien"
                                                :class="{ 'p-invalid': form.errors.status_pasien }"
                                            />
                                            <small v-if="form.errors.status_pasien" class="text-red-500">{{ form.errors.status_pasien }}</small>
                                        </div>

                                        <!-- Golongan Darah -->
                                        <div class="flex flex-col gap-2">
                                            <label for="golongan_darah" class="font-medium text-gray-700">Golongan Darah</label>
                                            <Select
                                                id="golongan_darah"
                                                v-model="form.golongan_darah"
                                                :options="golonganDarahOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih golongan darah"
                                                :class="{ 'p-invalid': form.errors.golongan_darah }"
                                            />
                                            <small v-if="form.errors.golongan_darah" class="text-red-500">{{ form.errors.golongan_darah }}</small>
                                        </div>

                                        <!-- NIM/NIP -->
                                        <div class="flex flex-col gap-2">
                                            <label for="nim_nip" class="font-medium text-gray-700">NIM/NIP</label>
                                            <InputText
                                                id="nim_nip"
                                                v-model="form.nim_nip"
                                                placeholder="Masukkan NIM atau NIP"
                                                :class="{ 'p-invalid': form.errors.nim_nip }"
                                            />
                                            <small v-if="form.errors.nim_nip" class="text-red-500">{{ form.errors.nim_nip }}</small>
                                        </div>

                                        <!-- Fakultas -->
                                        <div class="flex flex-col gap-2">
                                            <label for="fakultas" class="font-medium text-gray-700">Fakultas</label>
                                            <InputText
                                                id="fakultas"
                                                v-model="form.fakultas"
                                                placeholder="Masukkan nama fakultas"
                                                :class="{ 'p-invalid': form.errors.fakultas }"
                                            />
                                            <small v-if="form.errors.fakultas" class="text-red-500">{{ form.errors.fakultas }}</small>
                                        </div>

                                        <!-- Program Studi -->
                                        <div class="flex flex-col gap-2">
                                            <label for="program_studi" class="font-medium text-gray-700">Program Studi</label>
                                            <InputText
                                                id="program_studi"
                                                v-model="form.program_studi"
                                                placeholder="Masukkan program studi"
                                                :class="{ 'p-invalid': form.errors.program_studi }"
                                            />
                                            <small v-if="form.errors.program_studi" class="text-red-500">{{ form.errors.program_studi }}</small>
                                        </div>

                                        <!-- Pekerjaan -->
                                        <div class="flex flex-col gap-2">
                                            <label for="pekerjaan" class="font-medium text-gray-700">Pekerjaan</label>
                                            <Select
                                                id="pekerjaan"
                                                v-model="form.pekerjaan"
                                                :options="pekerjaanOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih pekerjaan"
                                                :class="{ 'p-invalid': form.errors.pekerjaan }"
                                            />
                                            <small v-if="form.errors.pekerjaan" class="text-red-500">{{ form.errors.pekerjaan }}</small>
                                        </div>

                                        <!-- Status Perkawinan -->
                                        <div class="flex flex-col gap-2">
                                            <label for="status_perkawinan" class="font-medium text-gray-700">Status Perkawinan</label>
                                            <Select
                                                id="status_perkawinan"
                                                v-model="form.status_perkawinan"
                                                :options="statusPerkawinanOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih status perkawinan"
                                                :class="{ 'p-invalid': form.errors.status_perkawinan }"
                                            />
                                            <small v-if="form.errors.status_perkawinan" class="text-red-500">{{ form.errors.status_perkawinan }}</small>
                                        </div>

                                        <!-- Agama -->
                                        <div class="flex flex-col gap-2">
                                            <label for="agama" class="font-medium text-gray-700">Agama</label>
                                            <Select
                                                id="agama"
                                                v-model="form.agama"
                                                :options="agamaOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih agama"
                                                :class="{ 'p-invalid': form.errors.agama }"
                                            />
                                            <small v-if="form.errors.agama" class="text-red-500">{{ form.errors.agama }}</small>
                                        </div>

                                        <!-- Pendidikan Terakhir -->
                                        <div class="flex flex-col gap-2">
                                            <label for="pendidikan_terakhir" class="font-medium text-gray-700">Pendidikan Terakhir</label>
                                            <Select
                                                id="pendidikan_terakhir"
                                                v-model="form.pendidikan_terakhir"
                                                :options="pendidikanOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih pendidikan terakhir"
                                                :class="{ 'p-invalid': form.errors.pendidikan_terakhir }"
                                            />
                                            <small v-if="form.errors.pendidikan_terakhir" class="text-red-500">{{ form.errors.pendidikan_terakhir }}</small>
                                        </div>

                                        <!-- Telepon -->
                                        <div class="flex flex-col gap-2">
                                            <label for="phone" class="font-medium text-gray-700">No. Telepon</label>
                                            <InputText
                                                id="phone"
                                                v-model="form.phone"
                                                placeholder="Masukkan nomor telepon"
                                                :class="{ 'p-invalid': form.errors.phone }"
                                            />
                                            <small v-if="form.errors.phone" class="text-red-500">{{ form.errors.phone }}</small>
                                        </div>

                                        <!-- Email -->
                                        <div class="flex flex-col gap-2 md:col-span-2">
                                            <label for="email" class="font-medium text-gray-700">Email</label>
                                            <InputText
                                                id="email"
                                                v-model="form.email"
                                                type="email"
                                                placeholder="Masukkan alamat email"
                                                :class="{ 'p-invalid': form.errors.email }"
                                            />
                                            <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                                        </div>

                                        <!-- Alamat -->
                                        <div class="flex flex-col gap-2 md:col-span-2">
                                            <label for="alamat" class="font-medium text-gray-700">Alamat <span class="text-red-500">*</span></label>
                                            <Textarea
                                                id="alamat"
                                                v-model="form.alamat"
                                                rows="3"
                                                placeholder="Masukkan alamat lengkap"
                                                :class="{ 'p-invalid': form.errors.alamat }"
                                            />
                                            <small v-if="form.errors.alamat" class="text-red-500">{{ form.errors.alamat }}</small>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-3 pt-4 border-t">
                                        <Button
                                            type="button"
                                            label="Batal"
                                            severity="secondary"
                                            @click="router.visit(route('pasien.index'))"
                                        />
                                        <Button
                                            type="submit"
                                            label="Simpan"
                                            icon="pi pi-check"
                                            :loading="form.processing"
                                        />
                                    </div>
                                </form>
                            </template>
                        </Card>
                    </TabPanel>

                    <TabPanel value="1" class="!p-0">
                        <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                            <template #content>
                                <div class="mb-6 p-2">
                                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-2">
                                        <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                                        Daftar Pasien Tersimpan (Draft)
                                    </h3>
                                    <p class="text-xs text-gray-500">
                                        Pasien di daftar ini belum masuk ke Daftar Pasien Utama. Klik "Daftarkan" untuk meresmikan.
                                    </p>
                                </div>
                                <DataTable
                                    :value="draftPasiens"
                                    :paginator="draftPasiens && draftPasiens.length > 10"
                                    :rows="10"
                                    dataKey="id"
                                    responsiveLayout="scroll"
                                    class="p-datatable-sm"
                                    emptyMessage="Tidak ada registrasi pasien yang disimpan."
                                >
                                    <Column header="No" style="width: 60px">
                                        <template #body="{ index }">
                                            {{ index + 1 }}
                                        </template>
                                    </Column>
                                    <Column field="nomor_rm" header="No. RM" style="width: 120px">
                                        <template #body="{ data }">
                                            <span class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">{{ data.nomor_rm }}</span>
                                        </template>
                                    </Column>
                                    <Column field="nama" header="Nama Pasien">
                                        <template #body="{ data }">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ data.nama }}</p>
                                                <p class="text-[11px] text-gray-500">{{ data.nik || '-' }}</p>
                                            </div>
                                        </template>
                                    </Column>
                                    <Column field="tipe_pasien" header="Tipe Pasien" style="width: 120px">
                                        <template #body="{ data }">
                                            <Tag :value="data.tipe_pasien" class="!text-[10px] !px-2 uppercase" />
                                        </template>
                                    </Column>
                                    <Column header="Waktu Didaftarkan" style="width: 150px">
                                        <template #body="{ data }">
                                            <span class="text-xs text-gray-600">
                                                {{ new Date(data.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Aksi" style="width: 150px" class="text-center">
                                        <template #body="{ data }">
                                            <div class="flex items-center justify-center gap-1">
                                                <Button
                                                    label="Daftarkan"
                                                    icon="pi pi-arrow-right"
                                                    severity="success"
                                                    class="!rounded-xl !text-[11px] !py-2 !px-3 shadow-sm hover:shadow-md transition-all font-bold"
                                                    @click="activatePasien(data)"
                                                    v-tooltip.top="'Pindahkan ke Daftar Pasien Utama'"
                                                />
                                                <Button
                                                    icon="pi pi-print"
                                                    severity="secondary"
                                                    class="!rounded-xl !text-[11px] !py-2 !px-3 shadow-sm hover:shadow-md transition-all font-bold"
                                                    @click="cetakPdf(data)"
                                                    v-tooltip.top="'Cetak PDF Registrasi'"
                                                />
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </template>
                        </Card>
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </div>
    </AppLayout>
</template>

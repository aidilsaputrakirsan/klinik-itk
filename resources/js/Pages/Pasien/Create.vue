<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

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
    consent: false,
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
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pasien berhasil didaftarkan', life: 3000 });
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Gagal', detail: 'Periksa kembali field yang ditandai merah', life: 5000 });
        }
    });
};
</script>

<template>
    <Head title="Registrasi Pasien" />
    <AppLayout>
        <template #header>Registrasi Pasien Baru</template>

        <div class="max-w-4xl mx-auto">
            <Card class="shadow-sm">
                <template #content>
                    <form @submit.prevent="submit" class="space-y-6">
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

                            <!-- Consent Checkbox -->
                            <div class="flex flex-col gap-2 md:col-span-2">
                                <div class="flex items-center gap-2 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <Checkbox
                                        v-model="form.consent"
                                        inputId="consent"
                                        :binary="true"
                                        :class="{ 'p-invalid': form.errors.consent }"
                                    />
                                    <label for="consent" class="cursor-pointer text-gray-700">
                                        Pasien menyetujui pengumpulan data untuk keperluan medis <span class="text-red-500">*</span>
                                    </label>
                                </div>
                                <small v-if="form.errors.consent" class="text-red-500">{{ form.errors.consent }}</small>
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
                                label="Simpan & Daftarkan Kunjungan"
                                icon="pi pi-check"
                                :loading="form.processing"
                            />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>

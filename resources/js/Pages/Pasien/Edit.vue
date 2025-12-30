<script setup lang="ts">
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Pasien } from '@/types';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Card from 'primevue/card';
import { useToast } from 'primevue/usetoast';

interface Props {
    pasien: Pasien;
}

const props = defineProps<Props>();
const toast = useToast();

const form = useForm({
    nik: props.pasien.nik || '',
    nama: props.pasien.nama,
    tanggal_lahir: props.pasien.tanggal_lahir ? new Date(props.pasien.tanggal_lahir) : null,
    jenis_kelamin: props.pasien.jenis_kelamin,
    alamat: props.pasien.alamat || '',
    phone: props.pasien.phone || '',
    email: props.pasien.email || '',
    golongan_darah: props.pasien.golongan_darah || '',
    status_pasien: props.pasien.tipe_pasien,
    nim_nip: props.pasien.nomor_identitas || '',
    fakultas: props.pasien.fakultas || '',
    program_studi: props.pasien.prodi || '',
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

const submit = () => {
    form.transform((data) => ({
        ...data,
        tanggal_lahir: data.tanggal_lahir ? data.tanggal_lahir.toISOString().split('T')[0] : null,
    })).put(route('pasien.update', props.pasien.id), {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Data pasien berhasil diperbarui', life: 3000 });
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Gagal', detail: 'Periksa kembali field yang ditandai merah', life: 5000 });
        }
    });
};
</script>

<template>
    <Head :title="`Edit Pasien - ${pasien.nama}`" />
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('pasien.index')">
                    <Button icon="pi pi-arrow-left" text rounded />
                </Link>
                <span>Edit Pasien</span>
            </div>
        </template>

        <div class="max-w-4xl mx-auto">
            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <span>Edit Data: {{ pasien.nama }}</span>
                        <span class="text-sm font-normal text-gray-500">({{ pasien.nomor_rm }})</span>
                    </div>
                </template>
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
                            <Link :href="route('pasien.index')">
                                <Button
                                    type="button"
                                    label="Batal"
                                    severity="secondary"
                                />
                            </Link>
                            <Button
                                type="submit"
                                label="Simpan Perubahan"
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

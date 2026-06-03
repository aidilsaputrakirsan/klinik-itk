<script setup lang="ts">
import { ref, computed } from 'vue'; // HMR trigger
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { User } from '@/types';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import Password from 'primevue/password';
import ToggleSwitch from 'primevue/toggleswitch';
import Card from 'primevue/card';
import { useToast } from 'primevue/usetoast';
import Swal from 'sweetalert2';

interface Props {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        role?: string;
    };
}

const props = defineProps<Props>();
const toast = useToast();

const search = ref(props.filters.search || '');
const showDialog = ref(false);
const isEdit = ref(false);
const selectedUser = ref<User | null>(null);

const form = ref({
    name: '',
    email: '',
    role: '',
    password: '',
    nip: '',
    phone: '',
    specialization: '',
    is_active: true,
});

const formErrors = ref<Record<string, string>>({});

const getRoleBadgeClass = (role: string) => {
    const classes: Record<string, string> = {
        superadmin: 'bg-purple-50 text-purple-600',
        admin: 'bg-blue-50 text-blue-600',
        perawat: 'bg-emerald-50 text-emerald-600',
        dokter: 'bg-amber-50 text-amber-600'
    };
    return classes[role] || 'bg-gray-50 text-gray-600';
};

const getRoleLabel = (role: string) => {
    const labels: Record<string, string> = {
        superadmin: 'Super Admin',
        admin: 'Admin',
        perawat: 'Perawat',
        dokter: 'Dokter'
    };
    return labels[role] || role;
};

const roleOptions = [
    { label: 'Super Admin', value: 'superadmin' },
    { label: 'Admin', value: 'admin' },
    { label: 'Perawat', value: 'perawat' },
    { label: 'Dokter', value: 'dokter' },
];

const showSpecialization = computed(() => form.value.role === 'dokter');

const doSearch = () => {
    router.get(route('users.index'), { search: search.value }, { preserveState: true });
};

const openCreateDialog = () => {
    isEdit.value = false;
    resetForm();
    showDialog.value = true;
};

const openEditDialog = (user: User) => {
    isEdit.value = true;
    selectedUser.value = user;
    form.value = {
        name: user.name,
        email: user.email,
        role: user.role,
        password: '',
        nip: user.nip || '',
        phone: user.phone || '',
        specialization: user.specialization || '',
        is_active: user.is_active,
    };
    showDialog.value = true;
};

const resetForm = () => {
    form.value = {
        name: '',
        email: '',
        role: '',
        password: '',
        nip: '',
        phone: '',
        specialization: '',
        is_active: true,
    };
    formErrors.value = {};
    selectedUser.value = null;
};

const closeDialog = () => {
    showDialog.value = false;
    resetForm();
};

const submitForm = () => {
    formErrors.value = {};

    if (isEdit.value && selectedUser.value) {
        router.put(route('users.update', selectedUser.value.id), form.value, {
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pengguna berhasil diperbarui', life: 3000 });
                closeDialog();
            },
            onError: (errors) => {
                formErrors.value = errors;
            }
        });
    } else {
        router.post(route('users.store'), form.value, {
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pengguna berhasil ditambahkan', life: 3000 });
                closeDialog();
            },
            onError: (errors) => {
                formErrors.value = errors;
            }
        });
    }
};

const deleteUser = (user: User) => {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus pengguna "${user.name}"?`,
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
            router.delete(route('users.destroy', user.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Pengguna berhasil dihapus', life: 3000 });
                },
                onError: () => {
                    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Tidak dapat menghapus pengguna', life: 3000 });
                }
            });
        }
    });
};

const toggleUserActive = (user: User) => {
    const action = user.is_active ? 'menonaktifkan' : 'mengaktifkan';
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin ${action} pengguna "${user.name}"?`,
        icon: 'question',
        showCancelButton: true,
        buttonsStyling: false,
        background: '#ffffff',
        customClass: {
            popup: 'rounded-3xl shadow-2xl border border-gray-100',
            title: 'text-2xl font-bold text-gray-900',
            htmlContainer: 'text-gray-500 text-sm mt-2',
            actions: 'flex gap-3 mt-6',
            confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl px-6 py-2.5 font-semibold transition-all shadow-md hover:shadow-lg',
            cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl px-6 py-2.5 font-semibold transition-all border border-gray-200'
        },
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('users.toggle-active', user.id), {}, {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: `Pengguna berhasil di${action.replace('meng', '')}`, life: 3000 });
                },
                onError: () => {
                    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Tidak dapat mengubah status pengguna', life: 3000 });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Kelola Pengguna" />
    <AppLayout>
        <template #header>
            Kelola Pengguna
        </template>

        <div class="space-y-4 font-sans">
            <Card class="shadow-sm border border-gray-100 overflow-hidden !p-0">
                <template #content>
                    <div class="p-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center">
                            <InputText
                                v-model="search"
                                placeholder="Cari pengguna..."
                                class="w-full sm:w-64 md:w-80 !rounded-r-none !border-r-0 border-gray-300 focus:ring-0 focus:border-gray-300"
                                @keyup.enter="doSearch"
                            />
                            <Button icon="pi pi-search" @click="doSearch" class="bg-emerald-600 hover:bg-emerald-700 border-emerald-600 hover:border-emerald-700 text-white w-10 h-[42px] !rounded-l-none p-0 flex justify-center items-center" />
                        </div>
                        <Button label="Tambah Pengguna" icon="pi pi-plus" @click="openCreateDialog" class="bg-emerald-600 hover:bg-emerald-700 border-emerald-600 hover:border-emerald-700 text-white px-4 h-[42px] font-semibold" />
                    </div>

                    <DataTable :value="users.data" dataKey="id" responsiveLayout="scroll" class="p-datatable-sm" stripedRows>
                    <Column field="name" header="Nama" />
                    <Column field="email" header="Email" />
                    <Column field="nip" header="NIP / SIP">
                        <template #body="{ data }">
                            <span class="text-gray-600">{{ data.nip || '-' }}</span>
                        </template>
                    </Column>
                    <Column field="role" header="Role">
                        <template #body="{ data }">
                            <span :class="['px-2.5 py-1 rounded-md text-xs font-bold', getRoleBadgeClass(data.role)]">
                                {{ getRoleLabel(data.role) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="is_active" header="Status" style="width: 100px">
                        <template #body="{ data }">
                            <div v-if="data.is_active" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-600">Aktif</div>
                            <div v-else class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-gray-50 text-gray-500">Nonaktif</div>
                        </template>
                    </Column>
                    <Column header="Aksi" style="width: 150px">
                        <template #body="{ data }">
                            <div class="flex items-center gap-1">
                                <Button
                                    :icon="data.is_active ? 'pi pi-ban' : 'pi pi-check'"
                                    :class="data.is_active ? 'text-gray-400 hover:text-gray-600 hover:bg-gray-100' : 'text-emerald-500 hover:text-emerald-600 hover:bg-emerald-50'"
                                    text
                                    rounded
                                    @click="toggleUserActive(data)"
                                    v-tooltip.top="data.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                />
                                <Button icon="pi pi-pencil" class="text-orange-400 hover:text-orange-500 hover:bg-orange-50" text rounded @click="openEditDialog(data)" />
                                <Button icon="pi pi-trash" class="text-rose-400 hover:text-rose-500 hover:bg-rose-50" text rounded @click="deleteUser(data)" />
                            </div>
                        </template>
                    </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Dialog Form -->
        <Dialog
            v-model:visible="showDialog"
            modal
            :header="isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru'"
            :style="{ width: '500px' }"
        >
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Nama Lengkap <span class="text-red-500">*</span></label>
                    <InputText v-model="form.name" placeholder="Nama lengkap" :class="{ 'p-invalid': formErrors.name }" />
                    <small v-if="formErrors.name" class="text-red-500">{{ formErrors.name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Email <span class="text-red-500">*</span></label>
                    <InputText v-model="form.email" type="email" placeholder="email@klinik-itk.ac.id" :class="{ 'p-invalid': formErrors.email }" />
                    <small v-if="formErrors.email" class="text-red-500">{{ formErrors.email }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Role <span class="text-red-500">*</span></label>
                    <Select
                        v-model="form.role"
                        :options="roleOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Pilih role"
                        :class="{ 'p-invalid': formErrors.role }"
                    />
                    <small v-if="formErrors.role" class="text-red-500">{{ formErrors.role }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">
                        Password
                        <span v-if="!isEdit" class="text-red-500">*</span>
                        <span v-else class="text-gray-400 text-xs">(kosongkan jika tidak ingin mengubah)</span>
                    </label>
                    <Password
                        v-model="form.password"
                        :feedback="false"
                        toggleMask
                        placeholder="Password"
                        :class="{ 'p-invalid': formErrors.password }"
                        inputClass="w-full"
                    />
                    <small v-if="formErrors.password" class="text-red-500">{{ formErrors.password }}</small>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">{{ form.role === 'dokter' ? 'SIP' : 'NIP' }}</label>
                        <InputText v-model="form.nip" :placeholder="form.role === 'dokter' ? 'Surat Izin Praktik' : 'Nomor Induk Pegawai'" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">No. Telepon</label>
                        <InputText v-model="form.phone" placeholder="08xxxxxxxxxx" />
                    </div>
                </div>
                <div v-if="showSpecialization" class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Spesialisasi</label>
                    <InputText v-model="form.specialization" placeholder="Spesialisasi dokter" />
                </div>
                <div class="flex items-center gap-2">
                    <ToggleSwitch v-model="form.is_active" />
                    <label class="font-medium text-sm">Pengguna Aktif</label>
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" />
                <Button :label="isEdit ? 'Simpan' : 'Tambah'" icon="pi pi-check" @click="submitForm" />
            </template>
        </Dialog>
    </AppLayout>
</template>

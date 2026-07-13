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
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
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

const filterRole = ref(props.filters.role || '');

const filterRoleOptions = [
    { label: 'Semua Role', value: '' },
    { label: 'Super Admin', value: 'superadmin' },
    { label: 'Admin', value: 'admin' },
    { label: 'Perawat', value: 'perawat' },
    { label: 'Dokter', value: 'dokter' },
];

const doSearch = () => {
    router.get(route('users.index'), { 
        search: search.value, 
        role: filterRole.value || undefined 
    }, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    filterRole.value = '';
    doSearch();
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
            <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                <template #content>
                    <!-- Filter & Actions -->
                    <div class="mb-6 space-y-4">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                                Daftar Pengguna Sistem
                            </h3>
                            <Button 
                                label="Tambah Pengguna" 
                                icon="pi pi-plus" 
                                severity="success" 
                                class="!rounded-xl !text-xs font-bold shadow-sm"
                                @click="openCreateDialog" 
                            />
                        </div>

                        <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-xl shadow-sm space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
                                <!-- Field: Cari Pengguna -->
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Pengguna</span>
                                    <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                                        <InputGroupAddon class="!bg-white !border-0 !px-3">
                                            <i class="pi pi-search text-emerald-500 text-[10px]"></i>
                                        </InputGroupAddon>
                                        <InputText
                                            v-model="search"
                                            placeholder="Nama, email, atau NIP..."
                                            class="!border-0 !text-xs !py-2 !pl-0 focus:!ring-0 placeholder:text-gray-300"
                                            @keyup.enter="doSearch"
                                        />
                                    </InputGroup>
                                </div>

                                <!-- Field: Role -->
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Role</span>
                                    <div class="flex gap-2 items-center">
                                        <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all flex-1">
                                            <InputGroupAddon class="!bg-white !border-0 !px-3">
                                                <i class="pi pi-users text-emerald-500 text-[10px]"></i>
                                            </InputGroupAddon>
                                            <Select
                                                v-model="filterRole"
                                                :options="filterRoleOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih Role"
                                                class="!border-0 !text-xs !py-0 focus:!ring-0 flex-1"
                                                :pt="{
                                                    root: { class: '!border-0 !shadow-none' },
                                                    label: { class: '!text-xs !py-2 !pl-0' },
                                                    dropdownIcon: { class: '!w-3 !h-3 text-emerald-500' }
                                                }"
                                                @change="doSearch"
                                            />
                                        </InputGroup>
                                        
                                        <Button 
                                            icon="pi pi-filter-slash" 
                                            severity="secondary" 
                                            text 
                                            v-tooltip.top="'Bersihkan Filter'"
                                            class="!rounded-xl !p-2 !h-9 !w-9 flex items-center justify-center border border-gray-200 hover:bg-gray-100 bg-white shrink-0"
                                            @click="clearFilters" 
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
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

<script setup lang="ts">
import { ref, computed } from 'vue';
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
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

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
const confirm = useConfirm();
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
        superadmin: 'bg-purple-100 text-purple-800',
        admin: 'bg-blue-100 text-blue-800',
        perawat: 'bg-green-100 text-green-800',
        dokter: 'bg-amber-100 text-amber-800'
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
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
    confirm.require({
        message: `Apakah Anda yakin ingin menghapus pengguna "${user.name}"?`,
        header: 'Konfirmasi Hapus',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
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
    confirm.require({
        message: `Apakah Anda yakin ingin ${action} pengguna "${user.name}"?`,
        header: 'Konfirmasi',
        icon: 'pi pi-question-circle',
        accept: () => {
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
        <template #header>Kelola Pengguna</template>

        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="p-input-icon-left w-full sm:w-80">
                        <i class="pi pi-search" />
                        <InputText
                            v-model="search"
                            placeholder="Cari pengguna..."
                            class="w-full"
                            @keyup.enter="doSearch"
                        />
                    </span>
                    <Button icon="pi pi-search" @click="doSearch" />
                </div>
                <Button label="Tambah Pengguna" icon="pi pi-plus" @click="openCreateDialog" />
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <DataTable :value="users.data" dataKey="id" responsiveLayout="scroll" class="p-datatable-sm">
                    <Column field="name" header="Nama" />
                    <Column field="email" header="Email" />
                    <Column field="nip" header="NIP">
                        <template #body="{ data }">
                            <span class="text-gray-600">{{ data.nip || '-' }}</span>
                        </template>
                    </Column>
                    <Column field="role" header="Role">
                        <template #body="{ data }">
                            <span :class="['px-2 py-1 rounded text-xs font-medium', getRoleBadgeClass(data.role)]">
                                {{ getRoleLabel(data.role) }}
                            </span>
                        </template>
                    </Column>
                    <Column field="is_active" header="Status" style="width: 100px">
                        <template #body="{ data }">
                            <Tag :value="data.is_active ? 'Aktif' : 'Nonaktif'" :severity="data.is_active ? 'success' : 'secondary'" />
                        </template>
                    </Column>
                    <Column header="Aksi" style="width: 150px">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Button
                                    :icon="data.is_active ? 'pi pi-ban' : 'pi pi-check'"
                                    :severity="data.is_active ? 'secondary' : 'success'"
                                    text
                                    rounded
                                    size="small"
                                    @click="toggleUserActive(data)"
                                    v-tooltip.top="data.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                                />
                                <Button icon="pi pi-pencil" severity="warn" text rounded size="small" @click="openEditDialog(data)" />
                                <Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="deleteUser(data)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>
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
                        <label class="font-medium text-sm">NIP</label>
                        <InputText v-model="form.nip" placeholder="Nomor Induk Pegawai" />
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

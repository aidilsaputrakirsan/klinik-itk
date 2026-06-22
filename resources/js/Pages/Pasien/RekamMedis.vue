<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Pasien, RekamMedis } from '@/types';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker';
import FileUpload from 'primevue/fileupload';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import { usePage } from '@inertiajs/vue3';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Swal from 'sweetalert2';

interface ActionItem { id: number; nama: string; biaya?: number; pivot?: any }
interface ObatItem { id: number; nama: string; satuan?: string }

interface ResepObatItem {
    id: number;
    nama_obat: string;
    jumlah: number;
    satuan: string;
    dosis: string | null;
    aturan_pakai: string | null;
    keterangan: string | null;
    obat?: ObatItem;
}

interface PemeriksaanData {
    id: number;
    pemeriksaan_fisik: string | null;
    hasil_pemeriksaan: string | null;
    diagnosis_utama: string;
    diagnosis_sekunder: string | null;
    kode_icd10: string | null;
    prognosis: string | null;
    anjuran: string | null;
    penatalaksanaan_medis: string | null;
    dokter_id?: number | null;
    dokter?: { name: string; };
    resep_obats?: ResepObatItem[];
    tindakans?: ActionItem[];
}

interface AnamnesisData {
    tekanan_darah: string | null;
    suhu: number | null;
    nadi: number | null;
    respirasi: number | null;
    tinggi_badan: number | null;
    berat_badan: number | null;
    skala_nyeri: number | null;
    keluhan_utama: string;
    riwayat_penyakit_sekarang: string | null;
    riwayat_penyakit_dahulu: string | null;
    riwayat_alergi: string | null;
    riwayat_obat: string | null;
    riwayat_keluarga: string | null;
    diagnosa_keperawatan: string | null;
    intervensi_keperawatan: string | null;
    implementasi_keperawatan: string | null;
    evaluasi_keperawatan: string | null;
    lingkar_perut: number | null;
    is_hamil: boolean;
    is_menyusui: boolean;
    tindak_lanjut: string | null;
    keterangan_tindak_lanjut: string | null;
    gula_darah?: number | null;
    jenis_gula_darah?: 'puasa' | 'sewaktu' | null;
    asam_urat?: number | null;
    kolesterol?: number | null;
    hemoglobin?: number | null;
    bmi?: number;
    perawat_id?: number | null;
    perawat?: { name: string; };
}

interface RekamMedisWithDetails extends Omit<RekamMedis, 'anamnesis' | 'pemeriksaan'> {
    anamnesis?: AnamnesisData;
    pemeriksaan?: PemeriksaanData;
}

interface UserProp { id: number; name: string; }

interface Props {
    pasien: Pasien & { rekam_medis?: RekamMedisWithDetails[] };
    obats: ObatItem[];
    tindakans: ActionItem[];
    perawatList: UserProp[];
    dokterList: UserProp[];
}

const props = defineProps<Props>();
const toast = useToast();
const page = usePage();

const showDetailDialog = ref(false);
const showKunjunganDialog = ref(false);
const showImportDialog = ref(false);
const selectedRekamMedis = ref<RekamMedisWithDetails | null>(null);
const isEditingAll = ref(false);
const isSaving = ref(false);

const canEditPasien = computed(() => {
    // @ts-ignore
    const role = page.props.auth?.user?.role;
    return role === 'superadmin' || role === 'admin';
});

const canDeleteRekamMedis = computed(() => {
    // @ts-ignore
    return page.props.auth?.user?.role === 'superadmin';
});

const getClientTime = () => {
    const now = new Date();
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
};

const activeTab = ref('0');

const horizontalRekamMedis = computed(() => {
    return props.pasien.rekam_medis?.filter(rm => rm.jenis_layanan !== 'screening') || [];
});

const screeningRekamMedis = computed(() => {
    return props.pasien.rekam_medis?.filter(rm => rm.jenis_layanan === 'screening') || [];
});

const formKunjungan = useForm({
    tanggal_kunjungan: new Date(),
    client_time: '',
    jenis_layanan: 'berobat',
    catatan: ''
});

const formImport = useForm({
    file_excel: null as File | null,
});

const serviceOptions = [
    { label: 'Umum', value: 'umum' },
    { label: 'Gigi', value: 'gigi' },
    { label: 'KIA', value: 'kia' },
    { label: 'Surat Sehat', value: 'surat_sehat' },
    { label: 'Surat Sakit', value: 'surat_sakit' }
];

const formAnamnesis = useForm({
    perawat_id: null as number | null,
    tekanan_darah: '',
    suhu: null as number | null,
    nadi: null as number | null,
    respirasi: null as number | null,
    tinggi_badan: null as number | null,
    berat_badan: null as number | null,
    skala_nyeri: null as number | null,
    keluhan_utama: '',
    riwayat_penyakit_sekarang: '',
    riwayat_penyakit_dahulu: '',
    riwayat_keluarga: '',
    riwayat_alergi: '',
    riwayat_obat: '',
    diagnosa_keperawatan: '',
    intervensi_keperawatan: '',
    implementasi_keperawatan: '',
    evaluasi_keperawatan: '',
    lingkar_perut: null as number | null,
    is_hamil: false,
    is_menyusui: false,
    tindak_lanjut: '',
    keterangan_tindak_lanjut: '',
    gula_darah: null as number | null,
    jenis_gula_darah: 'sewaktu',
    asam_urat: null as number | null,
    kolesterol: null as number | null,
    hemoglobin: null as number | null,
});

const formPemeriksaan = useForm({
    dokter_id: null as number | null,
    diagnosis_utama: '',
    diagnosis_sekunder: '',
    kode_icd10: '',
    pemeriksaan_fisik: '',
    hasil_pemeriksaan: '',
    penatalaksanaan_medis: '',
    prognosis: '',
    anjuran: '',
});

const submitKunjungan = () => {
    formKunjungan.client_time = getClientTime();
    formKunjungan.post(route('pasien.kunjungan', props.pasien.id), {
        onSuccess: () => {
            showKunjunganDialog.value = false;
            formKunjungan.reset();
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Kunjungan medis baru berhasil didaftarkan', life: 3000 });
        }
    });
};

const openDetailDialog = (rekamMedis: RekamMedisWithDetails) => {
    selectedRekamMedis.value = rekamMedis;
    isEditingAll.value = false;
    
    if (rekamMedis.anamnesis) {
        formAnamnesis.perawat_id = rekamMedis.anamnesis.perawat_id || null;
        formAnamnesis.tekanan_darah = rekamMedis.anamnesis.tekanan_darah || '';
        formAnamnesis.suhu = rekamMedis.anamnesis.suhu;
        formAnamnesis.nadi = rekamMedis.anamnesis.nadi;
        formAnamnesis.respirasi = rekamMedis.anamnesis.respirasi;
        formAnamnesis.tinggi_badan = Number(rekamMedis.anamnesis.tinggi_badan) || null;
        formAnamnesis.berat_badan = Number(rekamMedis.anamnesis.berat_badan) || null;
        formAnamnesis.skala_nyeri = rekamMedis.anamnesis.skala_nyeri;
        formAnamnesis.keluhan_utama = rekamMedis.anamnesis.keluhan_utama || '';
        formAnamnesis.riwayat_penyakit_sekarang = rekamMedis.anamnesis.riwayat_penyakit_sekarang || '';
        formAnamnesis.riwayat_penyakit_dahulu = rekamMedis.anamnesis.riwayat_penyakit_dahulu || '';
        formAnamnesis.riwayat_keluarga = rekamMedis.anamnesis.riwayat_keluarga || '';
        formAnamnesis.riwayat_alergi = rekamMedis.anamnesis.riwayat_alergi || '';
        formAnamnesis.riwayat_obat = rekamMedis.anamnesis.riwayat_obat || '';
        formAnamnesis.diagnosa_keperawatan = rekamMedis.anamnesis.diagnosa_keperawatan || '';
        formAnamnesis.intervensi_keperawatan = rekamMedis.anamnesis.intervensi_keperawatan || '';
        formAnamnesis.implementasi_keperawatan = rekamMedis.anamnesis.implementasi_keperawatan || '';
        formAnamnesis.evaluasi_keperawatan = rekamMedis.anamnesis.evaluasi_keperawatan || '';
        formAnamnesis.lingkar_perut = Number(rekamMedis.anamnesis.lingkar_perut) || null;
        formAnamnesis.is_hamil = Boolean(rekamMedis.anamnesis.is_hamil);
        formAnamnesis.is_menyusui = Boolean(rekamMedis.anamnesis.is_menyusui);
        formAnamnesis.tindak_lanjut = rekamMedis.anamnesis.tindak_lanjut || '';
        formAnamnesis.keterangan_tindak_lanjut = rekamMedis.anamnesis.keterangan_tindak_lanjut || '';
        formAnamnesis.gula_darah = rekamMedis.anamnesis.gula_darah !== null ? Number(rekamMedis.anamnesis.gula_darah) : null;
        formAnamnesis.asam_urat = rekamMedis.anamnesis.asam_urat !== null ? Number(rekamMedis.anamnesis.asam_urat) : null;
        formAnamnesis.kolesterol = rekamMedis.anamnesis.kolesterol !== null ? Number(rekamMedis.anamnesis.kolesterol) : null;
        formAnamnesis.hemoglobin = rekamMedis.anamnesis.hemoglobin !== null ? Number(rekamMedis.anamnesis.hemoglobin) : null;
    } else {
        formAnamnesis.clearErrors();
        formAnamnesis.perawat_id = null;
        formAnamnesis.tekanan_darah = '';
        formAnamnesis.suhu = null;
        formAnamnesis.nadi = null;
        formAnamnesis.respirasi = null;
        formAnamnesis.tinggi_badan = null;
        formAnamnesis.berat_badan = null;
        formAnamnesis.skala_nyeri = null;
        formAnamnesis.keluhan_utama = '';
        formAnamnesis.riwayat_penyakit_sekarang = '';
        formAnamnesis.riwayat_penyakit_dahulu = '';
        formAnamnesis.riwayat_keluarga = '';
        formAnamnesis.riwayat_alergi = '';
        formAnamnesis.riwayat_obat = '';
        formAnamnesis.diagnosa_keperawatan = '';
        formAnamnesis.intervensi_keperawatan = '';
        formAnamnesis.implementasi_keperawatan = '';
        formAnamnesis.evaluasi_keperawatan = '';
        formAnamnesis.lingkar_perut = null;
        formAnamnesis.is_hamil = false;
        formAnamnesis.is_menyusui = false;
        formAnamnesis.tindak_lanjut = '';
        formAnamnesis.keterangan_tindak_lanjut = '';
        formAnamnesis.gula_darah = null;
        formAnamnesis.asam_urat = null;
        formAnamnesis.kolesterol = null;
        formAnamnesis.hemoglobin = null;
    }

    if (rekamMedis.pemeriksaan) {
        formPemeriksaan.dokter_id = rekamMedis.pemeriksaan.dokter_id || null;
        formPemeriksaan.diagnosis_utama = rekamMedis.pemeriksaan.diagnosis_utama || '';
        formPemeriksaan.diagnosis_sekunder = rekamMedis.pemeriksaan.diagnosis_sekunder || '';
        formPemeriksaan.kode_icd10 = rekamMedis.pemeriksaan.kode_icd10 || '';
        formPemeriksaan.pemeriksaan_fisik = rekamMedis.pemeriksaan.pemeriksaan_fisik || '';
        formPemeriksaan.hasil_pemeriksaan = rekamMedis.pemeriksaan.hasil_pemeriksaan || '';
        formPemeriksaan.penatalaksanaan_medis = rekamMedis.pemeriksaan.penatalaksanaan_medis || '';
        formPemeriksaan.prognosis = rekamMedis.pemeriksaan.prognosis || '';
        formPemeriksaan.anjuran = rekamMedis.pemeriksaan.anjuran || '';
    } else {
        formPemeriksaan.clearErrors();
        formPemeriksaan.dokter_id = null;
        formPemeriksaan.diagnosis_utama = '';
        formPemeriksaan.diagnosis_sekunder = '';
        formPemeriksaan.kode_icd10 = '';
        formPemeriksaan.pemeriksaan_fisik = '';
        formPemeriksaan.hasil_pemeriksaan = '';
        formPemeriksaan.penatalaksanaan_medis = '';
        formPemeriksaan.prognosis = '';
        formPemeriksaan.anjuran = '';
    }

    showDetailDialog.value = true;
};

const closeDetailDialog = () => {
    showDetailDialog.value = false;
    selectedRekamMedis.value = null;
    isEditingAll.value = false;
};

const showScreeningDialog = ref(false);

const openScreeningDialog = (rekamMedis: RekamMedisWithDetails) => {
    selectedRekamMedis.value = rekamMedis;
    isEditingAll.value = false;
    
    if (rekamMedis.anamnesis) {
        formAnamnesis.perawat_id = rekamMedis.anamnesis.perawat_id || null;
        formAnamnesis.tekanan_darah = rekamMedis.anamnesis.tekanan_darah || '';
        formAnamnesis.tinggi_badan = Number(rekamMedis.anamnesis.tinggi_badan) || null;
        formAnamnesis.berat_badan = Number(rekamMedis.anamnesis.berat_badan) || null;
        formAnamnesis.keluhan_utama = rekamMedis.anamnesis.keluhan_utama || 'Pemeriksaan Screening (Otomatis)';
        formAnamnesis.lingkar_perut = Number(rekamMedis.anamnesis.lingkar_perut) || null;
        formAnamnesis.tindak_lanjut = rekamMedis.anamnesis.tindak_lanjut || '';
        formAnamnesis.gula_darah = rekamMedis.anamnesis.gula_darah !== null ? Number(rekamMedis.anamnesis.gula_darah) : null;
        formAnamnesis.asam_urat = rekamMedis.anamnesis.asam_urat !== null ? Number(rekamMedis.anamnesis.asam_urat) : null;
        formAnamnesis.kolesterol = rekamMedis.anamnesis.kolesterol !== null ? Number(rekamMedis.anamnesis.kolesterol) : null;
        formAnamnesis.hemoglobin = rekamMedis.anamnesis.hemoglobin !== null ? Number(rekamMedis.anamnesis.hemoglobin) : null;
    } else {
        formAnamnesis.clearErrors();
        formAnamnesis.perawat_id = null;
        formAnamnesis.tekanan_darah = '';
        formAnamnesis.tinggi_badan = null;
        formAnamnesis.berat_badan = null;
        formAnamnesis.keluhan_utama = 'Pemeriksaan Screening (Otomatis)';
        formAnamnesis.lingkar_perut = null;
        formAnamnesis.tindak_lanjut = '';
        formAnamnesis.gula_darah = null;
        formAnamnesis.asam_urat = null;
        formAnamnesis.kolesterol = null;
        formAnamnesis.hemoglobin = null;
    }

    showScreeningDialog.value = true;
};

const submitSemua = () => {
    if (!selectedRekamMedis.value) return;
    const rmId = selectedRekamMedis.value.id;
    isSaving.value = true;

    axios.put(route('rekam-medis.anamnesis.update', rmId), formAnamnesis.data())
        .then(() => {
            formPemeriksaan.put(route('rekam-medis.pemeriksaan.update', rmId), {
                onSuccess: () => {
                    isEditingAll.value = false;
                    isSaving.value = false;
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Seluruh data rekam medis berhasil diperbarui', life: 3000 });
                },
                onError: () => {
                    isSaving.value = false;
                    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Pastikan format isian data benar.', life: 3000 });
                }
            });
        })
        .catch(err => {
            console.error(err);
            isSaving.value = false;
            toast.add({ severity: 'error', summary: 'Gagal Error', detail: 'Gagal Menyimpan Anamnesis', life: 3000 });
        });
};

const submitScreening = () => {
    if (!selectedRekamMedis.value) return;
    const rmId = selectedRekamMedis.value.id;
    isSaving.value = true;

    axios.put(route('rekam-medis.anamnesis.update', rmId), formAnamnesis.data())
        .then(() => {
            isSaving.value = false;
            showScreeningDialog.value = false;
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Data screening berhasil diperbarui', life: 3000 });
        })
        .catch(err => {
            console.error(err);
            isSaving.value = false;
            toast.add({ severity: 'error', summary: 'Gagal', detail: 'Pastikan format isian data benar.', life: 3000 });
        });
};

const deleteRekamMedis = (data: any) => {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus rekam medis tanggal ${formatDate(data.created_at || data.tanggal_kunjungan)}? Data yang dihapus tidak dapat dikembalikan.`,
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
            router.delete(route('rekam-medis.destroy', data.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Rekam medis berhasil dihapus', life: 3000 });
                },
                onError: () => {
                    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menghapus rekam medis', life: 3000 });
                }
            });
        }
    });
};

const handleImportUpload = (event: any) => {
    formImport.file_excel = event.files[0];
};

const submitImport = () => {
    formImport.post(route('pasien.rekam-medis.import', props.pasien.id), {
        preserveScroll: true,
        onSuccess: () => {
            showImportDialog.value = false;
            formImport.reset();
            toast.add({ severity: 'success', summary: 'Sukses', detail: 'Data rekam medis berhasil diimpor.', life: 3000 });
        },
        onError: (errors) => {
            const errorMsg = errors.file_excel || 'Pastikan file Excel valid dan maksimal 2MB.';
            toast.add({ severity: 'error', summary: 'Gagal', detail: errorMsg, life: 5000 });
        }
    });
};

const downloadTemplate = () => {
    window.location.href = route('pasien.rekam-medis.template');
};

const calcBmi = computed(() => {
    if (formAnamnesis.berat_badan && formAnamnesis.tinggi_badan) {
        const h = formAnamnesis.tinggi_badan / 100;
        return (formAnamnesis.berat_badan / (h * h)).toFixed(2);
    }
    return '-';
});

// Formatting helpers
const getStatusSeverity = (status: string) => {
    const severities: Record<string, string> = { mahasiswa: 'info', dosen: 'success', tendik: 'warn', umum: 'secondary' };
    return severities[status] || 'secondary';
};
const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = { mahasiswa: 'Mahasiswa', dosen: 'Dosen', tendik: 'Tendik', umum: 'Umum' };
    return labels[status] || status;
};
const getKunjunganStatusSeverity = (status: string) => {
    const severities: Record<string, string> = { menunggu_perawat: 'warn', proses_anamnesis: 'info', siap_dokter: 'info', sedang_diperiksa: 'info', selesai: 'success', batal: 'danger' };
    return severities[status] || 'secondary';
};
const getKunjunganStatusLabel = (status: string) => {
    const labels: Record<string, string> = { menunggu_perawat: 'Menunggu Perawat', proses_anamnesis: 'Proses Anamnesis', siap_dokter: 'Siap Dokter', sedang_diperiksa: 'Sedang Diperiksa', selesai: 'Selesai', batal: 'Batal' };
    return labels[status] || status;
};

// Screening Calculation Helpers
const getBmiData = (tb: number | null | undefined, bb: number | null | undefined) => {
    if (!tb || !bb) return { value: '-', category: '-', isCritical: false };
    const h = tb / 100;
    const bmi = bb / (h * h);
    let category = '';
    let isCritical = false;
    
    if (bmi < 18) category = 'Underweight (<18)';
    else if (bmi <= 22.9) category = 'Normal (18-22.9)';
    else if (bmi <= 24.9) { category = 'Overweight (23-24.9)'; isCritical = true; }
    else if (bmi <= 29.9) { category = 'Obesitas Tingkat 1 (>25-29.9)'; isCritical = true; }
    else { category = 'Obesitas Tingkat 2 (>30)'; isCritical = true; }
    
    return { value: bmi.toFixed(2), category, isCritical };
};

const getLpData = (lp: number | null | undefined, isHamil: boolean | undefined, gender: string | undefined) => {
    if (isHamil) return { value: lp || '-', status: 'Hamil', isCritical: false };
    if (!lp) return { value: '-', status: '-', isCritical: false };
    
    let isCritical = false;
    if (gender === 'L' && lp > 90) isCritical = true;
    if (gender === 'P' && lp > 80) isCritical = true;
    
    return { 
        value: lp, 
        status: isCritical ? 'Obesitas Sentral' : 'Normal', 
        isCritical 
    };
};

const getTdData = (td: string | null | undefined) => {
    if (!td) return { value: '-', category: '-', isCritical: false };
    const parts = td.split('/');
    if (parts.length !== 2) return { value: td, category: '-', isCritical: false };
    
    const sys = parseInt(parts[0]);
    const dia = parseInt(parts[1]);
    let category = '';
    let isCritical = false;
    
    if (sys < 130 && dia < 85) {
        category = 'Normal (<129/84)';
    } else if (sys <= 139 || dia <= 89) {
        category = 'Prehipertensi (130/85-139/89)';
        isCritical = true;
    } else if (sys <= 159 || dia <= 99) {
        category = 'Hipertensi Grade 1 (140/90-159/99)';
        isCritical = true;
    } else {
        category = 'Hipertensi Grade 2 (>160/100)';
        isCritical = true;
    }
    
    return { value: td, category, isCritical };
};

const getGdData = (gd: number | null | undefined, jenis: string | null | undefined) => {
    if (!gd) return { value: '-', category: '-', isCritical: false };
    let category = 'Normal';
    let isCritical = false;
    if (jenis === 'puasa') {
        if (gd > 120) { isCritical = true; category = 'Hiperglikemia (GDP: >120)'; }
    } else {
        if (gd > 200) { isCritical = true; category = 'Hiperglikemia (GDS: >200)'; }
    }
    return { value: gd, category, isCritical };
};

const getAuData = (au: number | null | undefined, gender: string | undefined) => {
    if (!au) return { value: '-', category: '-', isCritical: false };
    let isCritical = false;
    let label = 'Normal';
    if (gender === 'L' && au > 7) { isCritical = true; label = 'Hiperuricemia (L: >7)'; }
    if (gender === 'P' && au > 6) { isCritical = true; label = 'Hiperuricemia (P: >6)'; }
    return { value: au, category: label, isCritical };
};

const getCholData = (chol: number | null | undefined) => {
    if (!chol) return { value: '-', category: '-', isCritical: false };
    const isCritical = chol > 200;
    return { value: chol, category: isCritical ? 'Hipercholesterolemia (>200)' : 'Normal', isCritical };
};

const getHbData = (hb: number | null | undefined) => {
    if (!hb) return { value: '-', category: '-', isCritical: false };
    const isCritical = hb < 12;
    return { value: hb, category: isCritical ? 'Anemia (< 12)' : 'Normal', isCritical };
};

const formatDate = (date: string) => {
    if (!date) return '-';
    const d = new Date(date);
    const day = d.getDate().toString().padStart(2, '0');
    const month = d.toLocaleString('id-ID', { month: 'short' });
    const year = d.getFullYear();
    const hours = d.getHours().toString().padStart(2, '0');
    const minutes = d.getMinutes().toString().padStart(2, '0');
    
    // Hide time if it's 00:00 (likely a DATE only field)
    if (hours === '00' && minutes === '00') {
        return `${day} ${month} ${year}`;
    }
    
    return `${day} ${month} ${year}, ${hours}:${minutes}`;
};
const formatText = (text: string | null | undefined) => {
    if (!text) return '-';
    return text.toString()
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
};
const getAge = (birthDate: string | undefined) => {
    if (!birthDate) return '-';
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    return age;
};

const printAnamnesis = (rm: RekamMedisWithDetails) => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Cetak Anamnesis - ${rm.nomor_kunjungan}</title>
            <style>
                body { font-family: 'Inter', sans-serif; margin: 40px; font-size: 14px; line-height: 1.5; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; }
                .section { margin-bottom: 15px; }
                .section-title { font-weight: bold; background: #f0f0f0; padding: 5px; margin-bottom: 10px; }
                .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
                .row { display: flex; margin-bottom: 5px; }
                .label { color: #333; min-width: 150px; font-weight: bold; }
                .value { font-weight: 500; }
                .patient-info { background: #f9f9f9; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; }
                @media print { body { margin: 0; padding: 20px; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
                <p>Formulir Hasil Anamnesis</p>
            </div>

            <div class="patient-info">
                <div class="grid">
                    <div>
                        <div class="row"><span class="label">Nama Pasien:</span><span class="value">${props.pasien.nama}</span></div>
                        <div class="row"><span class="label">No. RM:</span><span class="value">${props.pasien.nomor_rm}</span></div>
                        <div class="row"><span class="label">Jenis Kelamin:</span><span class="value">${props.pasien.jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan'}</span></div>
                    </div>
                    <div>
                        <div class="row"><span class="label">No. Kunjungan:</span><span class="value">${rm.nomor_kunjungan}</span></div>
                        <div class="row"><span class="label">Tanggal:</span><span class="value">${formatDate(rm.tanggal_kunjungan)}</span></div>
                        <div class="row"><span class="label">Perawat:</span><span class="value">${rm.anamnesis?.perawat?.name || '-'}</span></div>
                    </div>
                </div>
            </div>

            ${rm.anamnesis ? `
            <div class="section">
                <div class="section-title">A. TANDA VITAL & ANTROPOMETRI</div>
                <div class="grid">
                    <div>
                        <div class="row"><span class="label">Tekanan Darah:</span><span class="value">${rm.anamnesis.tekanan_darah || '-'} mmHg</span></div>
                        <div class="row"><span class="label">Suhu Tubuh:</span><span class="value">${rm.anamnesis.suhu || '-'} °C</span></div>
                        <div class="row"><span class="label">Nadi:</span><span class="value">${rm.anamnesis.nadi || '-'} x/menit</span></div>
                        <div class="row"><span class="label">Respirasi:</span><span class="value">${rm.anamnesis.respirasi || '-'} x/menit</span></div>
                    </div>
                    <div>
                        <div class="row"><span class="label">Berat Badan:</span><span class="value">${rm.anamnesis.berat_badan || '-'} kg</span></div>
                        <div class="row"><span class="label">Tinggi Badan:</span><span class="value">${rm.anamnesis.tinggi_badan || '-'} cm</span></div>
                        <div class="row"><span class="label">Skala Nyeri (0-10):</span><span class="value">${rm.anamnesis.skala_nyeri ?? '-'}</span></div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">B. KELUHAN & RIWAYAT</div>
                <div class="row"><span class="label">Keluhan Utama:</span><span class="value">${rm.anamnesis.keluhan_utama || '-'}</span></div>
                <div class="row"><span class="label">Riwayat Penyakit Skrg:</span><span class="value">${rm.anamnesis.riwayat_penyakit_sekarang || '-'}</span></div>
                <div class="row"><span class="label">Riwayat Penyakit Dhl:</span><span class="value">${rm.anamnesis.riwayat_penyakit_dahulu || '-'}</span></div>
                <div class="row"><span class="label">Riwayat Keluarga:</span><span class="value">${rm.anamnesis.riwayat_keluarga || '-'}</span></div>
                <div class="row"><span class="label">Riwayat Alergi:</span><span class="value">${rm.anamnesis.riwayat_alergi || '-'}</span></div>
                <div class="row"><span class="label">Riwayat Obat:</span><span class="value">${rm.anamnesis.riwayat_obat || '-'}</span></div>
            </div>
            ` : '<p>Data anamnesis belum diisi.</p>'}
            
            <div style="margin-top: 50px; text-align: right;">
                <p>Balikpapan, ${new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</p>
                <p>Perawat Pemeriksa,</p>
                <br><br><br>
                <p><strong>${rm.anamnesis?.perawat?.name || '(...................................)'}</strong></p>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
    }, 250);
};
</script>

<template>
    <Head :title="`Rekam Medis - ${pasien.nama}`" />
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('pasien.index')">
                    <Button icon="pi pi-arrow-left" text rounded />
                </Link>
                <span>Rekam Medis Pasien</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Info Pasien Komprehensif -->
            <Card class="shadow-sm border-l-4 border-l-purple-500">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="pi pi-id-card text-3xl text-purple-600"></i>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ pasien.nama }}</h2>
                                <p class="text-sm text-gray-500">No. RM: <strong class="text-purple-700">{{ pasien.nomor_rm }}</strong></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2" v-if="canEditPasien">
                            <Button label="Buat Rekam Medis" icon="pi pi-plus" size="small" @click="showKunjunganDialog = true" severity="success" outlined />
                            <Button label="Import Template" icon="pi pi-upload" size="small" @click="showImportDialog = true" severity="info" outlined />
                        </div>
                        <div class="flex items-center gap-2">
                            <Tag :value="getStatusLabel(pasien.tipe_pasien)" :severity="getStatusSeverity(pasien.tipe_pasien)" />
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-y-4 gap-x-2 text-sm mt-2 p-3 bg-gray-50 rounded-lg">
                        <div><span class="text-gray-500 block text-xs">No Identitas</span><span class="font-semibold">{{ pasien.nik || pasien.nomor_identitas || '-' }}</span></div>
                        <div><span class="text-gray-500 block text-xs">Tanggal Lahir</span><span class="font-medium">{{ pasien.tanggal_lahir ? formatDate(pasien.tanggal_lahir) : '-' }} ({{ pasien.tanggal_lahir ? getAge(pasien.tanggal_lahir) + ' Thn' : '-' }})</span></div>
                        <div><span class="text-gray-500 block text-xs">Jenis Kelamin</span><span class="font-medium">{{ pasien.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                        <div><span class="text-gray-500 block text-xs">Golongan Darah</span><span class="text-red-600 font-bold">{{ pasien.golongan_darah || '-' }}</span></div>
                        <div><span class="text-gray-500 block text-xs">No. Telp</span><span class="font-medium">{{ pasien.phone || '-' }}</span></div>
                        
                        <div><span class="text-gray-500 block text-xs">Agama</span><span class="font-medium">{{ formatText(pasien.agama) }}</span></div>
                        <div><span class="text-gray-500 block text-xs">Status Perkawinan</span><span class="font-medium">{{ formatText(pasien.status_perkawinan) }}</span></div>
                        <div><span class="text-gray-500 block text-xs">Pendidikan Terakhir</span><span class="font-medium">{{ formatText(pasien.pendidikan_terakhir) }}</span></div>
                        <div class="md:col-span-2"><span class="text-gray-500 block text-xs">Pekerjaan</span><span class="font-medium">{{ formatText(pasien.pekerjaan) }}</span></div>
                        
                        <div class="md:col-span-5 border-t pt-2 mt-1">
                            <span class="text-gray-500 block text-xs">Alamat Lengkap</span>
                            <span class="font-medium">{{ pasien.alamat || '-' }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Horizontal Scrolling Spreadsheet DataTable -->
            <Card class="shadow-sm overflow-hidden">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-table text-purple-600"></i>
                            <span>Buku Rekam Medis Horizontal (Spreadsheet)</span>
                        </div>
                    </div>
                </template>
                <template #content>
                    <Tabs v-model:value="activeTab" class="bg-transparent">
                        <TabList class="!bg-white/80 !backdrop-blur-md !border-b !border-gray-200 !rounded-t-xl overflow-hidden shadow-sm sticky top-0 z-10">
                            <Tab value="0" class="!px-8 !py-4 !transition-all !duration-300">Rekam Medis Horizontal</Tab>
                            <Tab value="1" class="!px-8 !py-4 !transition-all !duration-300">Data Screening</Tab>
                        </TabList>
                        <TabPanels class="!bg-transparent !p-0 !mt-4">
                            <TabPanel value="0" class="!p-0">
                                <div class="border rounded my-2 bg-gray-50 p-1">
                                    <p class="text-xs text-gray-500 px-2 flex gap-2 items-center">
                                        <i class="pi pi-info-circle text-blue-500"></i> 
                                        Gunakan scroll horizontal / geser ke kanan untuk melihat semua baris rekam medis. Tekan tombol "Edit Detail" pada kolom baris akhir untuk mengeditnya.
                                    </p>
                                </div>

                                <DataTable
                                    :value="horizontalRekamMedis"
                                    :paginator="horizontalRekamMedis.length > 10"
                                    :rows="10"
                                    dataKey="id"
                                    scrollable
                                    scrollDirection="both"
                                    class="p-datatable-sm text-xs mt-2 excel-table"
                                    emptyMessage="Belum ada riwayat rekam medis"
                                >
                                    <!-- Group 1: PURPLE (#5b328a) Patient and Admin Info -->
                        <Column header="Timestamp" style="min-width: 150px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body="{ data }"><span>{{ formatDate(data.created_at || data.tanggal_kunjungan) }}</span></template>
                        </Column>
                        <Column header="No. RM" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body><span>{{ pasien.nomor_rm }}</span></template>
                        </Column>
                        <Column header="Nama Pasien" style="min-width: 180px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body><span>{{ pasien.nama }}</span></template>
                        </Column>
                        <Column header="Tanggal Lahir" style="min-width: 140px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.tanggal_lahir ? formatDate(pasien.tanggal_lahir) : '-' }}</span></template>
                        </Column>
                        <Column header="No. Identitas (NIK/NIP/NIM)" style="min-width: 180px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.nik || pasien.nomor_identitas || '-' }}</span></template>
                        </Column>
                        <Column header="No. Telp" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.phone || '-' }}</span></template>
                        </Column>
                        <Column header="Jenis Kelamin" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span></template>
                        </Column>
                        <Column header="Alamat" style="min-width: 200px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.alamat || '-' }}</span></template>
                        </Column>
                        <Column header="Agama" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ formatText(pasien.agama) }}</span></template>
                        </Column>
                        <Column header="Status Perkawinan" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ formatText(pasien.status_perkawinan) }}</span></template>
                        </Column>
                        <Column header="Pendidikan terakhir" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ formatText(pasien.pendidikan_terakhir) }}</span></template>
                        </Column>
                        <Column header="Status di ITK" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ getStatusLabel(pasien.tipe_pasien) }}</span></template>
                        </Column>
                        <Column header="Golongan Darah" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.golongan_darah || '-' }}</span></template>
                        </Column>
                        <Column header="Pasien Baru atau Lama" style="min-width: 150px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ index }"><span>{{ index === ((pasien.rekam_medis?.length || 1) - 1) ? 'Baru' : 'Lama' }}</span></template>
                        </Column>
                        <Column header="Petugas Administrasi" style="min-width: 150px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>Admin / Sistem</span></template>
                        </Column>

                        <!-- Group 2: BLUE (#4a86e8) Anamnesis / Kunjungan -->
                        <Column header="Keluhan Utama" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.keluhan_utama || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat penyakit sekarang" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.riwayat_penyakit_sekarang || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat penyakit dahulu" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.riwayat_penyakit_dahulu || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat Keluarga" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.riwayat_keluarga || '-' }}</span></template>
                        </Column>
                        <Column header="Alergi" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.riwayat_alergi || '-' }}</span></template>
                        </Column>
                        <Column header="Kondisi Khusus" style="min-width: 120px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }">
                                <span v-if="pasien.jenis_kelamin === 'P'" class="font-medium text-pink-600">
                                    {{ data.anamnesis?.is_hamil ? 'Hamil' : (data.anamnesis?.is_menyusui ? 'Menyusui' : '-') }}
                                </span>
                                <span v-else>-</span>
                            </template>
                        </Column>
                        
                        <!-- Group 3: BLUE TTV -->
                        <Column header="TTV.1 TD" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.tekanan_darah || '-' }}</span></template>
                        </Column>
                        <Column header="TTV.2 Nadi" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.nadi || '-' }}</span></template>
                        </Column>
                        <Column header="TTV.3 Suhu" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.suhu || '-' }}</span></template>
                        </Column>
                        <Column header="TTV. 4 RR" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.respirasi || '-' }}</span></template>
                        </Column>
                        <Column header="Berat Badan" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.berat_badan || '-' }}</span></template>
                        </Column>
                        <Column header="Tinggi Badan" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.tinggi_badan || '-' }}</span></template>
                        </Column>
                        <Column header="IMT" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }">
                                <span v-if="data.anamnesis?.berat_badan && data.anamnesis?.tinggi_badan">
                                    {{ (data.anamnesis.berat_badan / Math.pow(data.anamnesis.tinggi_badan / 100, 2)).toFixed(2) }}
                                </span>
                                <span v-else>-</span>
                            </template>
                        </Column>
                        <Column header="Skala Nyeri" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.skala_nyeri ?? '-' }}</span></template>
                        </Column>

                        <!-- Group 4: BLUE Pemeriksaan/Tindakan -->
                        <Column header="Pemeriksaan Fisik Lain" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.pemeriksaan?.pemeriksaan_fisik || '-' }}</span></template>
                        </Column>
                        <Column header="Dokter penanggung jawab" style="min-width: 180px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.pemeriksaan?.dokter?.name || '-' }}</span></template>
                        </Column>
                        <Column header="Diagnosa medis" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }">
                                <div>
                                    <strong class="block">{{ data.pemeriksaan?.diagnosis_utama || '-' }}</strong>
                                    <span v-if="data.pemeriksaan?.diagnosis_sekunder" class="text-[10px] mt-1">{{ data.pemeriksaan.diagnosis_sekunder }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Penatalaksanaan Medis" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.pemeriksaan?.penatalaksanaan_medis || '-' }}</span></template>
                        </Column>

                        <!-- Group 5: BLUE Keperawatan -->
                        <Column header="Diagnosa Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.diagnosa_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Intervensi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.intervensi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Implementasi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.implementasi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Evaluasi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.evaluasi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Perawat" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.perawat?.name || '-' }}</span></template>
                        </Column>

                        <!-- ACTION COLUMN -->
                        <Column header="Aksi" style="min-width: 140px; text-align: center" alignFrozen="right" frozen>
                            <template #body="{ data }">
                                <div class="flex gap-2 justify-center">
                                    <Button
                                        v-if="data.anamnesis"
                                        icon="pi pi-print"
                                        size="small"
                                        severity="secondary"
                                        title="Cetak Anamnesis"
                                        class="!rounded-xl h-8 w-8 p-0 flex items-center justify-center shadow-sm hover:shadow-md transition-all"
                                        @click="printAnamnesis(data)"
                                    />
                                    <a
                                        v-if="data.surat_dokter"
                                        :href="route('surat-dokter.pdf', data.surat_dokter.id)"
                                        target="_blank"
                                        title="Cetak Surat Dokter"
                                        class="p-button p-component p-button-warning p-button-sm !rounded-xl p-0 h-8 w-8 flex items-center justify-center shadow-sm hover:shadow-md transition-all text-white no-underline"
                                    >
                                        <i class="pi pi-print"></i>
                                    </a>
                                        <Button
                                            icon="pi pi-pencil"
                                            size="small"
                                            severity="help"
                                            title="Edit Detail Rekam Medis"
                                            class="!rounded-xl h-8 w-8 p-0 flex items-center justify-center shadow-sm hover:shadow-md transition-all"
                                            @click="openDetailDialog(data)"
                                        />
                                        <Button
                                            v-if="canDeleteRekamMedis"
                                            icon="pi pi-trash"
                                            size="small"
                                            severity="danger"
                                            title="Hapus Rekam Medis"
                                            class="!rounded-xl h-8 w-8 p-0 flex items-center justify-center shadow-sm hover:shadow-md transition-all"
                                            @click="deleteRekamMedis(data)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                            </TabPanel>

                            <TabPanel value="1" class="!p-0">
                                <DataTable
                                    :value="screeningRekamMedis"
                                    :paginator="screeningRekamMedis.length > 10"
                                    :rows="10"
                                    dataKey="id"
                                    scrollable
                                    scrollDirection="both"
                                    class="p-datatable-sm text-xs mt-2 excel-table"
                                    emptyMessage="Belum ada riwayat rekam medis"
                                >
                                    <!-- Meta Info -->
                                    <Column header="No" style="min-width: 50px" frozen>
                                        <template #body="{ index }"><span>{{ index + 1 }}</span></template>
                                    </Column>
                                    <Column header="Timestamp" style="min-width: 130px" frozen>
                                        <template #body="{ data }"><span>{{ formatDate(data.created_at || data.tanggal_kunjungan) }}</span></template>
                                    </Column>
                                    
                                    <Column header="Nama" style="min-width: 180px" frozen>
                                        <template #body><span>{{ pasien.nama }}</span></template>
                                    </Column>
                                    <Column header="Umur" style="min-width: 80px">
                                        <template #body><span>{{ getAge(pasien.tanggal_lahir) }} Thn</span></template>
                                    </Column>
                                    <Column header="J.K" style="min-width: 80px">
                                        <template #body><span>{{ pasien.jenis_kelamin === 'L' ? 'L' : 'P' }}</span></template>
                                    </Column>
                                    <Column header="Status ITK" style="min-width: 100px">
                                        <template #body><span>{{ getStatusLabel(pasien.tipe_pasien) }}</span></template>
                                    </Column>
                                    <Column header="Kondisi Khusus" style="min-width: 120px">
                                        <template #body="{ data }">
                                            <span v-if="pasien.jenis_kelamin === 'P'" class="font-medium text-pink-600">
                                                {{ data.anamnesis?.is_hamil ? 'Hamil' : (data.anamnesis?.is_menyusui ? 'Menyusui' : '-') }}
                                            </span>
                                            <span v-else>-</span>
                                        </template>
                                    </Column>
                                    
                                    <!-- Screening Measurements -->
                                    <Column header="Tinggi Badan (cm)" style="min-width: 130px" headerStyle="background-color: #2e7d32; color: white;">
                                        <template #body="{ data }"><span>{{ data.anamnesis?.tinggi_badan || '-' }}</span></template>
                                    </Column>
                                    <Column header="Berat Badan (kg)" style="min-width: 130px" headerStyle="background-color: #2e7d32; color: white;">
                                        <template #body="{ data }"><span>{{ data.anamnesis?.berat_badan || '-' }}</span></template>
                                    </Column>
                                    <Column header="IMT" style="min-width: 80px" headerStyle="background-color: #2e7d32; color: white;">
                                        <template #body="{ data }">
                                            <span :class="{'text-red-600 font-bold': getBmiData(data.anamnesis?.tinggi_badan, data.anamnesis?.berat_badan).isCritical}">
                                                {{ getBmiData(data.anamnesis?.tinggi_badan, data.anamnesis?.berat_badan).value }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Kategori IMT" style="min-width: 150px" headerStyle="background-color: #2e7d32; color: white;">
                                        <template #body="{ data }">
                                            <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getBmiData(data.anamnesis?.tinggi_badan, data.anamnesis?.berat_badan).isCritical}">
                                                {{ getBmiData(data.anamnesis?.tinggi_badan, data.anamnesis?.berat_badan).category }}
                                            </div>
                                        </template>
                                    </Column>
                                    
                                    <Column header="Lingkar Perut (cm)" style="min-width: 150px" headerStyle="background-color: #0277bd; color: white;">
                                        <template #body="{ data }">
                                            <span :class="{'text-red-600 font-bold': getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, pasien.jenis_kelamin).isCritical}">
                                                {{ getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, pasien.jenis_kelamin).value }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Status LP" style="min-width: 150px" headerStyle="background-color: #0277bd; color: white;">
                                        <template #body="{ data }">
                                            <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, pasien.jenis_kelamin).isCritical, 'bg-pink-100 text-pink-800 font-bold px-2 py-1 rounded': getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, pasien.jenis_kelamin).status === 'Hamil'}">
                                                {{ getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, pasien.jenis_kelamin).status }}
                                            </div>
                                        </template>
                                    </Column>
                                    
                                    <Column header="Tensi Sistolik/Diastolik" style="min-width: 170px" headerStyle="background-color: #f57c00; color: white;">
                                        <template #body="{ data }">
                                            <span :class="{'text-red-600 font-bold': getTdData(data.anamnesis?.tekanan_darah).isCritical}">
                                                {{ data.anamnesis?.tekanan_darah || '-' }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Kategori Tekanan Darah" style="min-width: 180px" headerStyle="background-color: #f57c00; color: white;">
                                        <template #body="{ data }">
                                            <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getTdData(data.anamnesis?.tekanan_darah).isCritical}">
                                                {{ getTdData(data.anamnesis?.tekanan_darah).category }}
                                            </div>
                                        </template>
                                    </Column>
                                    
                                    <!-- Gula Darah -->
                                    <Column header="Gula Darah (mg/dL)" style="min-width: 150px" headerStyle="background-color: #d84315; color: white;">
                                        <template #body="{ data }">
                                            <span :class="{'text-red-600 font-bold': getGdData(data.anamnesis?.gula_darah, data.anamnesis?.jenis_gula_darah).isCritical}">
                                                {{ getGdData(data.anamnesis?.gula_darah, data.anamnesis?.jenis_gula_darah).value }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Kategori Gula Darah" style="min-width: 170px" headerStyle="background-color: #d84315; color: white;">
                                        <template #body="{ data }">
                                            <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getGdData(data.anamnesis?.gula_darah, data.anamnesis?.jenis_gula_darah).isCritical}">
                                                {{ getGdData(data.anamnesis?.gula_darah, data.anamnesis?.jenis_gula_darah).category }}
                                            </div>
                                        </template>
                                    </Column>

                                    <!-- Asam Urat -->
                                    <Column header="Asam Urat (mg/dL)" style="min-width: 150px" headerStyle="background-color: #558b2f; color: white;">
                                        <template #body="{ data }">
                                            <span :class="{'text-red-600 font-bold': getAuData(data.anamnesis?.asam_urat, pasien.jenis_kelamin).isCritical}">
                                                {{ getAuData(data.anamnesis?.asam_urat, pasien.jenis_kelamin).value }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Kategori Asam Urat" style="min-width: 170px" headerStyle="background-color: #558b2f; color: white;">
                                        <template #body="{ data }">
                                            <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getAuData(data.anamnesis?.asam_urat, pasien.jenis_kelamin).isCritical}">
                                                {{ getAuData(data.anamnesis?.asam_urat, pasien.jenis_kelamin).category }}
                                            </div>
                                        </template>
                                    </Column>

                                    <!-- Kolesterol -->
                                    <Column header="Kolesterol (mg/dL)" style="min-width: 150px" headerStyle="background-color: #6a1b9a; color: white;">
                                        <template #body="{ data }">
                                            <span :class="{'text-red-600 font-bold': getCholData(data.anamnesis?.kolesterol).isCritical}">
                                                {{ getCholData(data.anamnesis?.kolesterol).value }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Kategori Kolesterol" style="min-width: 170px" headerStyle="background-color: #6a1b9a; color: white;">
                                        <template #body="{ data }">
                                            <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getCholData(data.anamnesis?.kolesterol).isCritical}">
                                                {{ getCholData(data.anamnesis?.kolesterol).category }}
                                            </div>
                                        </template>
                                    </Column>

                                    <!-- Hemoglobin -->
                                    <Column header="Hemoglobin (g/dL)" style="min-width: 150px" headerStyle="background-color: #c62828; color: white;">
                                        <template #body="{ data }">
                                            <span :class="{'text-red-600 font-bold': getHbData(data.anamnesis?.hemoglobin).isCritical}">
                                                {{ getHbData(data.anamnesis?.hemoglobin).value }}
                                            </span>
                                        </template>
                                    </Column>
                                    <Column header="Kategori Hemoglobin" style="min-width: 170px" headerStyle="background-color: #c62828; color: white;">
                                        <template #body="{ data }">
                                            <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getHbData(data.anamnesis?.hemoglobin).isCritical}">
                                                {{ getHbData(data.anamnesis?.hemoglobin).category }}
                                            </div>
                                        </template>
                                    </Column>
                                    <!-- History & Actions -->
                                    <Column header="Keterangan" style="min-width: 150px" headerStyle="background-color: #4527a0; color: white;">
                                        <template #body="{ data }"><span>{{ data.anamnesis?.keterangan_tindak_lanjut || '-' }}</span></template>
                                    </Column>
                                    <Column header="Tindak Lanjut" style="min-width: 150px" headerStyle="background-color: #4527a0; color: white;">
                                        <template #body="{ data }">
                                            <Tag :value="data.anamnesis?.tindak_lanjut === 'rujuk' ? 'Kembali ke Faskes 1' : (data.anamnesis?.tindak_lanjut === 'rawat_jalan' ? 'Rawat Jalan' : (data.anamnesis?.tindak_lanjut === 'edukasi' ? 'Edukasi' : 'Belum Ada'))" :severity="data.anamnesis?.tindak_lanjut === 'rujuk' ? 'danger' : (data.anamnesis?.tindak_lanjut === 'rawat_jalan' ? 'info' : (data.anamnesis?.tindak_lanjut === 'edukasi' ? 'success' : 'secondary'))" />
                                        </template>
                                    </Column>
                                    
                                    <Column header="Aksi" style="min-width: 60px; text-align: center" alignFrozen="right" frozen>
                                        <template #body="{ data }">
                                            <div class="flex gap-2 justify-center">
                                                <Button
                                                    icon="pi pi-pencil"
                                                    size="small"
                                                    severity="help"
                                                    title="Edit Screening / Detail"
                                                    class="!rounded-xl h-8 w-8 p-0 flex items-center justify-center shadow-sm hover:shadow-md transition-all"
                                                    @click="openScreeningDialog(data)"
                                                />
                                                <Button
                                                    v-if="canDeleteRekamMedis"
                                                    icon="pi pi-trash"
                                                    size="small"
                                                    severity="danger"
                                                    title="Hapus Rekam Medis"
                                                    class="!rounded-xl h-8 w-8 p-0 flex items-center justify-center shadow-sm hover:shadow-md transition-all"
                                                    @click="deleteRekamMedis(data)"
                                                />
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </TabPanel>
                        </TabPanels>
                    </Tabs>
                </template>
            </Card>
        </div>

        <Dialog v-model:visible="showImportDialog" modal header="Import Excel Rekam Medis" :style="{ width: '400px' }">
            <div class="flex flex-col gap-4">
                <p class="text-sm text-gray-600">Terima kasih telah menggunakan sistem data bulk. Anda dapat mengunduh Template yang telah terformat otomatis melalui tombol di bawah.</p>
                <Button label="Download Template Excel" icon="pi pi-download" size="small" severity="help" class="w-full" @click="downloadTemplate" />
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Data Rekam Medis Baru (.xlsx)</label>
                    <FileUpload 
                        mode="basic" 
                        name="file_excel" 
                        accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" 
                        :maxFileSize="2000000" 
                        @select="handleImportUpload" 
                        chooseLabel="Pilih File Excel (Max 2MB)"
                        class="w-full"
                    />
                    <small v-if="formImport.errors.file_excel" class="text-red-500">{{ formImport.errors.file_excel }}</small>
                </div>
            </div>
            <template #footer>
                <Button label="Batal" icon="pi pi-times" text @click="showImportDialog = false" />
                <Button label="Mulai Import" icon="pi pi-cloud-upload" :loading="formImport.processing" @click="submitImport" />
            </template>
        </Dialog>

        <!-- DIALOG TOMBOL BUAT KUNJUNGAN/RM -->
        <Dialog
            v-model:visible="showKunjunganDialog"
            modal
            header="Pendaftaran Kunjungan Rekam Medis Baru"
            :style="{ width: '35vw' }"
        >
            <form @submit.prevent="submitKunjungan" class="space-y-4 pt-2">
                <div class="flex flex-col gap-2">
                    <label>Pasien</label>
                    <InputText :value="pasien.nama" disabled class="bg-gray-100" />
                </div>
                <div class="flex flex-col gap-2">
                    <label>Tanggal Kunjungan Rekam Medis</label>
                    <DatePicker v-model="formKunjungan.tanggal_kunjungan" dateFormat="dd/mm/yy" showIcon />
                </div>
                <!-- Layanan and Surat fields removed per user request, defaulting to regular Rekam Medis (berobat) -->
                
                <div class="flex justify-end gap-2 mt-4">
                    <Button label="Batal" severity="secondary" text @click="showKunjunganDialog = false" />
                    <Button type="submit" label="Buat Record" severity="success" icon="pi pi-plus" :loading="formKunjungan.processing" />
                </div>
            </form>
        </Dialog>

        <!-- Dialog Edit Kunjungan Rekam Medis LENGKAP -->
        <Dialog
            v-model:visible="showDetailDialog"
            modal
            :header="`Detail Rekam Medis: ${selectedRekamMedis?.nomor_kunjungan}`"
            :style="{ width: '65vw', minWidth: '800px' }"
            :maximizable="true"
            :closable="true"
            @hide="closeDetailDialog"
        >
            <div v-if="selectedRekamMedis" class="mt-2 text-sm">
                <div class="flex justify-end mb-4">
                    <Button 
                        :label="isEditingAll ? 'Batal Edit' : 'Edit Seluruh Kolom'" 
                        :icon="isEditingAll ? 'pi pi-times' : 'pi pi-pencil'" 
                        :severity="isEditingAll ? 'secondary' : 'help'"
                        @click="isEditingAll = !isEditingAll" 
                    />
                </div>

                <form @submit.prevent="submitSemua" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 p-4 rounded bg-white">
                        
                        <!-- I. Anamnesis / Kunjungan -->
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mb-2">I. Anamnesis / Kunjungan</div>
                        
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Keluhan Utama</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.keluhan_utama" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.keluhan_utama || '-' }}</span>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Riwayat Penyakit Sekarang</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.riwayat_penyakit_sekarang" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.riwayat_penyakit_sekarang || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Riwayat Penyakit Dahulu</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.riwayat_penyakit_dahulu" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.riwayat_penyakit_dahulu || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Riwayat Keluarga</label>
                            <InputText v-if="isEditingAll" v-model="formAnamnesis.riwayat_keluarga" class="w-full border-gray-300" />
                            <span v-else class="font-medium">{{ formAnamnesis.riwayat_keluarga || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Alergi (Obat/Lainnya)</label>
                            <InputText v-if="isEditingAll" v-model="formAnamnesis.riwayat_alergi" class="w-full border-gray-300" placeholder="Cth: Amoxicillin" />
                            <span v-else class="font-bold whitespace-pre-wrap" :class="formAnamnesis.riwayat_alergi ? 'text-red-600' : ''">{{ formAnamnesis.riwayat_alergi || 'Tidak Ada Riwayat Alergi' }}</span>
                        </div>
                        
                        <!-- II. Tanda Vital & Antropometri -->
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mt-4 mb-2 flex justify-between items-center">
                            <span>II. Tanda Vital & Antropometri</span>
                            <div class="flex items-center gap-2 pr-2" v-if="isEditingAll">
                                <label class="text-xs font-medium text-white">Perawat Penanggung Jawab:</label>
                                <Select v-model="formAnamnesis.perawat_id" :options="perawatList" optionLabel="name" optionValue="id" placeholder="Pilih Perawat" class="w-48 p-0! text-gray-900 text-sm" filter />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 col-span-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">TTV.1 TD (mmHg)</label>
                                <InputText v-if="isEditingAll" v-model="formAnamnesis.tekanan_darah" class="border-gray-300" placeholder="120/80" />
                                <span v-else class="font-medium">{{ formAnamnesis.tekanan_darah || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">TTV.2 Nadi (x/mnt)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.nadi" inputClass="border-gray-300" />
                                <span v-else class="font-medium">{{ formAnamnesis.nadi || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">TTV.3 Suhu (°C)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.suhu" inputClass="border-gray-300" :minFractionDigits="1" />
                                <span v-else class="font-medium">{{ formAnamnesis.suhu || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">TTV.4 RR (x/mnt)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.respirasi" inputClass="border-gray-300" />
                                <span v-else class="font-medium">{{ formAnamnesis.respirasi || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Berat Badan (kg)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.berat_badan" inputClass="border-gray-300" />
                                <span v-else class="font-medium">{{ formAnamnesis.berat_badan || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Tinggi Badan (cm)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.tinggi_badan" inputClass="border-gray-300" />
                                <span v-else class="font-medium">{{ formAnamnesis.tinggi_badan || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Skala Nyeri (0-10)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.skala_nyeri" inputClass="border-gray-300" :min="0" :max="10" />
                                <span v-else class="font-bold text-red-500">{{ formAnamnesis.skala_nyeri ?? '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">IMT</label>
                                <span class="font-bold pt-1 text-gray-700">{{ calcBmi }}</span>
                            </div>
                            <div class="flex flex-col gap-1" v-if="pasien.jenis_kelamin === 'P'">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Hamil / Menyusui</label>
                                <div class="flex items-center gap-4 mt-2" v-if="isEditingAll">
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" v-model="formAnamnesis.is_hamil" @change="formAnamnesis.is_hamil ? formAnamnesis.is_menyusui = false : null" class="peer appearance-none w-5 h-5 rounded-full border-2 border-gray-300 checked:border-emerald-500 checked:bg-emerald-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-110 active:scale-95 focus:ring-2 focus:ring-emerald-200 focus:ring-offset-1 cursor-pointer">
                                            <i class="pi pi-check text-white text-[10px] absolute opacity-0 peer-checked:opacity-100 transition-opacity duration-300 pointer-events-none"></i>
                                        </div>
                                        <span class="text-xs font-medium text-gray-700 group-hover:text-emerald-600 transition-colors duration-300">Hamil</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <div class="relative flex items-center justify-center">
                                            <input type="checkbox" v-model="formAnamnesis.is_menyusui" @change="formAnamnesis.is_menyusui ? formAnamnesis.is_hamil = false : null" class="peer appearance-none w-5 h-5 rounded-full border-2 border-gray-300 checked:border-emerald-500 checked:bg-emerald-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-110 active:scale-95 focus:ring-2 focus:ring-emerald-200 focus:ring-offset-1 cursor-pointer">
                                            <i class="pi pi-check text-white text-[10px] absolute opacity-0 peer-checked:opacity-100 transition-opacity duration-300 pointer-events-none"></i>
                                        </div>
                                        <span class="text-xs font-medium text-gray-700 group-hover:text-emerald-600 transition-colors duration-300">Menyusui</span>
                                    </label>
                                </div>
                                <div v-else class="flex gap-2 font-bold text-pink-600 text-xs">
                                    <span v-if="formAnamnesis.is_hamil">Hamil</span>
                                    <span v-if="formAnamnesis.is_menyusui">Menyusui</span>
                                    <span v-if="!formAnamnesis.is_hamil && !formAnamnesis.is_menyusui" class="text-gray-500 font-normal">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- III. Pemeriksaan Dokter & Medis -->
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mt-4 mb-2 flex justify-between items-center">
                            <span>III. Pemeriksaan Dokter & Medis</span>
                            <div class="flex items-center gap-2 pr-2" v-if="isEditingAll">
                                <label class="text-xs font-medium text-white">Dokter Penanggung Jawab:</label>
                                <Select v-model="formPemeriksaan.dokter_id" :options="dokterList" optionLabel="name" optionValue="id" placeholder="Pilih Dokter" class="w-48 p-0! text-gray-900 text-sm" filter />
                            </div>
                        </div>
                        
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Pemeriksaan Fisik Lain</label>
                            <Textarea v-if="isEditingAll" v-model="formPemeriksaan.pemeriksaan_fisik" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formPemeriksaan.pemeriksaan_fisik || '-' }}</span>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Pemeriksaan Penunjang / Laborat</label>
                            <Textarea v-if="isEditingAll" v-model="formPemeriksaan.hasil_pemeriksaan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formPemeriksaan.hasil_pemeriksaan || '-' }}</span>
                        </div>
                        
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Diagnosa Medis Utama</label>
                            <InputText v-if="isEditingAll" v-model="formPemeriksaan.diagnosis_utama" class="w-full border-gray-300" />
                            <span v-else class="font-bold text-blue-700">{{ formPemeriksaan.diagnosis_utama || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Kode ICD-10</label>
                            <InputText v-if="isEditingAll" v-model="formPemeriksaan.kode_icd10" class="w-full border-gray-300" />
                            <span v-else class="font-medium">{{ formPemeriksaan.kode_icd10 || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Diagnosa Sekunder</label>
                            <InputText v-if="isEditingAll" v-model="formPemeriksaan.diagnosis_sekunder" class="w-full border-gray-300" />
                            <span v-else class="font-medium">{{ formPemeriksaan.diagnosis_sekunder || '-' }}</span>
                        </div>
                        
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Penatalaksanaan Medis (Treatment)</label>
                            <Textarea v-if="isEditingAll" v-model="formPemeriksaan.penatalaksanaan_medis" rows="3" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formPemeriksaan.penatalaksanaan_medis || '-' }}</span>
                        </div>

                        <!-- IV. Asuhan Keperawatan -->
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mt-4 mb-2">IV. Asuhan Keperawatan</div>

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Diagnosa Keperawatan</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.diagnosa_keperawatan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.diagnosa_keperawatan || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Intervensi Keperawatan</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.intervensi_keperawatan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.intervensi_keperawatan || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Implementasi Keperawatan</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.implementasi_keperawatan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.implementasi_keperawatan || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Evaluasi Keperawatan</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.evaluasi_keperawatan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.evaluasi_keperawatan || '-' }}</span>
                        </div>

                    </div>
                    <div v-if="isEditingAll" class="flex justify-end gap-2 mt-4 pt-4 border-t">
                        <Button label="Batal Simpan" severity="secondary" text @click="isEditingAll = false" />
                        <Button type="submit" label="Simpan Seluruh Rekam Medis" severity="success" icon="pi pi-check" :loading="isSaving" />
                    </div>
                </form>
            </div>
            <template #footer>
            </template>
        </Dialog>

        <!-- Dialog Edit Screening (Khusus Tab Data Screening) -->
        <Dialog
            v-model:visible="showScreeningDialog"
            modal
            :header="`Detail & Edit Data Screening: ${selectedRekamMedis?.pasien?.nama || ''}`"
            :style="{ width: '50vw', minWidth: '600px' }"
            :closable="true"
        >
            <div v-if="selectedRekamMedis" class="mt-2 text-sm">
                <form @submit.prevent="submitScreening" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 p-4 rounded bg-white">
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mb-2">Pemeriksaan Fisik & Antropometri</div>
                        
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Tinggi Badan (cm)</label>
                            <InputNumber v-model="formAnamnesis.tinggi_badan" inputClass="border-gray-300" class="w-full" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Berat Badan (kg)</label>
                            <InputNumber v-model="formAnamnesis.berat_badan" inputClass="border-gray-300" class="w-full" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">IMT (Otomatis)</label>
                            <div class="mt-1" :class="{'text-red-600 font-bold': getBmiData(formAnamnesis.tinggi_badan, formAnamnesis.berat_badan).isCritical}">
                                {{ getBmiData(formAnamnesis.tinggi_badan, formAnamnesis.berat_badan).value }} - {{ getBmiData(formAnamnesis.tinggi_badan, formAnamnesis.berat_badan).category }}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Lingkar Perut (cm)</label>
                            <InputNumber v-model="formAnamnesis.lingkar_perut" inputClass="border-gray-300" class="w-full" />
                            <div class="text-[10px] mt-1" :class="{'text-red-600 font-bold': getLpData(formAnamnesis.lingkar_perut, formAnamnesis.is_hamil, pasien.jenis_kelamin).isCritical}">
                                {{ getLpData(formAnamnesis.lingkar_perut, formAnamnesis.is_hamil, pasien.jenis_kelamin).status }}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1" v-if="pasien.jenis_kelamin === 'P'">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Kondisi Khusus</label>
                            <div class="flex items-center gap-4 mt-1">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" v-model="formAnamnesis.is_hamil" @change="formAnamnesis.is_hamil ? formAnamnesis.is_menyusui = false : null" class="peer appearance-none w-5 h-5 rounded-full border-2 border-gray-300 checked:border-emerald-500 checked:bg-emerald-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-110 active:scale-95 focus:ring-2 focus:ring-emerald-200 focus:ring-offset-1 cursor-pointer">
                                        <i class="pi pi-check text-white text-[10px] absolute opacity-0 peer-checked:opacity-100 transition-opacity duration-300 pointer-events-none"></i>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 group-hover:text-emerald-600 transition-colors duration-300">Hamil</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <div class="relative flex items-center justify-center">
                                        <input type="checkbox" v-model="formAnamnesis.is_menyusui" @change="formAnamnesis.is_menyusui ? formAnamnesis.is_hamil = false : null" class="peer appearance-none w-5 h-5 rounded-full border-2 border-gray-300 checked:border-emerald-500 checked:bg-emerald-500 shadow-sm transition-all duration-300 ease-in-out hover:scale-110 active:scale-95 focus:ring-2 focus:ring-emerald-200 focus:ring-offset-1 cursor-pointer">
                                        <i class="pi pi-check text-white text-[10px] absolute opacity-0 peer-checked:opacity-100 transition-opacity duration-300 pointer-events-none"></i>
                                    </div>
                                    <span class="text-xs font-medium text-gray-700 group-hover:text-emerald-600 transition-colors duration-300">Menyusui</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Tekanan Darah (mmHg)</label>
                            <InputText v-model="formAnamnesis.tekanan_darah" class="border-gray-300 w-full" placeholder="Cth: 120/80" />
                            <div class="text-[10px] mt-1" :class="{'text-red-600 font-bold': getTdData(formAnamnesis.tekanan_darah).isCritical}">
                                {{ getTdData(formAnamnesis.tekanan_darah).category }}
                            </div>
                        </div>
                        
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mb-2 mt-4">Pemeriksaan Laboratorium</div>
                        
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Jenis Gula Darah</label>
                            <Select v-model="formAnamnesis.jenis_gula_darah" :options="[{label: 'Gula Darah Puasa (GDP)', value: 'puasa'}, {label: 'Gula Darah Sewaktu (GDS)', value: 'sewaktu'}]" optionLabel="label" optionValue="value" placeholder="Pilih Jenis" class="w-full" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Gula Darah (mg/dL)</label>
                            <InputNumber v-model="formAnamnesis.gula_darah" inputClass="border-gray-300" class="w-full" />
                            <div class="text-[10px] mt-1" :class="{'text-red-600 font-bold': getGdData(formAnamnesis.gula_darah, formAnamnesis.jenis_gula_darah).isCritical}">
                                {{ getGdData(formAnamnesis.gula_darah, formAnamnesis.jenis_gula_darah).category }}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Asam Urat (mg/dL)</label>
                            <InputNumber v-model="formAnamnesis.asam_urat" inputClass="border-gray-300" class="w-full" />
                            <div class="text-[10px] mt-1" :class="{'text-red-600 font-bold': getAuData(formAnamnesis.asam_urat, pasien.jenis_kelamin).isCritical}">
                                {{ getAuData(formAnamnesis.asam_urat, pasien.jenis_kelamin).category }}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Kolesterol (mg/dL)</label>
                            <InputNumber v-model="formAnamnesis.kolesterol" inputClass="border-gray-300" class="w-full" />
                            <div class="text-[10px] mt-1" :class="{'text-red-600 font-bold': getCholData(formAnamnesis.kolesterol).isCritical}">
                                {{ getCholData(formAnamnesis.kolesterol).category }}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Hemoglobin (g/dL)</label>
                            <InputNumber v-model="formAnamnesis.hemoglobin" inputClass="border-gray-300" class="w-full" />
                            <div class="text-[10px] mt-1" :class="{'text-red-600 font-bold': getHbData(formAnamnesis.hemoglobin).isCritical}">
                                {{ getHbData(formAnamnesis.hemoglobin).category }}
                            </div>
                        </div>
                        
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mb-2 mt-4">Kesimpulan & Edukasi</div>

                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Tindak Lanjut / Edukasi</label>
                            <Select v-model="formAnamnesis.tindak_lanjut" :options="[{label:'Edukasi',value:'edukasi'},{label:'Kembali ke Faskes 1',value:'rujuk'}]" optionLabel="label" optionValue="value" placeholder="Pilih Tindak Lanjut" class="w-full" />
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Keterangan / Pesan</label>
                            <Textarea v-model="formAnamnesis.keterangan_tindak_lanjut" rows="2" class="w-full border-gray-300" />
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-2 mt-4 pt-4 border-t">
                        <Button label="Batal" severity="secondary" text @click="showScreeningDialog = false" />
                        <Button type="submit" label="Simpan Screening" severity="success" icon="pi pi-check" :loading="isSaving" />
                    </div>
                </form>
            </div>
        </Dialog>

    </AppLayout>
</template>

<style scoped>
/* Optional custom styling for the horizontal gridlines if needed */
:deep(.p-datatable.p-datatable-gridlines .p-datatable-header) {
    border-width: 1px 1px 0 1px;
}
</style>

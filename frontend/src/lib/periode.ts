import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const BULAN = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
]

const BULAN_SINGKAT = [
  'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
  'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des',
]

export function formatPeriode(bulan: number, tahun: number): string {
  return `${BULAN[bulan - 1]} ${tahun}`
}

export function formatPeriodeSingkat(bulan: number, tahun: number): string {
  return `${BULAN_SINGKAT[bulan - 1]} ${String(tahun).slice(2)}`
}

export function periodeKey(bulan: number, tahun: number): string {
  return `${tahun}-${String(bulan).padStart(2, '0')}`
}

/**
 * Periode (bulan/tahun) yang disinkronkan ke query string URL (?bulan=&tahun=),
 * supaya filter periode tetap "nempel" saat pindah halaman (Dashboard <-> Daftar
 * Puskesmas <-> Rekap) dan tahan di-refresh/di-share sebagai link.
 */
export function useQueryPeriode() {
  const route = useRoute()
  const router = useRouter()
  const now = new Date()

  return computed({
    get: () => ({
      bulan: Number(route.query.bulan) || now.getMonth() + 1,
      tahun: Number(route.query.tahun) || now.getFullYear(),
    }),
    set: (value: { bulan: number; tahun: number }) => {
      router.replace({ query: { ...route.query, bulan: String(value.bulan), tahun: String(value.tahun) } })
    },
  })
}

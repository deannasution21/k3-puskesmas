export const kategoriBadgeClasses: Record<string, string> = {
  tidak_memenuhi: 'bg-red-100 text-red-700',
  perlu_perbaikan_mayor: 'bg-orange-100 text-orange-700',
  memenuhi_sebagian: 'bg-amber-100 text-amber-700',
  memenuhi_standar: 'bg-green-100 text-green-700',
  unggul: 'bg-emerald-200 text-emerald-800',
}

export const kategoriBarClasses: Record<string, string> = {
  tidak_memenuhi: 'bg-red-500',
  perlu_perbaikan_mayor: 'bg-orange-500',
  memenuhi_sebagian: 'bg-amber-500',
  memenuhi_standar: 'bg-green-500',
  unggul: 'bg-emerald-600',
}

export const kategoriOrder = [
  'tidak_memenuhi',
  'perlu_perbaikan_mayor',
  'memenuhi_sebagian',
  'memenuhi_standar',
  'unggul',
] as const

export interface PenilaianDistribusi {
  tidak_memenuhi: number
  perlu_perbaikan_mayor: number
  memenuhi_sebagian: number
  memenuhi_standar: number
  unggul: number
}

export interface PenilaianPuskesmas {
  persen: number
  kuesioner: { terpenuhi: number; total: number }
  observasi: { terpenuhi: number; total: number }
  total_terpenuhi: number
  total_item: number
  kategori_key: string
  kategori_label: string
}

export interface PenilaianPuskesmasRow extends PenilaianPuskesmas {
  puskesmas_id: number
  nama: string
}

export interface PenilaianRingkasan {
  rata_rata: number
  kategori_key: string
  kategori_label: string
  distribusi: PenilaianDistribusi
  list: PenilaianPuskesmasRow[]
}

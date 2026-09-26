export interface QuestionnaireRecapItem {
  kategori_kode: string
  kategori: string
  nomor: number
  pertanyaan: string
  ya: number
  tidak: number
  total_isi: number
}

export interface ObservationRecapItem {
  kategori_kode: string
  kategori: string
  nomor: number
  item_teks: string
  skala_kondisi: 'baik_buruk' | 'baik_rusak_ringan_rusak_berat'
  ada: number
  tidak_ada: number
  kondisi: Record<string, number>
  total_isi: number
}

export interface Insight {
  level: 'critical' | 'warning' | 'info'
  text: string
}

export const insightLevelIcon: Record<Insight['level'], string> = {
  critical:
    'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
  warning: 'M12 9v3.75m0 3.75h.007v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z',
  info: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
}

export const insightLevelClasses: Record<Insight['level'], string> = {
  critical: 'bg-red-50 text-red-700',
  warning: 'bg-amber-50 text-amber-700',
  info: 'bg-brand-50 text-brand-700',
}

export function buildInsights(params: {
  totalPuskesmas: number
  jumlahKuesioner: number
  jumlahObservasi: number
  kuesioner: QuestionnaireRecapItem[]
  observasi: ObservationRecapItem[]
}): Insight[] {
  const { totalPuskesmas, jumlahKuesioner, jumlahObservasi, kuesioner, observasi } = params
  const list: Insight[] = []

  const kuesionerBelum = totalPuskesmas - jumlahKuesioner
  const observasiBelum = totalPuskesmas - jumlahObservasi

  if (totalPuskesmas > 0 && kuesionerBelum > 0) {
    list.push({
      level: kuesionerBelum > totalPuskesmas / 2 ? 'critical' : 'warning',
      text: `${kuesionerBelum} dari ${totalPuskesmas} puskesmas belum mengisi Kuesioner K3 pada periode ini. Segera ingatkan sebelum periode ditutup.`,
    })
  }

  if (totalPuskesmas > 0 && observasiBelum > 0) {
    list.push({
      level: observasiBelum > totalPuskesmas / 2 ? 'critical' : 'warning',
      text: `${observasiBelum} dari ${totalPuskesmas} puskesmas belum mengisi Observasi Sarana Prasarana pada periode ini. Segera ingatkan sebelum periode ditutup.`,
    })
  }

  const kuesionerProblems = kuesioner
    .filter((i) => i.total_isi > 0 && i.tidak / i.total_isi >= 0.3)
    .map((i) => ({ ...i, ratio: i.tidak / i.total_isi }))
    .sort((a, b) => b.ratio - a.ratio)
    .slice(0, 3)

  for (const item of kuesionerProblems) {
    list.push({
      level: item.ratio >= 0.5 ? 'critical' : 'warning',
      text: `${item.tidak} dari ${item.total_isi} puskesmas (${Math.round(item.ratio * 100)}%) menjawab "Tidak" untuk: "${item.pertanyaan}" (${item.kategori}).`,
    })
  }

  const observasiKritis = observasi
    .map((i) => ({ ...i, rusakBerat: i.kondisi.rusak_berat ?? 0, buruk: i.kondisi.buruk ?? 0 }))
    .filter((i) => i.rusakBerat > 0 || i.buruk > 0)
    .sort((a, b) => b.rusakBerat + b.buruk - (a.rusakBerat + a.buruk))
    .slice(0, 3)

  for (const item of observasiKritis) {
    const count = item.rusakBerat > 0 ? item.rusakBerat : item.buruk
    const label = item.rusakBerat > 0 ? 'Rusak Berat' : 'Buruk'
    list.push({
      level: 'critical',
      text: `${count} puskesmas melaporkan kondisi "${label}" pada: "${item.item_teks}" (${item.kategori}). Perlu tindak lanjut perbaikan segera.`,
    })
  }

  const observasiTidakAda = observasi
    .filter((i) => i.total_isi > 0 && i.tidak_ada / i.total_isi >= 0.3)
    .map((i) => ({ ...i, ratio: i.tidak_ada / i.total_isi }))
    .sort((a, b) => b.ratio - a.ratio)
    .slice(0, 2)

  for (const item of observasiTidakAda) {
    list.push({
      level: item.ratio >= 0.5 ? 'critical' : 'warning',
      text: `${item.tidak_ada} dari ${item.total_isi} puskesmas (${Math.round(item.ratio * 100)}%) melaporkan "Tidak Ada" untuk: "${item.item_teks}" (${item.kategori}).`,
    })
  }

  const order = { critical: 0, warning: 1, info: 2 }
  return list.sort((a, b) => order[a.level] - order[b.level]).slice(0, 6)
}

export type OverallLevel = 'critical' | 'warning' | 'good' | 'empty'

export function overallLevelFromInsights(insights: Insight[], jumlahKuesioner: number, jumlahObservasi: number): OverallLevel {
  if (jumlahKuesioner === 0 && jumlahObservasi === 0) return 'empty'
  if (insights.some((i) => i.level === 'critical')) return 'critical'
  if (insights.some((i) => i.level === 'warning')) return 'warning'
  return 'good'
}

export function overallBadgeFromLevel(level: OverallLevel): { label: string; classes: string } {
  switch (level) {
    case 'critical':
      return { label: 'Perlu Tindakan Segera', classes: 'bg-red-100 text-red-700' }
    case 'warning':
      return { label: 'Perlu Perhatian', classes: 'bg-amber-100 text-amber-700' }
    case 'good':
      return { label: 'Kondisi Baik', classes: 'bg-green-100 text-green-700' }
    default:
      return { label: 'Belum Ada Data', classes: 'bg-gray-100 text-gray-500' }
  }
}

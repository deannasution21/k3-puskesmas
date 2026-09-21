const BULAN = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
]

export function formatPeriode(bulan: number, tahun: number): string {
  return `${BULAN[bulan - 1]} ${tahun}`
}

export function periodeKey(bulan: number, tahun: number): string {
  return `${tahun}-${String(bulan).padStart(2, '0')}`
}

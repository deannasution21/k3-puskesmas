export interface Attachment {
  id: number
  item_id: number
  original_name: string
  mime_type: string
  size: number
}

export function groupAttachmentsByItem(attachments: Attachment[]): Record<number, Attachment[]> {
  const map: Record<number, Attachment[]> = {}
  for (const a of attachments) {
    ;(map[a.item_id] ??= []).push(a)
  }
  return map
}

export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

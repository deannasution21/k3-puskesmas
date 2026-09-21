export function extractErrorMessage(error: unknown): string {
  const data = (error as any)?.response?.data
  if (!data) return 'Terjadi kesalahan. Silakan coba lagi.'

  if (data.errors) {
    const first = Object.values(data.errors)[0]
    if (Array.isArray(first) && first.length > 0) return first[0] as string
  }

  if (typeof data.message === 'string') return data.message

  return 'Terjadi kesalahan. Silakan coba lagi.'
}

export function extractFieldErrors(error: unknown): Record<string, string> {
  const errors = (error as any)?.response?.data?.errors
  if (!errors) return {}

  const result: Record<string, string> = {}
  for (const [field, messages] of Object.entries(errors)) {
    if (Array.isArray(messages) && messages.length > 0) {
      result[field] = messages[0] as string
    }
  }
  return result
}

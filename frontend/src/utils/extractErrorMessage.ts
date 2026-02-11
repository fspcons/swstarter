/**
 * Pulls a user-friendly message out of an Axios error response,
 * falling back to the supplied default when none is available.
 */
export function extractErrorMessage(err: unknown, fallback: string): string {
  if (err && typeof err === 'object' && 'response' in err && (err as any).response?.data?.message) {
    return (err as any).response.data.message;
  }
  return fallback;
}

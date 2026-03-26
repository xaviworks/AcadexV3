/**
 * Get stable device fingerprint
 * Uses localStorage to persist the same fingerprint across sessions
 * Generates SHA-256 hash based on stable browser properties only
 */
export async function getDeviceFingerprint() {
  const fingerprintSource = buildFingerprintSource();
  const stored = readStoredFingerprint();

  try {
    if (stored && stored.length === 64) {
      return stored;
    }

    // Generate SHA-256 hash
    const encoder = new TextEncoder();
    const data = encoder.encode(fingerprintSource);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const fingerprint = hashArray.map((b) => b.toString(16).padStart(2, '0')).join('');

    return persistFingerprint(fingerprint);
  } catch (error) {
    console.error('Error generating fingerprint:', error);

    const fingerprint = generateFallbackFingerprint(fingerprintSource);
    return persistFingerprint(fingerprint);
  }
}

/**
 * Generate the stable browser fingerprint source string.
 */
function buildFingerprintSource() {
  return JSON.stringify({
    userAgent: navigator.userAgent,
    language: navigator.language,
    languages: navigator.languages ? navigator.languages.join(',') : '',
    platform: navigator.platform,
    screenResolution: `${screen.width}x${screen.height}`,
    colorDepth: screen.colorDepth,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    timezoneOffset: new Date().getTimezoneOffset(),
    hardwareConcurrency: navigator.hardwareConcurrency || 'unknown',
    deviceMemory: navigator.deviceMemory || 'unknown',
  });
}

/**
 * Fallback fingerprint using deterministic hashing when crypto.subtle is unavailable.
 * Returns a 64-character value so it stays compatible with trusted-device records.
 */
function generateFallbackFingerprint(source) {
  const segments = [
    hashSegment(`a:${source}`),
    hashSegment(`b:${source}`),
    hashSegment(`c:${source}`),
    hashSegment(`d:${source}`),
    hashSegment(`e:${source}`),
    hashSegment(`f:${source}`),
    hashSegment(`g:${source}`),
    hashSegment(`h:${source}`),
  ];

  return segments.join('').slice(0, 64);
}

function hashSegment(data) {
  let hash = 0;
  for (let i = 0; i < data.length; i++) {
    const char = data.charCodeAt(i);
    hash = (hash << 5) - hash + char;
    hash |= 0;
  }

  return Math.abs(hash).toString(16).padStart(8, '0');
}

function readStoredFingerprint() {
  try {
    return localStorage.getItem('device_fingerprint');
  } catch {
    return null;
  }
}

function persistFingerprint(fingerprint) {
  try {
    localStorage.setItem('device_fingerprint', fingerprint);
  } catch {
    // Storage can be unavailable in private or restricted browsing contexts.
  }

  return fingerprint;
}

/**
 * Inject fingerprint into a form
 */
export function injectFingerprint(formSelector) {
  getDeviceFingerprint().then((fingerprint) => {
    const form = document.querySelector(formSelector);
    if (form) {
      let input = form.querySelector('input[name="device_fingerprint"]');
      if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'device_fingerprint';
        form.appendChild(input);
      }
      input.value = fingerprint;
    }
  });
}

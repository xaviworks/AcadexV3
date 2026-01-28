/**
 * Get stable device fingerprint
 * Uses localStorage to persist the same fingerprint across sessions
 * Generates SHA-256 hash based on stable browser properties only
 */
export async function getDeviceFingerprint() {
  try {
    // Check localStorage first for existing fingerprint
    const stored = localStorage.getItem('device_fingerprint');
    if (stored) {
      return stored;
    }

    // Generate new fingerprint from stable browser properties
    const fingerprintData = JSON.stringify({
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

    // Generate SHA-256 hash
    const encoder = new TextEncoder();
    const data = encoder.encode(fingerprintData);
    const hashBuffer = await crypto.subtle.digest('SHA-256', data);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const fingerprint = hashArray.map((b) => b.toString(16).padStart(2, '0')).join('');

    // Store in localStorage for future use
    localStorage.setItem('device_fingerprint', fingerprint);

    return fingerprint;
  } catch (error) {
    console.error('Error generating fingerprint:', error);
    return generateFallbackFingerprint();
  }
}

/**
 * Fallback fingerprint using simple hash (only if crypto.subtle fails)
 */
function generateFallbackFingerprint() {
  const data = [
    navigator.userAgent,
    navigator.language,
    screen.colorDepth,
    screen.width + 'x' + screen.height,
    new Date().getTimezoneOffset(),
  ].join('|||');

  let hash = 0;
  for (let i = 0; i < data.length; i++) {
    const char = data.charCodeAt(i);
    hash = (hash << 5) - hash + char;
    hash = hash & hash;
  }

  // Pad to make it longer and more unique
  const baseHash = Math.abs(hash).toString(16);
  return baseHash.padStart(32, '0');
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

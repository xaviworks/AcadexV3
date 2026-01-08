/**
 * Alpine.js Debug Helper
 * Add this script to debug Alpine initialization issues
 */

console.log('🔍 Alpine Debug Script Loaded');

// Check if Alpine is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('📋 DOM Content Loaded');
    console.log('Alpine available:', typeof window.Alpine !== 'undefined');
    console.log('jQuery available:', typeof window.$ !== 'undefined');
    console.log('Bootstrap available:', typeof window.bootstrap !== 'undefined');
    console.log('bootbox available:', typeof window.bootbox !== 'undefined');
    console.log('Chart available:', typeof window.Chart !== 'undefined');
    console.log('Swal available:', typeof window.Swal !== 'undefined');
});

// Check Alpine after it should have started
setTimeout(() => {
    console.log('⏰ After 1 second:');
    console.log('Alpine available:', typeof window.Alpine !== 'undefined');
    if (window.Alpine) {
        console.log('✅ Alpine initialized successfully');
    } else {
        console.error('❌ Alpine failed to initialize!');
    }
}, 1000);

// Listen for Alpine init event
document.addEventListener('alpine:init', () => {
    console.log('🎉 Alpine:init event fired');
});

document.addEventListener('alpine:initialized', () => {
    console.log('✅ Alpine fully initialized');
});

// Check for announcement popup
setTimeout(() => {
    const announcementBackdrop = document.querySelector('.announcement-backdrop');
    const announcementModal = document.querySelector('.announcement-modal');
    
    console.log('📢 Announcement Debug:');
    console.log('Backdrop element:', announcementBackdrop);
    console.log('Modal element:', announcementModal);
    
    if (announcementBackdrop) {
        const display = window.getComputedStyle(announcementBackdrop).display;
        console.log('Backdrop display:', display);
    }
}, 2000);

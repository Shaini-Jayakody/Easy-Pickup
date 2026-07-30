/**
 * Invoice Print Script
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-print if URL parameter is set
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('auto_print') === '1') {
        setTimeout(function() {
            window.print();
        }, 500);
    }

    // Print button click handler
    const printBtn = document.querySelector('.btn-print');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }

    console.log('Invoice print script loaded!');
});
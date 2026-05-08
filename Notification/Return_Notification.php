<?php
/**
 * Return_Notification.php
 * Handles return confirmation dialogs using SweetAlert2
 */
?>
<script>
/**
 * Triggers a premium confirmation dialog for returning books
 * @param {number|string} detailId - The ID of the loan detail
 * @param {string} bookTitle - The title of the book
 * @param {string} readerName - The name of the reader (optional)
 */
function confirmReturn(detailId, bookTitle, readerName = '') {
    let text = "Register the return of '" + bookTitle + "'?";
    if (readerName) {
        text = "Register the return of '" + bookTitle + "' from " + readerName + "?";
    }

    Swal.fire({
        title: 'Check-in Asset?',
        text: text,
        icon: 'success',
        iconColor: '#10b981',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, process return',
        cancelButtonText: 'Back',
        showClass: { popup: '', backdrop: '' },
        hideClass: { popup: '', backdrop: '' }
    }).then((result) => {
        if (result.isConfirmed) {
            const url = new URL(window.location.href);
            url.searchParams.set('return_detail_id', detailId);
            window.location.href = url.href;
        }
    });
}

/**
 * Triggers confirmation for bulk returns in return.php
 * @param {Event} event - The form submission event
 */
function confirmBulkReturn(event) {
    event.preventDefault();
    const form = event.target;
    const selectedCount = form.querySelectorAll('input[name="return_details[]"]:checked').length;

    if (selectedCount === 0) {
        Swal.fire({
            icon: 'error',
            title: 'No Books Selected',
            text: 'Please select at least one book to return.',
            confirmButtonColor: '#3b82f6',
            showClass: { popup: '', backdrop: '' },
            hideClass: { popup: '', backdrop: '' }
        });
        return;
    }

    Swal.fire({
        title: 'Process Returns?',
        text: "You are about to process return for " + selectedCount + " book(s). Continue?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Confirm Returns',
        showClass: { popup: '', backdrop: '' },
        hideClass: { popup: '', backdrop: '' }
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>

<?php
/**
 * Delete_notification.php
 * Handles delete confirmation dialogs using SweetAlert2
 */
?>
<script>
/**
 * Triggers a premium confirmation dialog for deletion
 * @param {number|string} id - The ID of the item to delete
 * @param {string} title - The display name of the item
 * @param {string} type - The type of item (book, reader, etc.) for text customization
 */
function confirmDelete(id, title, type = 'item', targetUrl = null) {
    let displayTitle = 'Delete ' + type.charAt(0).toUpperCase() + type.slice(1) + '?';
    let displayText = "Are you sure you want to completely delete '" + title + "'? This action cannot be undone.";
    let confirmBtn = 'Yes, delete ' + type;

    if (type === 'reader') {
        displayText = "Are you sure you want to completely erase reader '" + title + "'? All loan statistics for this user will be erased.";
    } else if (type === 'transaction') {
        displayTitle = 'Purge Record?';
        displayText = "Erase the borrowing trace for '" + title + "'?";
        confirmBtn = 'Delete Traces';
    }

    Swal.fire({
        title: displayTitle,
        text: displayText,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: confirmBtn,
        cancelButtonText: 'Cancel',
        showClass: { popup: '', backdrop: '' },
        hideClass: { popup: '', backdrop: '' }
    }).then((result) => {
        if (result.isConfirmed) {
            // If targetUrl is provided, go there. Otherwise, use current URL.
            if (targetUrl) {
                window.location.href = targetUrl + (targetUrl.includes('?') ? '&' : '?') + 'id=' + id;
            } else {
                const url = new URL(window.location.href);
                url.searchParams.set('delete', id);
                window.location.href = url.href;
            }
        }
    });
}
</script>

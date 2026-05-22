<?php
/**
 * JS Notification Templates
 */
?>
<script>
function confirmReturn(detailId, bookTitle) {
    Swal.fire({
        title: 'Return Book?',
        text: bookTitle,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Confirm'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href += (window.location.href.includes('?') ? '&' : '?') + 'return_detail_id=' + detailId;
        }
    });
}

function confirmDelete(id, name, type, url) {
    Swal.fire({
        title: 'Delete ' + type + '?',
        text: name,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url + '?id=' + id;
        }
    });
}
</script>

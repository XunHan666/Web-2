<?php
require_once '../env/config.php';
$page_title = 'My Loans';
include '../inc/header.php';

$rid = $reader['id'];

$res = mysqli_query($db_connect, "
    SELECT l.id loan_id, l.borrow_date, l.due_date, l.status loan_status,
           b.title, b.id book_id, ld.status item_status, ld.return_date,
           DATEDIFF(l.due_date, CURDATE()) days_left
    FROM loans l
    JOIN loan_details ld ON l.id=ld.loan_id
    JOIN book_copies bc  ON ld.book_copy_id=bc.id
    JOIN books b         ON bc.book_id=b.id
    WHERE l.reader_id=$rid
    ORDER BY l.borrow_date DESC, l.id DESC
");

// Group by loan
$loans = [];
while ($r = mysqli_fetch_assoc($res)) {
    $id = $r['loan_id'];
    $loans[$id] ??= ['borrow_date'=>$r['borrow_date'],'due_date'=>$r['due_date'],'status'=>$r['loan_status'],'days_left'=>$r['days_left'],'items'=>[]];
    $loans[$id]['items'][] = ['title'=>$r['title'],'book_id'=>$r['book_id'],'status'=>$r['item_status'],'return_date'=>$r['return_date']];
}
?>

include 'views/my_loans_display.php';
include '../inc/footer.php';

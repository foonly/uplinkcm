<?php

/*email invoice to contact*/

require 'invoice_gen.php';

$email_id= $_GET['eid'];

/*account email*/
$sent = "$acc_hold_r[email]";
$accountname = "$acc_hold_r[name]";
/*customer email */
if ($cl_r[email]) {
	$tocontact = "$cl_r[email]";
} else {
	$tocontact = "";
}


/*update invoice print status */

$query = "
			update $acco.invoice_out
			set		emailed=now()
			where	id=$inoid
		";
$ch = pg_query($conn, $query);
	
$invoice->invoice($info);

//$invoice->fetch("uplink_".$in_r[ident]);

$attachment = chunk_split(base64_encode($invoice->fetch()));

$due = date('d.m.Y', strtotime($in_r['due_date_out']));
$amount = $total+$vattot;

/* send mail to user */
$random_hash = md5(date('r', time()));
$charset = "\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
$from = $sent;
$to = $tocontact;
$headers = "From:" . $from.$charset;
$subject = "{$lng->__('New invoice from')} ".$accountname;
$plainpart = "
--PHP-mixed-$random_hash
Content-Type: text/plain; charset=UTF-8\r\n
{$lng->__('New invoice from')} ".$accountname." \r\n
{$lng->__('Invoice number')}: $in_r[ident] \r\n
{$lng->__('Due date')}: $due \r
{$lng->__('Amount')}: $amount €\r\n
{$lng->__('See attachment for pdf')}\r\n
{$lng->__('Virtual barcode')}: $virtual";
$attachpart = "
--PHP-mixed-$random_hash
Content-Type: application/pdf; name=invoice_".$acc_hold_r[id]."_".$in_r[ident].".pdf
Content-Transfer-Encoding: base64 
Content-Disposition: attachment 

$attachment
--PHP-mixed-$random_hash--";

$ret_url = 'index.php?template=main';
$icon = 'layout/img/icon_succ.png';

$body = $plainpart.$attachpart;

/* send the mail */
$mail_sent = @mail($to,$subject,$body,$headers);

$mail_sent ? $message = "Mail sent" : $message = "Mail failed"; 

header("Refresh: 2; URL=".$ret_url);

echo "
	<div class='messagebox'>
		<img class='messageicon' src='$icon' alt='$message'>
		<p class='messagetext'>$message</p>
	</div>
	";

?>
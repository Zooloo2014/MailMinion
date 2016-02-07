# MailMinion
A PHP wrapper class to process all or only new emails in an IMAP or POP3 mailbox sequentially.

This class allows you to:


1. Iterate over NEW or ALL messages in the inbox
2. Parses each message into Headers, Body and Attachments

The mailbox is automatically opened, processed in first-to-last order and automatically closed after reaching the last mail in the list.


`
<?php
require_once("minion.class.php");
$parms = array(
     "host"      => "localhost", 
	 "port"      => 143, 
	 "user"      => "username",
	 "pass"      => "password",
	 "mailType"  => MailType::IMAP,
	 "ssl"       => FALSE,
	 "validate"  => FALSE
	 );

$mm = new MailMinion($parms);
$mm->getFirst(SelectType::ALL);
if (!$mm->atEnd())
{
    echo "Message Count: " . $mm->getMailCount() . "<br>\r";

    while (!$mm->atEnd())
    {
        $mm->dump();
        $mm->getNext();
    }
}
else
{
    echo "No mails to process<br>\r";
}

?>
`

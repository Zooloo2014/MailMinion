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

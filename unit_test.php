<?php


require_once("minion.class.php");


$parms = array(
     "host"      => "localhost", 
     "port"      => 993, 
     "user"      => "username",
     "pass"      => "password",
     "mailType"  => MailType::IMAP,
     "ssl"       => TRUE,
     "validate"  => FALSE
);
		 

$mm = new MailMinion($parms);
$mm->getFirst(SelectType::UNSEEN);
if ($mm->getError() == MinionError::NO_ERROR)
{
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
}
else
{
    echo "Error opening mailbox<br>\r";
}


?>

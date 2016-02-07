<?php


class MailType
{
    const IMAP = 1;
    const POP3 = 2;
}


class SelectType
{
    const ALL    = 1;
    const UNSEEN = 2;
}


class MinionError
{
    const NO_ERROR      = 0;
    const LOGIN_FAILED  = 1;
}


class MailMinion 
{

    var $access;
    var $user;
    var $pass;

    var $error;

    var $is_imap;
    var $inbox;
    var $mids;
    var $midCount;
    var $fin;

    var $currMsgNo;
    var $headers;
    var $body;
    var $struct;
    var $attach;


    public function __construct($parms) 
    {
        $this->fin = TRUE;


        $this->access = "{" . $parms['host'] . ":";


        if ($parms['port'] == 0)
        {
            if ($parms['mailType'] == MailType::IMAP)
            {
                if ($parms['ssl'] == TRUE)
                    $this->access .= "993/";
                else
                    $this->access .= "143/";
            }
            else
            {
                if ($parms['ssl'] == TRUE)
	            $this->access .= "995/";
		else
		    $this->access .= "110/";
	    }
	}
	else
	{
	    $this->access .= $parms['port'] . "/";
	}


        if ($parms['mailType'] == MailType::IMAP)
        {
	    $this->is_imap = TRUE;
            $this->access .= "imap/";
        }
        else
        {
            $this->is_imap = FALSE;
            $this->access .= "pop3/";
        }


        if ($parms['ssl'] == TRUE)
	    $this->access .= "ssl";

        if ($parms['validate'] != TRUE)
            $this->access .= "/novalidate-cert";


        $this->access .= "}INBOX";


        $this->user = $parms['user'];
        $this->pass = $parms['pass'];
    }

	
    protected function open($searchType)
    {
        if ($this->inbox = imap_open($this->access, $this->user, $this->pass))
        {
            $this->mids = imap_search($this->inbox, $searchType);
            $this->midCount = count($this->mids);
            if (($this->midCount == 1) && (empty($this->mids[$this->midCount - 1])))
                $this->midCount = 0;
        }
        else
        {			
            $this->error = MinionError::LOGIN_FAILED;	
        }
    }


    public function getError()
    {
        return $this->error;
    }


    protected function checkFin()
    {
        if ($this->ptr < $this->midCount)
        {
            $this->fin = FALSE;
        }
        else
        {
            $this->fin = TRUE;
        }
    }


    public function getMailCount()
    {
        return $this->midCount;
    }


    protected function parseMsg($structure, $msgNum)
    {
        $attachments = array();

        if(isset($structure->parts) && count($structure->parts))
        {

            for($i = 0; $i < count($structure->parts); $i++) 
			{

                $attachments[$i] = array(
                    'is_attachment' => FALSE,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );
		
                if($structure->parts[$i]->ifdparameters) 
                {
                    foreach($structure->parts[$i]->dparameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'filename') 
                        {
                            $attachments[$i]['is_attachment'] = TRUE;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }
		
                if($structure->parts[$i]->ifparameters) 
                {
                    foreach($structure->parts[$i]->parameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'name') 
                        {
                            $attachments[$i]['is_attachment'] = TRUE;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }
		
                if($attachments[$i]['is_attachment']) 
                {
                    $attachments[$i]['attachment'] = imap_fetchbody($this->inbox, $msgNum, $i+1);
                    if($structure->parts[$i]->encoding == 3) 
                    {
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    }
                    elseif($structure->parts[$i]->encoding == 4) 
                    {
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }
            }
        }

        return $attachments;
    }


    protected function loadHeaders($msgNum)
    {
        $this->headers = imap_header($this->inbox, $msgNum);
    }


    protected function loadBody($msgNum)
    {
        $this->body   = imap_body($this->inbox, $msgNum);
        $this->struct = imap_fetchstructure($this->inbox, $msgNum);
        $this->attach = $this->parseMsg($this->struct, $msgNum);
	}


    protected function processMsg()
    {
        if (!$this->fin)
        {
            $this->currMsgNo = $this->mids[$this->ptr];
            $this->loadHeaders($this->currMsgNo);
            $this->loadBody($this->currMsgNo);
        }
    }


    public function atEnd()
    {
        return $this->fin;
    }


    public function getFirst($selectType)
    {
        if ($selectType == selectType::UNSEEN)
        {
            $this->open('UNSEEN');
	}
        else
        {
            $this->open('ALL');
        }

        $this->ptr = 0;
        $this->checkFin();

        if (!$this->fin)
        {
            $this->processMsg();
        }
    }


    public function getNext()
    {
        $this->ptr++;
        $this->checkFin();
        if ($this->fin == TRUE)
        {			
            $this->close();
        }
        else
        {			
            $this->processMsg();
        }
    }


    public function getHeaders()
    {
        return $headers;
    }


    public function getBody()
    {
        return $body;
    }


    public function getAttachments()
    {
        return $attach;
    }


    public function close()
    {
        if ((isset($this->inbox)) && ($this->inbox != NULL))
            imap_close($this->inbox);
    }


    public function dump()
    {
        echo "HEADERS:<br>\r";      print_r($this->headers);  echo "<br>\r";
        echo "BODY:<br>\r";         print_r($this->body);     echo "<br>\r";
        echo "ATTACHMENTS:<br>\r";  print_r($this->attach);   echo "<br>\r";
    }
}


?>

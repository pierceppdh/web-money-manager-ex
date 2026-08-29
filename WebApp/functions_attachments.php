<?php

##########################
#  Attachments function  #
##########################
class attachments
{
    private static function filename_matches_transaction($entry, $TrID)
        {
            return preg_match('/^Transaction_' . preg_quote((string)$TrID, '/') . '_Attach/', $entry) === 1;
        }

    public static function get_attachments_filename_array($TrID,$bIncludeZero=false)
        {
            $i=0;
            $AttachmentsArray = array();

            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if
                        (
                            self::filename_matches_transaction($entry, $TrID)
                            ||
                            ($bIncludeZero && self::filename_matches_transaction($entry, 0))
                        )
                            {
                            $AttachmentsArray[$i] = $entry;
                            $i++;
                            }
                    }
                    closedir($handle);
                }
            return $AttachmentsArray;
        }

    public static function get_number_of_attachments($TrID)
        {
            $LastAttachNum = 0;
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (self::filename_matches_transaction($entry, $TrID))
                        {
                            $AttachNumb = substr($entry,strpos($entry,"Attach")+6,strpos($entry,".")-(strpos($entry,"Attach")+6));
                            if ($AttachNumb > $LastAttachNum)
                                $LastAttachNum = $AttachNumb;
                        }
                    }
                    closedir($handle);
                }
            return $LastAttachNum;
        }

    public static function delete_zero()
        {
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (self::filename_matches_transaction($entry, 0))
                        {
                            unlink(costant::attachments_folder()."/".$entry);
                        }
                    }
                    closedir($handle);
                }
            return true;
        }

    public static function rename_zero($TrID)
        {
            if ($handle = opendir(costant::attachments_folder()))
                {
                    while (false !== ($entry = readdir($handle)))
                    {
                        if (self::filename_matches_transaction($entry, 0))
                        {
                            $NewFileName = str_replace("Transaction_0","Transaction_".$TrID,$entry);
                            rename(costant::attachments_folder()."/".$entry,costant::attachments_folder()."/".$NewFileName);
                        }
                    }
                    closedir($handle);
                }
            return true;
        }

    public static function delete_group($TrID_Array)
        {
            $N = count($TrID_Array);
            if ($handle = opendir(costant::attachments_folder()))
            {
                while (false !== ($entry = readdir($handle)))
                {
                    for($i=0; $i < $N; $i++)
                    {
                        $TrID = $TrID_Array[$i];
                        if (self::filename_matches_transaction($entry, $TrID))
                        {
                            unlink(costant::attachments_folder()."/".$entry);
                        }
                    }
                }
                closedir($handle);
            }
            return true;
        }
    public static function delete_attachment_by_name($FileName)
        {
            $FullPath = costant::attachments_folder()."/".$FileName;
            if (!empty($FileName) && file_exists($FullPath))
                unlink($FullPath);
        }
}

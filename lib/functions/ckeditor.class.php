<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource ckeditor.class.php
 *
 **/
require_once '../../third_party/ckeditorWrapper/CKEditorPHPWrapper.php';

class ckeditorInterface
{

    public $InstanceName;

    public $Value;

    public $Editor;

    public $config;

    /**
     */
    public function __construct($instanceName)
    {
        $this->InstanceName = $instanceName;
        $this->Value = '';
        $this->Editor = new CKEditor();
        $this->Editor->returnOutput = true;
    }

    /**
     */
    public function Create()
    {
        echo $this->CreateHtml($rows, $cols);
    }

    /**
     */
    public function CreateHtml($config = [])
    {
        return $this->Editor->editor($this->InstanceName, $this->Value, $config);
    }
}

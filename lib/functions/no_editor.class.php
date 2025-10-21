<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  no_editor.class.php
 *
 **/
class no_editor
{

    public $InstanceName;

    public $Value;

    public $rows = 8;

    public $cols = 80;

    public function __construct($instanceName)
    {
        $this->InstanceName = $instanceName;
        $this->Value = '';
    }

    public function Create($rows = null, $cols = null)
    {
        echo $this->CreateHtml($rows, $cols);
    }

    public function CreateHtml($rows = null, $cols = null)
    {
        $htmlValue = htmlspecialchars($this->Value);

        $my_rows = $rows;
        $my_cols = $cols;

        if (is_null($my_rows) || $my_rows <= 0) {
            $my_rows = $this->rows;
        }

        if (is_null($my_cols) || $my_cols <= 0) {
            $my_cols = $this->cols;
        }

        return ' <textarea style="resize:both;" ' .
            " name=\"{$this->InstanceName}\" " .
            " id=\"{$this->InstanceName}\" rows=\"{$my_rows}\" cols=\"{$my_cols}\" >" .
            "{$htmlValue}</textarea>";
    }
}

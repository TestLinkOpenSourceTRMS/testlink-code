<?php
namespace pChart;

/* pData class definition */

class pData
{
    public $Data;
    public $DataDescription;

    public function __construct()
    {
        $this->Data                           = array();
        $this->DataDescription                = array();
        $this->DataDescription["Position"]    = "Name";
        $this->DataDescription["Format"]["X"] = "number";
        $this->DataDescription["Format"]["Y"] = "number";
        $this->DataDescription["Unit"]["X"]   = null;
        $this->DataDescription["Unit"]["Y"]   = null;
    }

    function ImportFromCSV($FileName, $Delimiter = ",", $DataColumns = -1, $HasHeader = false, $DataName = -1)
    {
        $handle = @fopen($FileName, "r");
        if ($handle) {
            $HeaderParsed = false;
            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);
                $buffer = str_replace(chr(10), "", $buffer);
                $buffer = str_replace(chr(13), "", $buffer);
                $Values = explode($Delimiter, $buffer);

                if ($buffer != "") {
                    if ($HasHeader == true && $HeaderParsed == false) {
                        if ($DataColumns == -1) {
                            $ID = 1;
                            foreach ($Values as $key => $Value) {
                                $this->SetSerieName($Value, "Serie" . $ID);
                                $ID++;
                            }
                        } else {
                            $SerieName = "";

                            foreach ($DataColumns as $key => $Value) {
                                $this->SetSerieName($Values[$Value], "Serie" . $Value);
                            }
                        }
                        $HeaderParsed = true;
                    } else {
                        if ($DataColumns == -1) {
                            $ID = 1;
                            foreach ($Values as $key => $Value) {
                                $this->AddPoint(intval($Value), "Serie" . $ID);
                                $ID++;
                            }
                        } else {
                            $SerieName = "";
                            if ($DataName != -1) {
                                $SerieName = $Values[$DataName];
                            }

                            foreach ($DataColumns as $key => $Value) {
                                $this->AddPoint($Values[$Value], "Serie" . $Value, $SerieName);
                            }
                        }
                    }
                }
            }
            fclose($handle);
        }
    }

    function AddPoint($Value, $Serie = "Serie1", $Description = "")
    {
        if (is_array($Value) && count($Value) == 1) {
            $Value = $Value[0];
        }

        $ID = 0;
        if (is_array($this->Data)) {
            for ($i = 0; $i <= count($this->Data); $i++) {
                if (isset($this->Data[$i][$Serie])) {
                    $ID = $i + 1;
                }
            }
        }

        if (count($Value) == 1) {
            $this->Data[$ID][$Serie] = $Value;
            if ($Description != "") {
                $this->Data[$ID]["Name"] = $Description;
            } elseif (!isset($this->Data[$ID]["Name"])) {
                $this->Data[$ID]["Name"] = $ID;
            }
        } else {
            foreach ($Value as $key => $Val) {
                $this->Data[$ID][$Serie] = $Val;
                if (!isset($this->Data[$ID]["Name"])) {
                    $this->Data[$ID]["Name"] = $ID;
                }
                $ID++;
            }
        }
    }

    function AddSerie($SerieName = "Serie1")
    {
        if (!isset($this->DataDescription["Values"])) {
            $this->DataDescription["Values"][] = $SerieName;
        } else {
            $Found = false;
            foreach ($this->DataDescription["Values"] as $key => $Value) {
                if ($Value == $SerieName) {
                    $Found = true;
                }
            }

            if (!$Found) {
                $this->DataDescription["Values"][] = $SerieName;
            }
        }
    }

    function AddAllSeries()
    {
        unset($this->DataDescription["Values"]);

        if (isset($this->Data[0])) {
            foreach ($this->Data[0] as $Key => $Value) {
                if ($Key != "Name") {
                    $this->DataDescription["Values"][] = $Key;
                }
            }
        }
    }

    function RemoveSerie($SerieName = "Serie1")
    {
        if (!isset($this->DataDescription["Values"])) {
            return (0);
        }

        $Found = false;
        foreach ($this->DataDescription["Values"] as $key => $Value) {
            if ($Value == $SerieName) {
                unset($this->DataDescription["Values"][$key]);
            }
        }
    }

    function SetAbsciseLabelSerie($SerieName = "Name")
    {
        $this->DataDescription["Position"] = $SerieName;
    }

    function SetSerieName($Name, $SerieName = "Serie1")
    {
        $this->DataDescription["Description"][$SerieName] = $Name;
    }

    function SetXAxisName($Name = "X Axis")
    {
        $this->DataDescription["Axis"]["X"] = $Name;
    }

    function SetYAxisName($Name = "Y Axis")
    {
        $this->DataDescription["Axis"]["Y"] = $Name;
    }

    function SetXAxisFormat($Format = "number")
    {
        $this->DataDescription["Format"]["X"] = $Format;
    }

    function SetYAxisFormat($Format = "number")
    {
        $this->DataDescription["Format"]["Y"] = $Format;
    }

    function SetXAxisUnit($Unit = "")
    {
        $this->DataDescription["Unit"]["X"] = $Unit;
    }

    function SetYAxisUnit($Unit = "")
    {
        $this->DataDescription["Unit"]["Y"] = $Unit;
    }

    function SetSerieSymbol($Name, $Symbol)
    {
        $this->DataDescription["Symbol"][$Name] = $Symbol;
    }

    function removeSerieName($SerieName)
    {
        if (isset($this->DataDescription["Description"][$SerieName])) {
            unset($this->DataDescription["Description"][$SerieName]);
        }
    }

    function removeAllSeries()
    {
        foreach ($this->DataDescription["Values"] as $Key => $Value) {
            unset($this->DataDescription["Values"][$Key]);
        }
    }

    function GetData()
    {
        return ($this->Data);
    }

    function GetDataDescription()
    {
        return ($this->DataDescription);
    }
}

?>

  /**
   * render Ghost Test Case Summary
   */
  function renderGhostSummary(&$item2render) {
    $warningRenderException = lang_get('unable_to_render_ghost_summary');
    $versionTag = '[version:%s]';
 
    // $href = '<a href="Javascript:openTCW(\'%s\',%s);">%s:%s' . " $versionTag (link)<p></a>";
    // second \'%s\' needed if I want to use Latest as indication, need to understand
    // Javascript instead of javascript, because CKeditor sometimes complains
    $tlBeginMark = self::GHOSTBEGIN;
    $tlEndMark = self::GHOSTEND;
    $tlEndMarkLen = strlen($tlEndMark);

    // I've discovered that working with Web Rich Editor generates
    // some additional not wanted entities, that disturb a lot
    // when trying to use json_decode().
    // Hope this set is enough.
    $replaceSet = array($tlEndMark, '</p>', '<p>','&nbsp;');
    $replaceSetWebRichEditor = array('</p>', '<p>','&nbsp;');

    $key2check = array('summary','preconditions');

    // I've discovered that working with Web Rich Editor generates
    // some additional not wanted entities, that disturb a lot
    // when trying to use json_decode().
    // Hope this set is enough.
    // 20130605 - after algorithm change, this seems useless
    //$replaceSet = array($tlEndMark, '</p>', '<p>','&nbsp;');
    // $replaceSetWebRichEditor = array('</p>', '<p>','&nbsp;');
    $rse = &$item2render;
    foreach($key2check as $item_key) {
      $start = strpos($rse[$item_key],$tlBeginMark);
      $ghost = $rse[$item_key];

      // There is at least one request to replace ?
      if($start !== FALSE) {
        $xx = explode($tlBeginMark,$rse[$item_key]);

        // How many requests to replace ?
        $xx2do = count($xx);
        $ghost = '';
        for($xdx=0; $xdx < $xx2do; $xdx++) {
          $isTestCaseGhost = true;

          // Hope was not a false request.
          // if( strpos($xx[$xdx],$tlEndMark) !== FALSE)
          if( ($cutting_point = strpos($xx[$xdx],$tlEndMark)) !== FALSE) {
            // Separate command string from other text
            // Theorically can be just ONE, but it depends
            // is user had not messed things.
            $yy = explode($tlEndMark,$xx[$xdx]);

            if( ($elc = count($yy)) > 0) {
              $dx = $yy[0];

              // trick to convert to array
              $dx = '{' . html_entity_decode(trim($dx,'\n')) . '}';
              $dx = json_decode($dx,true);

              try {
                $xid = $this->getInternalID($dx['TestCase']);
                if( $xid > 0 ) {
                  $linkFeedback=")";
                  $addInfo="";
                  $vn = isset($dx['Version']) ? intval($dx['Version']) : 0;
                  if($vn == 0) {
                    // User wants to follow latest ACTIVE VERSION
                    $zorro = $this->get_last_version_info($xid,array('output' => 'full','active' => 1));
                    if (is_null($zorro)) {
                      // seems all versions are inactive, in this situation will get latest
                      $zorro = $this->get_last_version_info($xid,array('output' => 'full'));
                      $addInfo = " - All versions are inactive!!";
                    }
                    $vn = intval($zorro['version']);
                  }

                  $fi = $this->get_basic_info($xid,array('number' => $vn));
                  if(!is_null($fi)) {
                    if( isset($dx['Step']) ) {
                      $isTestCaseGhost = false;

                      // ghost for rendering Test Case Step (string display)
                      // [ghost]"Step":1,"TestCase":"MO-2","Version":1[/ghost]
                      //
                      // ATTENTION REMEMBER THAT ALSO CAN BE:
                      // 
                      // [ghost]"Step":1,"TestCase":"MO-2","Version":""[/ghost]
                      // [ghost]"Step":1,"TestCase":"MO -2"[/ghost]
                      //
                      if(intval($dx['Step']) > 0) {
                        $deghosted = true;
                        $rightside = trim(substr($xx[$xdx],$cutting_point+$tlEndMarkLen));
                        $stx = $this->get_steps($fi[0]['tcversion_id'],$dx['Step']);

                        $ghost .= $stx[0]['actions'] . $rightside;
                      }
                    } else {
                      // ghost for rendering Test Case (create link)
                      $ghost .= sprintf($href,$dx['TestCase'],$vn,$dx['TestCase'],$fi[0]['name'],$vn,$linkFeedback);
                    }
                  }
                }

                if($isTestCaseGhost) {
                  $lim = $elc-1;
                  for($cpx=1; $cpx <= $lim; $cpx++) {
                    $ghost .= $yy[$cpx];
                  }
                }
              } catch (Exception $e) {
                $ghost .= $rse[$item_key];
              }
            }
          } else {
            $ghost .= $xx[$xdx];
          }
        }
      }

      if($ghost != '') {
        $rse[$item_key] = $ghost;
      }
    }
  } // function end

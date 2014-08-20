<?php

namespace Alphred\Alfred;

class Alfred {

    public function trigger() {
        // call an external trigger
    }

function callExternalTrigger( $bundle, $trigger, $argument = FALSE ) {
  $script = "tell application \"Alfred 2\" to run trigger \"$trigger\" in workflow \"$bundle\"";
  if ( $argument !== FALSE ) {
    $script .= "with argument \"$argument\"";
  }
  exec( "osascript -e '$script'" );
}
}
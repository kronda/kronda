<div id="server_config">
  <table cellpadding="3px" cellspacing="10px">
    <thead>
      <tr>
        <th>&nbsp;</th>
        <th><?php _e( 'Original', 'developer-tools' ) ?></th>
        <th>Current</th>
      </tr>      
    </thead>
    <tbody>
      <tr>
        <td align="right"><strong><?php _e( 'WordPress', 'developer-tools' ) ?></strong></td>
        <td><?php print $view['original']['wordpress'] ?></td>
        <td<?php if ( $view['original']['wordpress'] != $view['wordpress'] ) print ' style="color:red;"'; ?>><?php print $view['wordpress'] ?></td>
      </tr>
      <tr>
        <td align="right"><strong><?php _e( 'PHP', 'developer-tools' ) ?></strong></td>
        <td><?php print $view['original']['php'] ?></td>
        <td<?php if ( $view['original']['php'] != $view['php'] ) print ' style="color:red;"'; ?>><?php print $view['php'] ?></td>
      </tr>
      <tr>
        <td align="right"><strong><?php _e( 'MySQL', 'developer-tools' ) ?></strong></td>
        <td><?php print $view['original']['mysql'] ?></td>
        <td<?php if ( $view['original']['mysql'] != $view['mysql'] ) print ' style="color:red;"'; ?>><?php print $view['mysql'] ?></td>
      </tr>           
    </tbody>
  </table><!-- FUCK YEA ITS A TABLE, WHAT!!!! -->
</div>
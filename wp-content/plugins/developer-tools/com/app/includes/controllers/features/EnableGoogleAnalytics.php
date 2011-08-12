<?php
class EnableGoogleAnalytics extends Feature
{
	public function SetSettings()
	{	
		$this->title				= '<a href="http://www.google.com/analytics/" target="_blank">Google Analytics</a>';
		$this->information  = __( 'Where can I find my tracking code and account number?', 'developer-tools' ) . ' <a href="http://www.google.com/support/analytics/bin/answer.py?hl=en&answer=81977" target="_blank">' . __( 'here', 'developer-tools' ) .'</a>.';
		$this->fields				= array(
			array( 
				'fieldType' => 'TextInput',
				'label' => __( 'Enter Google Analytics tracking number', 'developer-tools' ),
				'name' => 'UA',
				'required' => true,
				'characterSet' => 'alphaNumericHyphenUnderscore',
				'afterLabel' => __( 'Format' , 'developer-tools' ) . ': UA-xxxxxxx-y'
			),
			array( 
				'fieldType' => 'Checkbox',
				'label' => __( 'Don\'t track logged in users', 'developer-tools' ),
				'name' => 'track',
				'advanced' => true
			)			
		);
	}
	
	public function Enabled($value)
	{
		$this->value = $value;
		if ( $value['UA'] && ( ( $value['track'] && !is_user_logged_in() ) || (  !$value['track']  ) ) )
			add_action('wp_footer', array(&$this, 'FooterInclude'));
	}
	
	public function FooterInclude()
	{ ?>
		<script type="text/javascript">
			var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
			document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
			try{
				var pageTracker = _gat._getTracker("<?php print $this->value['UA'] ?>");
				pageTracker._trackPageview();
			} catch(err) {}
		</script>
	<?php }
}
<?php
/**
 * PayPoint (Secpay) Payment gateway for CartThrob 2
 *
 * @author Darren Miller <darren@dmlogic.com>
 * @copyright DM Logic Ltd 2011 http://dmlogic.com
 * @version 0.4
 * @uses CartThrob v2.0503
 *
 * Use as you see fit for any purpose other than re-sale
 * Use at your own risk.
 * This comment block must be left intact
 */
class Cartthrob_paypoint_secpay extends Cartthrob_payment_gateway
{

	// public vars
	public $title = 'PayPoint.net (Secpay)';
	public $language_file = TRUE;
 	public $overview = '<p>Documentation available at <a href="http://dmlogic.com/add_ons/ct_paypoint">dmlogic.com/add_ons/ct_paypoint</a></p>';
 	public $settings = array(
		array(
			'name' =>  'Submit URL',
			'short_name' => 'secpay_submit_url',
			'type' => 'text',
			'default' => 'https://www.secpay.com/java-bin/ValCard',
		),
		array(
			'name' =>  'Merchant ID',
			'short_name' => 'secpay_merchant_id',
			'type' => 'text',
			'default' => 'secpay',
		),
		array(
			'name' =>  'Digest password',
			'short_name' => 'secpay_digest_password',
			'type' => 'text',
			'default' => 'secpay',
		),
		array(
			'name' =>  'Transaction mode',
			'short_name' => 'secpay_mode',
			'type' => 'radio',
			'default' => 'test',
			'options' => array(
				'test' => 'Test - success',
				'fail' => 'Test - failure',
				'live' => 'live'
			)
		),
		array(
			'name'=>'Currency code',
			'short_name'=>'secpay_currency_code',
			'type'=>'text',
			'default' => 'GBP'
		),
		array(
			'name'=>'Payment template (for Pro accounts)',
			'short_name'=>'secpay_template',
			'type'=>'text',
		),
		array(
			'name'=>'Payment error template (for Pro accounts)',
			'short_name'=>'secpay_error_template',
			'type'=>'text',
		),
		array(
			'name'=>'Response template',
			'short_name'=>'secpay_response_template',
			'type'=>'select',
			'attributes' => array(
				'class' 	=> 'templates',
				),
		),
		array(
			'name' =>  'Send Paypoint customer email',
			'short_name' => 'secpay_customer_email',
			'type' => 'radio',
			'default' => 'No',
			'options' => array(
				'yes' => 'Yes',
				'no' => 'No'
			)
		),
		array(
			'name' =>  'Send Paypoint admin email',
			'short_name' => 'secpay_admin_email',
			'type' => 'radio',
			'default' => 'No',
			'options' => array(
				'yes' => 'Yes',
				'no' => 'No'
			)
		),
		array(
			'name' =>  'Send order items',
			'short_name' => 'secpay_send_order_items',
			'type' => 'radio',
			'default' => 'Yes',
			'options' => array(
				'yes' => 'Yes',
				'no' => 'No'
			)
		)

	);

	public $required_fields = array();

	public $paypoint_fields = array(

		'bill_name' => FALSE,
		'bill_company' => 'company',
		'bill_email' => 'email_address',
		'bill_addr_1' => 'address',
		'bill_addr_2' => 'address2',
		'bill_city' => 'city',
		'bill_state' => 'state',
		'bill_post_code' => 'zip',
		'bill_tel' => 'phone',
		'bill_country' => 'country_code',
		'bill_fax' => FALSE,
		'bill_url' => FALSE,
		'customer' => FALSE,

		'ship_name' => FALSE,
		'ship_company' => 'shipping_company',
		'ship_addr_1' => 'shipping_address',
		'ship_addr_2' => 'shipping_address2',
		'ship_city' => 'shipping_city',
		'ship_state' => 'shipping_state',
		'ship_post_code' => 'shipping_zip',
		'ship_tel' => FALSE,
		'ship_country' => 'shipping_country_code',
		'ship_email' => FALSE,
		'ship_fax' => FALSE,
		'ship_url' => FALSE,

		);

	public $hidden = array();

	public $fields = array(
		'first_name',
		'last_name',
		'company',
		'address' ,
		'address2' ,
		'city' ,
		'state' ,
		'zip' ,
		'country' ,
		'shipping_first_name' ,
		'shipping_last_name',
		'shipping_company',
		'shipping_address',
		'shipping_address2',
		'shipping_city',
		'shipping_state',
		'shipping_zip',
		'shipping_country',
		'phone',
		'email_address',
	);

	// -----------------------------------------------------------------

	// private vars
	private $EE;

	// -----------------------------------------------------------------

	public function __construct() {
		@session_start();
	}

	// -----------------------------------------------------------------

	/**
	 * process_payment
	 *
 	 * @param string $credit_card_number	not used
	 * @return void
	 */
	public function process_payment($credit_card_number) {

		$gateway_data = array();

		// transfer order data into post array
		foreach($this->paypoint_fields as $pp => $ct) {

			// check for PayPoint field name if no CT equivalent
			if(FALSE === $ct) {

				$var = $this->order($pp);
				if(!$var) {
					continue;
				}
				$value = $var;

			} else {
				$value = $this->order($ct);
			}

			if($value) {
				$gateway_data[$pp] = $value;
			}
		}

		// create shipping and billing names if not already set
		if(!isset($gateway_data['bill_name'])) {
			$gateway_data['bill_name'] = trim( $this->order('first_name').' '.$this->order('last_name')  );
		}
		if(!isset($gateway_data['ship_name'])) {
			$gateway_data['ship_name'] = trim( $this->order('shipping_first_name').' '.$this->order('shipping_last_name')  );
		}

		// setup important hidden vars

		// currency code
		$gateway_data['currency'] = $this->plugin_settings('secpay_currency_code');

		// merchant ID
		$gateway_data['merchant'] = $this->plugin_settings('secpay_merchant_id');

		// transaction ID
		$gateway_data['trans_id'] = session_id(); // we've got to pass this somewhere, and this is the only candidate

		// order value
		$gateway_data['amount'] = $this->order('total');

		// do we mail customer?
		$gateway_data['mail_customer'] = ($this->plugin_settings('secpay_customer_email') == 'no') ? 'FALSE' : 'bill';

		// do we mail merchant?
		if($this->plugin_settings('secpay_admin_email') == 'no')  {
			$gateway_data['mail_merchants'] = ':';
		}

		// digest string
		$gateway_data['digest'] = md5(	$gateway_data['trans_id'].
										$this->order('total').
										$this->plugin_settings('secpay_digest_password') );

		// payment template
		if($this->plugin_settings('secpay_template')) {
			$gateway_data['template'] =	'http://www.secpay.com/users/'.
															$this->plugin_settings('secpay_merchant_id').'/'.
															$this->plugin_settings('secpay_template');
		}

		// error template
		if($this->plugin_settings('secpay_error_template')) {
			$gateway_data['err_template'] =	'http://www.secpay.com/users/'.
															$this->plugin_settings('secpay_merchant_id').'/'.
															$this->plugin_settings('secpay_error_template');
		}

		// order items
		if($this->plugin_settings('secpay_send_order_items') == 'yes') {
			$delivery_desc = ($this->order('shipping_description')) ? $this->order('shipping_description') : $this->order('shipping_option');
			$gateway_data['order'] = $this->generate_order_items($delivery_desc);
		}

		// callback path
		$gateway_data['callback'] = $this->get_notify_url(ucfirst(get_class($this)),'process_callback');

		// we have to use a POST callback as PayPoint callback vars will bork EE before it gets anywhere near CartThrob (invalid GET vars message)
		$gateway_data['options'] = 'cb_post=true,md_flds=trans_id';

		// test mode
		switch($this->plugin_settings('secpay_mode')) {
			case 'test':
				$gateway_data['test_status'] = 'true';
				break;
			case 'fail':
				$gateway_data['test_status'] = 'false';
				break;
		}

		$this->gateway_exit_offsite($gateway_data,FALSE);

		$form = $this->ssf($gateway_data);
		echo $form;
		exit;
	}

	// -----------------------------------------------------------------

	/**
	 * process_callback
	 *
	 * Process the PayPoint callback and return a message
	 *
	 * @param array $post	the callback data
	 * @return string
	 */
	function process_callback($post) {

		$this->EE =& get_instance();

		if(!isset($post['hash']) || !isset($post['trans_id'])) {
			$this->bail( $this->lang('paypoint_callback_error_1') );
		}

		// confirm the hash at this point
		$digest_string = 'trans_id='.$post['trans_id'].
						//'&amount='.$post['amount']. // doesn't come through with failures
						'&'.$this->plugin_settings('secpay_digest_password');

		if(md5($digest_string) != $post['hash']) {
			$this->bail( $this->lang('paypoint_callback_error_2') );
		}

		// assume auth is bad
		$authorized =  FALSE;
		$error_message = NULL;
		$failed = TRUE;
		$processing = FALSE;
		$declined = FALSE;
		$transaction_id = NULL;

		// relaunch session
		if (!empty($post['trans_id'])) {

			// we'll need the same session_id as the client to clear the cart later
			if ($post['trans_id'] != @session_id()) {
				session_destroy();
				session_id($post['trans_id']);
				session_start();
			}
			$this->relaunch_session($post['trans_id']);

		} else {
			$this->bail( $this->lang('paypoint_callback_error_3') );
		}

		$order = $this->order();

		if(empty($order)) {
			$this->bail( $this->lang('paypoint_callback_error_4') );
		}

		$response = array(
			'session_id' => $post['trans_id'],
			'order_id' => $this->order('order_id'),
			'entry_id' => $this->order('entry_id'),
			'error_message' => ''
		);

		// describe transaction
		switch($post['code']) {
			case 'A':
				$transaction_id = $post['trans_id'];
				$authorized = TRUE;
				$failed = FALSE;
				break;

			case 'N':
			case 'F':
				$transaction_id = $post['trans_id'];
				$declined = TRUE;
				$failed = FALSE;
				$error_message = $post['message'];
				$response['error_message'] = $post['message'];
				break;
		}

		$auth = array(
			'authorized' 	=> $authorized,
			'error_message'	=> $error_message,
			'failed'		=> $failed,
			'processing'	=> $processing,
			'declined'		=> $declined,
			'transaction_id'=> $transaction_id
			);

		// update the order
		$this->gateway_order_update($auth,$this->order('entry_id') );

		// empty the cart if order is complete
		if($authorized) {
			session_destroy();
		}

		// display result
		$this->display_response($response,$authorized);
	}

	// -----------------------------------------------------------------

	/**
	 * generate_order_items
	 *
	 * Create an XML string in required format for order items
	 *
	 * @param string $delivery_desc
	 * @return string
	 * @author Darren Miller
	 * @access public
	 * @since 0.1
	 */
	public function generate_order_items($delivery_desc = '') {

		$items = $this->order('items');

		if(empty($items)) {
			return '';
		}

		$str = "<order class='com.secpay.seccard.Order'><orderLines class='com.secpay.seccard.OrderLine'>";

		foreach($items as $item) {

			$desc = $item['title'];

			if(isset($item['item_options'])
				&& is_array($item['item_options'])
				&& !empty($item['item_options']) ) {

				$desc .= ' [';
				$o = 0;
				foreach($item['item_options'] as $k => $v) {
					if($o > 0) $desc .= ', ';
					$desc .= "$k: $v";
					$o ++;
				}
				$desc .= ']';
			}

			$str .= '<OrderLine><prod_code>'.$desc.
					'</prod_code><item_amount>'.$item['price'].
					'</item_amount><quantity>'.$item['quantity'].'</quantity></OrderLine>';
		}

		if($this->order('shipping') > 0 ) {

			$str .= '<OrderLine><prod_code>Delivery '.$delivery_desc.
					'</prod_code><item_amount>'.$this->order('shipping').
					'</item_amount><quantity>1</quantity></OrderLine>';
		}

		$str .= '</orderLines></order>';

		return $str;
	}

	// -----------------------------------------------------------------

	/**
	 * display_response
	 *
	 * Use an EE Template to show response in PayPoint
	 *
	 * @param array $data
	 */
	private function display_response($data,$authorized) {

		$tmpl = explode('/',$this->plugin_settings('secpay_response_template'));

		// must have a group and template
		if(count($tmpl) != 2) {
			$this->bail( $this->lang('paypoint_callback_error_5') );
		}

		// go get the template
		$this->EE->load->library('Template', NULL, 'TMPL');
		$template = $this->EE->TMPL->fetch_template($tmpl[0], $tmpl[1],FALSE);

		if(!$template) {
			$this->bail( $this->lang('paypoint_callback_error_5') );
		}

		// basic conditionals, US-friendly. bless 'em
		$cond['authorized'] = $authorized;
		$cond['not_authorized'] = !$authorized;
		$template = $this->EE->functions->prep_conditionals($template, $cond);

		// parse template
		$template = $this->EE->TMPL->parse_variables($template, array($data));
		$template = $this->EE->TMPL->parse_globals($template);
		$this->EE->TMPL->parse($template);

		echo $this->EE->TMPL->final_template;
		exit;
	}

	// -----------------------------------------------------------------

	/**
	 * bail
	 *
	 * Fail with an EE standard error message
	 *
	 * @param string $msg
	 */
	private function bail($msg = '') {

		if($msg == '') {
			$msg = $this->EE->lang->line('invalid_action');
		}

		$this->EE->output->fatal_error($msg);
	}

	// -----------------------------------------------------------------

	/**
	 * ssf
	 *
	 * Build a self-submitting form
	 *
	 * @param array $data
	 * @return string
	 */
	private function ssf($data) {

		$hidden = '';
		foreach($data as $k => $v) {
			$hidden .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />'."\n";
		}

		$form = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Redirecting to Payment Gateway</title>
<style>
html{font-size:12px;}
body {color:#999;font-family:Arial,Helvetica,sans-serif;}
p{line-height:160%;margin:12px 0;}
</style>
</head>

<body>

	<h1>Redirecting to Payment Gateway</h1>

	<form action="'.$this->plugin_settings('secpay_submit_url').'" method="post" id="ssf">

		<p>You will shortly be transferred to the payment gateway to enter your credit card details.
			<!--noscript-->
			</p><p>If this does not happen within a few seconds, please <input type="submit" name="go" value="Click this button" />
			<!--/noscript-->
		</p>

		<div id="hidden_fields">
			'.$hidden.'
		</div>
	</form>

<script type="text/javascript">
/* <![CDATA[ */
document.getElementById("ssf").submit();
/* ]]> */
</script>

</body>
</html>';

		return $form;

	}
}
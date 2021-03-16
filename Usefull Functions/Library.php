<?php

use Twilio\Rest\Client; // https://www.twilio.com/docs/libraries/php
use PHPMailer\PHPMailer\PHPMailer; //https://github.com/PHPMailer/PHPMailer
use \Dejurin\GoogleTranslateForFree; //https://github.com/dejurin/php-google-translate-for-free
class Library
{

	public $lib_path;
	public $from_email;
	public $from_email_name;
	public $admin_send = false;
	public $attachment;
	public $path;

	function __construct()
	{
		date_default_timezone_set('Asia/Qatar');
		$this->from_email = "test@test.com";
		$this->from_email_name = "tester";
		$this->attachment = array();
		$this->lib_path = 'PATH';
	}

	public function translate_text($source, $target, $text, $attempts){
		require_once ($this->lib_path . '/GT/vendor/autoload.php');
		$tr = new GoogleTranslateForFree();
		$result = $tr->translate($source, $target, $text, $attempts);
		return $result; 
	}

	public function send_sms_twilio($number_to_send,$message){
		$twilio_path = $this->lib_path . '/twilio/src/Twilio/autoload.php';
		require_once($twilio_path);
		$credentials = $this->twilio_access_token();
		$sid = $credentials['sid'];
		$token = $credentials['token'];

		$client = new Client($sid, $token);

		$client->messages->create(
		    '+' . $number_to_send,
		    array(
		        // A Twilio phone number you purchased at twilio.com/console
		        'from' => $credentials['phone_number_from'],
		        'body' => $message,
		    )
		);

	}

	public function get_public_ip()
	{
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if (isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if (isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if (isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}

	public function date_diff($date_from, $date_to)
	{
		$ts1 = strtotime($date_from);
		$ts2 = strtotime($date_to);
		// $day1 = date('d', $ts1);
		$year1 = date('Y', $ts1);
		$year2 = date('Y', $ts2);
		$month1 = date('m', $ts1);
		$month2 = date('m', $ts2);
		$diff = (($year2 - $year1) * 12) + ($month2 - $month1) + 1;
		return $diff;
	}

	public function ordinal($number)
	{
		$ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
		if ((($number % 100) >= 11) && (($number % 100) <= 13))
			return $number . 'th';
		else
			return $number . $ends[$number % 10];
	}

	public function export_table2excel($table_post, $path = '')
	{
		$table = strip_tags($table_post, '<table><tr><td><th><thead><tfoot></table></tr></td></th></thead></tfoot>');
		$tmpfile = tempnam(sys_get_temp_dir(), 'html');
		file_put_contents($tmpfile, $table);

		$objPHPExcel     = new PHPExcel();
		$excelHTMLReader = PHPExcel_IOFactory::createReader('HTML');
		$excelHTMLReader->loadIntoExisting($tmpfile, $objPHPExcel);
		$objPHPExcel->getActiveSheet()->setTitle('sheet1');
		$full_path1 = 'media/' . $path . '.php';
		unlink($tmpfile);
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
		header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header('Pragma: public');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save(str_replace('.php', '.xlsx', $full_path1));
	}

	public function N2W($number, $upper = true)
	{
		$in_words = $this->convert_number_to_words($number);
		if ($upper == true) {
			$in_words = strtoupper($in_words);
		}
		return $in_words;
	}
	public function convert_number_to_words($number, $without_point_zero = true, $remove_special_chars = true) // CALL N2W for short
	{
		$number = str_replace(",", "", $number);
		// echo $number;exit;
		$hyphen      = '-';
		$conjunction = ' and ';
		$separator   = ', ';
		$negative    = 'negative ';
		$decimal     = ' point ';
		$dictionary  = array(
			0                   => 'zero',
			1                   => 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
			10                  => 'ten',
			11                  => 'eleven',
			12                  => 'twelve',
			13                  => 'thirteen',
			14                  => 'fourteen',
			15                  => 'fifteen',
			16                  => 'sixteen',
			17                  => 'seventeen',
			18                  => 'eighteen',
			19                  => 'nineteen',
			20                  => 'twenty',
			30                  => 'thirty',
			40                  => 'fourty',
			50                  => 'fifty',
			60                  => 'sixty',
			70                  => 'seventy',
			80                  => 'eighty',
			90                  => 'ninety',
			100                 => 'hundred',
			1000                => 'thousand',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		);

		if (!is_numeric($number)) {
			return false;
		}

		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . $this->convert_number_to_words(abs($number));
		}

		$string = $fraction = null;

		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}

		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];

				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}

				break;

			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . $this->convert_number_to_words($remainder);
				}

				break;

			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= $this->convert_number_to_words($remainder);
				}

				break;
		}

		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}
		if ($without_point_zero == true) {
			$string = str_replace(" POINT ZERO ZERO", "", $string);
		}
		if ($remove_special_chars == true) {
			$string = str_replace("-", "", $string);
		}
		return $string;
	}

	public function get_arabic_letter($number)
	{
		$number = (int) $number;
		$arabic_array = array(
			1 => 'واحد',
			2 => 'اثنان',
			3 => 'ثلاثة',
			4 => 'أربعة',
			5 => 'خمسة',
			6 => 'ستة',
			7 => 'سبعة',
			8 => 'ثمانية',
			9 => 'تسع',
			10 => 'عشرة',
			11 => 'أحد عشر',
			12 => "اثني عشر",
			13 => 'ثلاثة عشر',
			14 => 'أربعة عشرة',
			15 => 'خمسة عشر',
			16 => 'ستة عشر',
			17 => 'سبعة عشر',
			18 => 'ثمانية عشر',
			19 => 'تسعة عشر',
			20 => 'عشرون',
			21 => 'واحد وعشرين',
			22 => 'اثنين و عشرون',
			23 => 'ثلاثة وعشرون',
			24 => 'اربع وعشرون',
			25 => 'خمسة وعشرون',
			26 => 'ستة وعشرون',
			27 => 'سبعه وعشرين',
			28 => 'ثمانية وعشرون',
			29 => 'تسعة وعشرون',
			30 => 'ثلاثون',
			31 => 'واحد وثلاثين',
			32 => 'اثنان و ثلاثون',
			33 => 'ثلاثة وثلاثين',
			34 => 'اربع وثلاثون',
			35 => 'خمسة وثلاثون',
			36 => 'ستة وثلاثون',
			37 => 'سبعة وثلاثون',
			38 => 'ثمانية و ثلاثون',
			39 => 'تسعة وثلاثون',
			40 => 'أربعين',
			41 => 'واحد وأربعون',
			42 => 'اثنان وأربعون',
			43 => 'ثلاثة و اربعون',
			44 => 'أربعة وأربعون',
			45 => 'خمسة و أربعون',
			46 => 'ستة و اربعون',
			47 => 'سبعة واربعون',
			48 => 'ثمانية واربعون',
			49 => 'تسعة واربعون',
			50 => 'خمسون',
		);
		return $arabic_array[$number];
	}

	public function send_mail($reciever, $subject, $message, $attachments = array())
	{
		// require $this->lib_path . 'phpmailer/PHPMailerAutoload.php';
		$return_array = array();
		$this->include_phpmailer();
		$mail = new PHPMailer();
		$mail->isSMTP();
		$credentials = $this->mail_credentials();
		$mail->Host = $credentials['Host'];
		$mail->SMTPAuth = true;
		$mail->Username = $credentials['Username'];
		$mail->Password = $credentials['Password'];
		$mail->Port = $credentials['Port'];
		$mail->SMTPSecure = $credentials['SMTPSecure'];
		$mail->isHTML(true);
		$mail->setFrom($this->from_email, $this->from_email_name);
		$get_admin_email = $this->get_admin_email();
		foreach ($reciever as $key => $emails) {
			$mail->addAddress($emails['email']);
		}

		if (count($attachments) > 0) {
			foreach ($attachments as $key => $file_path) {
				$mail->addAttachment($file_path);
			}
		}

		$mail->Subject = $subject;
		$mail->Body = $message;

		if ($mail->send()) {
			$status = "success";
			$message = "Email send successfully";
			$response = "Email is sent!";
		} else {
			$status = "warning";
			$message = "Please contact IT support";
			$response = "Something is wrong:" . $mail->ErrorInfo;
		}
		if ($_SESSION['user_cateogory_admin'] == 'admin') {
			$message = $message . '. Sending email failed';
		}
		$return_array['status'] =  $status;
		$return_array['message'] =  $message;

		$return_array['response'] =  $response;
		return $return_array;;
	}

	public function get_admin_email()
	{
		$array_emails = array(
			'Rinshan' => 'test@test.com',
		);
	}

	public function mail_credentials()
	{
		$credentials = array();
		$credentials['Username'] = 'email';
		$credentials['Password'] = 'app_password'; //test password // don't keep personal password for your security
    		$credentials['Port'] = 'Port number'; //test password // don't keep personal password for your security
    		$credentials['Host'] = 'smtp_provider e.g.smtp.gmail.com'; //test password // don't keep personal password for your security
    		$credentials['SMTPSecure'] = 'ssl/tls'; //test password // don't keep personal password for your security
		return $credentials;
	}

	public function twilio_access_token()
	{
		$sid = 'sid'; //test sid // don't keep personal sid for your security
		$token = 'access_token'; //test token // don't keep personal token for your security
		$phone_number_from = '+0000000'; //test phone_number_from // don't keep personal phone_number_from for your security
		$credentials = array();
		$credentials['sid'] = $sid;
		$credentials['token'] = $token;
		$credentials['phone_number_from'] = $phone_number_from;
		return $credentials;
	}

	public function include_phpmailer()
	{
		require_once $this->lib_path . "/PHPMailer/PHPMailer.php";
		require_once $this->lib_path . "/PHPMailer/SMTP.php";
		require_once $this->lib_path . "/PHPMailer/Exception.php";
	}
}

<?php

class BaseTest extends \PHPUnit\Framework\TestCase
{
	public function test__qa_qa_version_below()
	{
		// as we cannot change the QA_VERSION constant, we test an appended version against the set constant
		$buildVersion = QA_VERSION . '.1234';
		$betaVersion = QA_VERSION . '-beta1';
		$this->assertSame(true, qa_qa_version_below($buildVersion));
		$this->assertSame(false, qa_qa_version_below($betaVersion));
	}

	public function test__qa_php_version_below()
	{
		// as we cannot change the PHP version, we test against an unsupported PHP version and a far-future version
		$this->assertSame(false, qa_php_version_below('5.1.4'));
		$this->assertSame(true, qa_php_version_below('11.1.0'));
	}

	public function test__qa_js()
	{
		$this->assertSame("'test'", qa_js('test'));
		$this->assertSame("'test'", qa_js('test', true));

		$this->assertSame(123, qa_js(123));
		$this->assertSame("'123'", qa_js(123, true));

		$this->assertSame('true', qa_js(true));
		$this->assertSame("'true'", qa_js(true, true));
	}

	public function test__convert_to_bytes()
	{
		$this->assertSame(102400, convert_to_bytes('k', 100));
		$this->assertSame(104857600, convert_to_bytes('m', 100));
		$this->assertSame(107374182400, convert_to_bytes('g', 100));

		$this->assertSame(102400, convert_to_bytes('K', 100));
		$this->assertSame(104857600, convert_to_bytes('M', 100));
		$this->assertSame(107374182400, convert_to_bytes('G', 100));

		$this->assertSame(100, convert_to_bytes('', 100));
		$this->assertSame(1048576, convert_to_bytes('k', 1024));

		// numeric strings cause warnings in PHP 7.1
		$this->assertSame(102400, convert_to_bytes('k', '100K'));
	}

	public function test__qa_q_request()
	{
		// set options cache to bypass database
		global $qa_options_cache, $qa_blockwordspreg_set;

		$title1 = 'How much wood would a woodchuck chuck if a woodchuck could chuck wood?';
		$title2 = 'Țĥé qũīçĶ ßřǭŴƞ Ƒöŧ ǰÙƢƥş ØƯĘŕ ƬĦȨ ĿÆƶȳ Ƌơǥ';

		$qa_options_cache['block_bad_words'] = '';
		$qa_blockwordspreg_set = false;
		$qa_options_cache['q_urls_title_length'] = 50;
		$qa_options_cache['q_urls_remove_accents'] = false;
		$expected1 = '1234/much-wood-would-woodchuck-chuck-woodchuck-could-chuck-wood';

		$this->assertSame($expected1, qa_q_request(1234, $title1));

		$qa_options_cache['block_bad_words'] = 'chuck';
		$qa_blockwordspreg_set = false;
		$qa_options_cache['q_urls_remove_accents'] = true;
		$expected2 = '5678/how-much-wood-would-a-woodchuck-if-a-woodchuck-could-wood';
		$expected3 = '9000/the-quick-ssrown-fot-juoips-ouer-the-laezy-dog';

		$this->assertSame($expected2, qa_q_request(5678, $title1));
		$this->assertSame($expected3, qa_q_request(9000, $title2));
	}

	public function test__qa_get_normal()
	{
		$_GET['test_basic'] = 'hello';
		$this->assertSame('hello', qa_get('test_basic'));
		unset($_GET['test_basic']);
	}

	public function test__qa_get_missing()
	{
		$this->assertNull(qa_get('field_does_not_exist_123xyz'));
	}

	public function test__qa_get_array_returns_null()
	{
		$_GET['test_arr'] = array('a' => 'b', 'c' => 'd');
		$this->assertNull(qa_get('test_arr'));
		unset($_GET['test_arr']);
	}

	public function test__qa_get_integer_returns_null()
	{
		$_GET['test_int'] = 42;
		$this->assertNull(qa_get('test_int'));
		unset($_GET['test_int']);
	}

	public function test__qa_post_text_normal()
	{
		$_POST['test_basic'] = '  hello world  ';
		$this->assertSame('hello world', qa_post_text('test_basic'));
		unset($_POST['test_basic']);
	}

	public function test__qa_post_text_trim()
	{
		$_POST['test_trim'] = "\t\n test \n";
		$this->assertSame('test', qa_post_text('test_trim'));
		unset($_POST['test_trim']);
	}

	public function test__qa_post_text_newlines()
	{
		$_POST['test_nl'] = "line1\r\nline2\rline3";
		$this->assertSame("line1\nline2\nline3", qa_post_text('test_nl'));
		unset($_POST['test_nl']);
	}

	public function test__qa_post_text_empty()
	{
		$_POST['test_empty'] = '';
		$this->assertSame('', qa_post_text('test_empty'));
		unset($_POST['test_empty']);
	}

	public function test__qa_post_text_missing()
	{
		$this->assertNull(qa_post_text('field_does_not_exist_456abc'));
	}

	public function test__qa_post_text_array_returns_null()
	{
		$_POST['test_arr'] = array('foo', 'bar', 'baz');
		$this->assertNull(qa_post_text('test_arr'));
		unset($_POST['test_arr']);
	}

	public function test__qa_post_text_integer_returns_null()
	{
		$_POST['test_int'] = 42;
		$this->assertNull(qa_post_text('test_int'));
		unset($_POST['test_int']);
	}

	public function test__qa_post_text_null_returns_null()
	{
		$_POST['test_null'] = null;
		$this->assertNull(qa_post_text('test_null'));
		unset($_POST['test_null']);
	}

	public function test__qa_retrieve_url_rejects_non_http_schemes()
	{
		$this->assertSame('', qa_retrieve_url('file:///etc/passwd'));
		$this->assertSame('', qa_retrieve_url('ftp://127.0.0.1/'));
		$this->assertSame('', qa_retrieve_url(''));
		$this->assertSame('', qa_retrieve_url('javascript:alert(1)'));
	}

	public function test__qa_retrieve_url_invalid_host_returns_empty()
	{
		$this->assertSame('', qa_retrieve_url('http:///path'));
		$this->assertSame('', qa_retrieve_url('http://?query'));
	}

	public function test__qa_retrieve_url_blocks_loopback_v4()
	{
		$this->assertSame('', qa_retrieve_url('http://127.0.0.1:1/'));
		$this->assertSame('', qa_retrieve_url('http://0.0.0.0:1/'));
	}

	public function test__qa_retrieve_url_blocks_loopback_v6()
	{
		$this->assertSame('', qa_retrieve_url('http://[::1]:1/'));
	}

	public function test__qa_retrieve_url_blocks_private_10()
	{
		$this->assertSame('', qa_retrieve_url('http://10.0.0.1:1/'));
		$this->assertSame('', qa_retrieve_url('http://10.255.255.255:1/'));
	}

	public function test__qa_retrieve_url_blocks_private_172()
	{
		$this->assertSame('', qa_retrieve_url('http://172.16.0.1:1/'));
		$this->assertSame('', qa_retrieve_url('http://172.31.255.255:1/'));
	}

	public function test__qa_retrieve_url_blocks_private_192()
	{
		$this->assertSame('', qa_retrieve_url('http://192.168.0.1:1/'));
		$this->assertSame('', qa_retrieve_url('http://192.168.255.255:1/'));
	}

	public function test__qa_retrieve_url_blocks_link_local()
	{
		$this->assertSame('', qa_retrieve_url('http://169.254.169.254:1/'));
		$this->assertSame('', qa_retrieve_url('http://169.254.0.1:1/'));
	}

	public function test__qa_retrieve_url_blocks_ula_v6()
	{
		$this->assertSame('', qa_retrieve_url('http://[fc00::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[fd00::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[fcff::1]:1/'));
	}

	public function test__qa_retrieve_url_blocks_link_local_v6()
	{
		$this->assertSame('', qa_retrieve_url('http://[fe80::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[fe90::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[fea0::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[febf::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[fec0::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[fed0::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[fee0::1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[feff::1]:1/'));
	}

	public function test__qa_retrieve_url_blocks_ipv4_mapped_v6()
	{
		$this->assertSame('', qa_retrieve_url('http://[::ffff:127.0.0.1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[::ffff:10.0.0.1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[::ffff:192.168.1.1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[::ffff:172.16.0.1]:1/'));
		$this->assertSame('', qa_retrieve_url('http://[::ffff:169.254.169.254]:1/'));
	}
}

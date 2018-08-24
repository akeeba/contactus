<?php
/**
 * @package        contactus
 * @copyright      Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

defined('_JEXEC') or die();

function openpgp_key($key, $ascii = true)
{
	if ($ascii)
	{
		preg_match( '/-----BEGIN ([A-Za-z ]+)-----/', $key, $matches );
		$marker = ( empty( $matches[1] ) ) ? 'MESSAGE' : $matches[1];
		$key = OpenPGP::unarmor( $key, $marker );
	}

	$openpgp_msg = OpenPGP_Message::parse( $key );

	return ( is_null( $openpgp_msg ) ) ? false : $openpgp_msg;
}

function openpgp_encrypt ($data, $keys, $armor = true)
{
	$plain_data = new OpenPGP_LiteralDataPacket($data, array(
		'format' => 'u', 'filename' => 'encrypted.gpg'
	));

	$encrypted = OpenPGP_Crypt_Symmetric::encrypt($keys, new OpenPGP_Message(array($plain_data)));

	if ($armor)
	{
		$headers = array();
		$encrypted = wordwrap(OpenPGP::enarmor($encrypted->to_bytes(), 'PGP MESSAGE', $headers), 64, "\n", true);
	}

	return $encrypted;
}

$pub_key     = openpgp_key($userGPGKeyInASCIIFormat);
$messageBody = openpgp_encrypt($message, $pub_key);
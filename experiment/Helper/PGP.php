<?php
/**
 * @package        contactus
 * @copyright      Copyright (c)2013-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU General Public License version 3 or later
 */

namespace Akeeba\ContactUs\Admin\Helper;

use Exception;
use InvalidArgumentException;
use OpenPGP;
use OpenPGP_Crypt_RSA;
use OpenPGP_Crypt_Symmetric;
use OpenPGP_LiteralDataPacket;
use OpenPGP_Message;
use RuntimeException;

defined('_JEXEC') or die();

abstract class PGP
{
	/**
	 * Parses an OpenPGP key provided in armored ASCII format into an OpenPGP_Message object, used for signing and / or
	 * encryption.
	 *
	 * @param   string $key The PGP key data
	 *
	 * @return  OpenPGP_Message  The parsed key; null if parsing fails.
	 */
	protected static function parseKey($key)
	{
		preg_match('/-----BEGIN ([A-Za-z ]+)-----/', $key, $matches);

		$marker = (empty($matches[1])) ? 'MESSAGE' : $matches[1];
		$key    = OpenPGP::unarmor($key, $marker);

		return OpenPGP_Message::parse($key);
	}

	/**
	 * Encrypt a chunk of text using PGP and the provided key object.
	 *
	 * @param   string          $string The text to encrypt
	 * @param   OpenPGP_Message $keys   The key to use
	 *
	 * @return  string  The encrypted output
	 */
	protected static function doEncrypt($string, $keys)
	{
		$plain_data = new OpenPGP_LiteralDataPacket($string, array(
			'format' => 'u', 'filename' => 'encrypted.gpg',
		));

		try
		{
			$encrypted = OpenPGP_Crypt_Symmetric::encrypt($keys, new OpenPGP_Message(array($plain_data)));
		}
		catch (Exception $e)
		{
			return '';
		}

		$headers = array();

		return wordwrap(OpenPGP::enarmor($encrypted->to_bytes(), 'PGP MESSAGE', $headers), 64, "\n", true);
	}

	/**
	 * Sign a piece of text using a key object and append the signature (clearsign)
	 *
	 * @param   string          $string The string to sign
	 * @param   OpenPGP_Message $key    The signing key
	 *
	 * @return  string
	 */
	protected static function doClearsign($string, $key)
	{
		// Create a new literal data packet
		$data = new OpenPGP_LiteralDataPacket($string, array('format' => 'u', 'filename' => 'stuff.txt'));

		// Clearsign-style normalization of the LiteralDataPacket
		$data->normalize(true);

		try
		{
			/* Create a signer from the key */
			$sign = new OpenPGP_Crypt_RSA($key);

			// The message is the signed data packet
			$m = $sign->sign($data);
		}
		catch (Exception $e)
		{
			// If signing fails return an empty string
			return '';
		}

		// Generate the signatures
		$signatures = $m->signatures();

		// Sanity checks
		if (empty($signatures))
		{
			return '';
		}

		$packets = $signatures[0];

		if (!is_object($packets) || !property_exists($packets, 'data'))
		{
			return '';
		}

		if (!is_array($packets[1]) || empty($packets[1]))
		{
			return '';
		}

		if (!is_object($packets[1][0]) || !method_exists($packets[1][0], 'to_bytes'))
		{
			return '';
		}

		// Create the ASCII-armored signed messages
		$ret = "-----BEGIN PGP SIGNED MESSAGE-----\nHash: SHA256\n\n";
		$ret .= wordwrap(preg_replace("/^-/", "- -", $packets[0]->data), 64, "\n", true);
		$ret .= "\n";
		$ret .= wordwrap(OpenPGP::enarmor($packets[1][0]->to_bytes(), "PGP SIGNATURE"), 64, "\n", true);

		return $ret;
	}

	/**
	 * Optionally encrypt a plaintext using the provided OpenPGP encryption key (a public or private OpenPGP key given
	 * in armored ASCII format). If the encryption key is invalid or the encryption fails the plaintext will be returned
	 * instead.
	 *
	 * If the encryption succeeded the result is an ASCII armored PGP message wrapped at 64 characters, each line
	 * terminated with a linebreak. Lines beginning with a dash are escaped with dash-space in front per RFC 4880. That
	 * is, you can use it in an email as-is.
	 *
	 * WARNING! This method is designed with a plaintext fallback in mind. If you want to ensure that the content is
	 * encrypted check the returned value against $plainText. If they are the same encryption failed and you should stop
	 * and do something about it. If they differ the encryption worked.
	 *
	 * @param   string $plainText     The plaintext to encrypt
	 * @param   string $encryptionKey The encryption key in ASCII-armored format.
	 *
	 * @return  string  The encrypted string or, if encryption failed, the plaintext.
	 */
	public static function encrypt($plainText, $encryptionKey)
	{
		// No key given? Return the plaintext.
		if (empty($encryptionKey))
		{
			return $plainText;
		}

		// Parse the ASCII-armored OpenPGP key
		$key = self::parseKey($encryptionKey);

		// Invalid key? Return the plaintext.
		if (is_null($key))
		{
			return $plainText;
		}

		try
		{
			// Try to encrypt the plaintext
			$encrypted = self::doEncrypt($plainText, $key);

			// If the encryption failed return the plaintext.
			if (empty($encrypted))
			{
				return $plainText;
			}
		}
		catch (Exception $e)
		{
			// If the encryption failed miserably return the plaintext.
			return $plainText;
		}

		return $encrypted;
	}

	/**
	 * Optionally sign a text using the provided OpenPGP key (a private OpenPGP key which IS NOT protected by a
	 * passphrase, given in armored ASCII format). If the signing key is invalid or the signing fails the original
	 * unsigned text will be returned instead.
	 *
	 * If the signing succeeded the result is an ASCII armored PGP clearisgned message wrapped at 64 characters. That
	 * is, you can use it in an email as-is.
	 *
	 * WARNING! This method is designed with a non-signed fallback in mind. If you want to ensure that the content is
	 * signed check the returned value against $text. If they are the same signing failed and you should stop and do
	 * something about it. If they differ the signing worked.
	 *
	 * @param   string $text       The text to clearsign
	 * @param   string $signingKey The signing key in ASCII-armored format.
	 *
	 * @return  string  The encrypted string or, if encryption failed, the plaintext.
	 */
	public static function sign($text, $signingKey)
	{
		// No key given? No signature.
		if (empty($encryptionKey))
		{
			return $text;
		}

		// Parse the ASCII-armored OpenPGP key
		$key = self::parseKey($signingKey);

		// Invalid key? No signature.
		if (is_null($key))
		{
			return $text;
		}

		try
		{
			// Try to sign the text
			$signed = self::doClearsign($text, $key);

			// If signing failed return the original text.
			if (empty($signed))
			{
				return $text;
			}
		}
		catch (Exception $e)
		{
			// If signing failed miserably return the plaintext.
			return $text;
		}

		return $signed;
	}

	/**
	 * Combines signing and encryption. Remember, the order of operations is FIRST sign THEN encrypt. It simply chains
	 * the sign() and encrypt() methods together. See their docblocks for information on the keys.
	 *
	 * Signing and encryption are treated as independent, optional operations. That is, your result may be:
	 *
	 * - signed and encrypted, if all goes well
	 * - only signed, if the encryption key is empty/invalid or encryption failed
	 * - only encrypted, if the signing key is empty/invalid/password-protected or signing failed
	 * - neither signed nor encrypted, if both aforementioned problems with signing and encryption occurred.
	 *
	 * It's best to use sign() and encrypt() separately if you want to send an email so that you know which headers to
	 * use.
	 *
	 * @param   string $text
	 * @param   string $encryptionKey
	 * @param   string $signingKey
	 *
	 * @return  SignAndEncryptResult  The result of possibly signing and encrypting the text.
	 */
	public static function signAndEncrypt($text, $encryptionKey, $signingKey)
	{
		$return = new SignAndEncryptResult($text);

		$return->message   = self::sign($return->message, $signingKey);
		$return->signed    = $return->message != $text;
		$encrypted         = self::encrypt($return->message, $encryptionKey);
		$return->encrypted = $encrypted != $return->message;
		$return->message   = $encrypted;

		return $return;
	}
}

/**
 * Class for returning the result of signAndEncrypt
 *
 * @property bool   $encrypted Is the message encrypted
 * @property bool   $signed    Is the message signed
 * @property string $message   The possibly signed and / or encrypted message
 */
final class SignAndEncryptResult
{
	/**
	 * Is the included message encrypted?
	 *
	 * @var bool
	 */
	private $encrypted = false;

	/**
	 * Is the included message signed?
	 *
	 * @var bool
	 */
	private $signed = false;

	/**
	 * The included message
	 *
	 * @var string
	 */
	private $message = '';

	/**
	 * Constructor.
	 *
	 * @param   string $message The message to initialise the object with.
	 */
	public function __construct($message)
	{
		$this->setMessage($message);
	}

	/**
	 * Converts the object to string by returning the raw message
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->message;
	}

	/**
	 * Gets the “encrypted” property
	 *
	 * @return  bool
	 */
	public function isEncrypted()
	{
		return $this->encrypted;
	}

	/**
	 * Sets the “encrypted” property
	 *
	 * @param   bool $encrypted
	 */
	public function setEncrypted($encrypted)
	{
		$this->encrypted = (bool) $encrypted;
	}

	/**
	 * Gets the “signed” property
	 *
	 * @return  bool
	 */
	public function isSigned()
	{
		return $this->signed;
	}

	/**
	 * Sets the “signed” property
	 *
	 * @param   bool $signed
	 */
	public function setSigned($signed)
	{
		$this->signed = (bool) $signed;
	}

	/**
	 * Gets the “message” property
	 *
	 * @return  string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Sets the “message” property
	 *
	 * @param mixed $message
	 */
	public function setMessage($message)
	{
		if (!is_string($message))
		{
			throw new InvalidArgumentException("The message must be a string");
		}

		$this->message = $message;
	}

	/**
	 * Magic property getter
	 *
	 * @param   string $name The name of the property to get
	 *
	 * @return  mixed
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'message':
				return $this->getMessage();
				break;

			case 'encrypted':
				return $this->isEncrypted();
				break;

			case 'signed':
				return $this->isSigned();
				break;
		}

		throw new RuntimeException(sprintf("Property $name does not exist in class %s", __CLASS__));
	}

	/**
	 * Magic property setter
	 *
	 * @param   string $name  The property to set
	 * @param   mixed  $value The property value
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'message':
				$this->setMessage($value);

				return;
				break;

			case 'encrypted':
				$this->setEncrypted($value);

				return;
				break;

			case 'signed':
				$this->setSigned($value);

				return;
				break;
		}

		throw new RuntimeException(sprintf("Property $name does not exist in class %s", __CLASS__));
	}
}

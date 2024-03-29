<?php
/**
 * @package   contactus
 * @copyright Copyright (c)2013-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\ContactUs\Site\Helper;

use Joomla\CMS\Environment\Browser;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\IpHelper;

class Akismet
{
	public static function isSpamContent(string $apiKey, string $name, string $email, string $content): bool
	{
		if (empty($apiKey))
		{
			return false;
		}

		$app    = Factory::getApplication();
		$struct = [
			'blog'                 => Uri::base(),
			'user_ip'              => IpHelper::getIp(),
			'user_agent'           => Browser::getInstance()->getAgentString(),
			'referrer'             => $app->input->server->get('HTTP_REFERER', '', 'raw'),
			'comment_type'         => 'contact-form',
			'comment_author'       => $name,
			'comment_author_email' => $email,
			'comment_content'      => $content,
		];

		$apiUrl = "https://{$apiKey}.rest.akismet.com/1.1/";
		$http   = HttpFactory::getHttp();
		$uri    = new Uri($apiUrl . 'comment-check');

		$response = $http->post($uri->toString(), $struct);

		return $response->body == 'true';
	}
}